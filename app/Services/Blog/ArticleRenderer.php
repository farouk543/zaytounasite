<?php

namespace App\Services\Blog;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Str;

/**
 * Transforme le HTML de l'éditeur (RichEditor) en article « magazine » :
 *  - ancres + sommaire à partir des titres H2 ;
 *  - section « Questions fréquentes » (H2 puis paires H3/réponse) extraite en accordéon + FAQPage ;
 *  - encadrés « À retenir » (blockquote), tableaux défilants sur mobile, paragraphe d'intro ;
 *  - liens internes sans nofollow ni nouvel onglet (le RichEditor les ajoute à tous les liens) ;
 *  - emplacement d'un appel à l'action au milieu de l'article (INLINE_CTA).
 */
class ArticleRenderer
{
    public const INLINE_CTA = '<!--zb-inline-cta-->';

    private const FAQ_HEADING = '/questions?\s+fr[ée]quentes|^\s*faq\b|الأسئلة\s+الشائعة|أسئلة\s+شائعة|frequently\s+asked/iu';

    /**
     * @return array{html: string, toc: array<int, array{id: string, text: string}>, faq: array<int, array{q: string, a: string}>}
     */
    public function render(string $html, string $keyPointLabel = 'À retenir'): array
    {
        if (trim($html) === '') {
            return ['html' => '', 'toc' => [], 'faq' => []];
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="zb-root">'.$html.'</div></body></html>');
        libxml_clear_errors();

        $root = $dom->getElementById('zb-root');
        $xpath = new DOMXPath($dom);

        $faq = $this->extractFaq($dom, $root);
        $toc = $this->anchorHeadings($root);
        $this->fixLinks($xpath, $root);
        $this->decorate($dom, $xpath, $root, $keyPointLabel);
        $this->placeInlineCta($dom, $root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return ['html' => $out, 'toc' => $toc, 'faq' => $faq];
    }

    private function extractFaq(DOMDocument $dom, DOMElement $root): array
    {
        $faqHeading = null;
        foreach ($this->childElements($root, 'h2') as $h2) {
            if (preg_match(self::FAQ_HEADING, trim($h2->textContent))) {
                $faqHeading = $h2;
                break;
            }
        }

        if (! $faqHeading) {
            return [];
        }

        $items = [];
        $current = null;
        $toRemove = [$faqHeading];

        for ($node = $faqHeading->nextSibling; $node; $node = $node->nextSibling) {
            if ($node instanceof DOMElement && $node->tagName === 'h2') {
                break;
            }
            $toRemove[] = $node;

            if ($node instanceof DOMElement && in_array($node->tagName, ['h3', 'h4'], true)) {
                if ($current) {
                    $items[] = $current;
                }
                $current = ['q' => trim($node->textContent), 'a' => ''];
            } elseif ($current && ! ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) === '')) {
                $current['a'] .= $dom->saveHTML($node);
            }
        }
        if ($current) {
            $items[] = $current;
        }

        foreach ($toRemove as $node) {
            $node->parentNode?->removeChild($node);
        }

        return array_values(array_filter($items, fn ($i) => $i['q'] !== '' && trim(strip_tags($i['a'])) !== ''));
    }

    private function anchorHeadings(DOMElement $root): array
    {
        $toc = [];
        $used = [];

        foreach ($this->childElements($root, 'h2') as $i => $h2) {
            $text = trim(preg_replace('/\s+/u', ' ', $h2->textContent));
            $id = Str::slug(Str::limit($text, 60, '')) ?: 'section-'.($i + 1);
            while (isset($used[$id])) {
                $id .= '-'.($i + 1);
            }
            $used[$id] = true;

            $h2->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'text' => $text];
        }

        return $toc;
    }

    private function fixLinks(DOMXPath $xpath, DOMElement $root): void
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $requestHost = request()->getHost();

        foreach ($xpath->query('.//a[@href]', $root) as $a) {
            /** @var DOMElement $a */
            $href = $a->getAttribute('href');
            $host = parse_url($href, PHP_URL_HOST);
            $isInternal = ! $host || in_array($host, array_filter([$appHost, $requestHost]), true)
                || str_starts_with($href, '#') || str_starts_with($href, '/');

            if ($isInternal) {
                $a->removeAttribute('target');
                $a->removeAttribute('rel');
                // Liens écrits en absolu vers « /… » : on les rattache à l'URL du site (sous-dossier compris).
                if (str_starts_with($href, '/') && ! str_starts_with($href, '//')) {
                    $a->setAttribute('href', url($href));
                }
            } else {
                $a->setAttribute('target', '_blank');
                $a->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    private function decorate(DOMDocument $dom, DOMXPath $xpath, DOMElement $root, string $keyPointLabel): void
    {
        $first = $this->childElements($root)[0] ?? null;
        if ($first && $first->tagName === 'p') {
            $first->setAttribute('class', 'zb-lead');
        }

        foreach ($xpath->query('.//blockquote', $root) as $quote) {
            /** @var DOMElement $quote */
            $quote->setAttribute('class', 'zb-callout');
            $quote->setAttribute('data-label', $keyPointLabel);
        }

        foreach (iterator_to_array($xpath->query('.//table', $root)) as $table) {
            $wrap = $dom->createElement('div');
            $wrap->setAttribute('class', 'zb-tableWrap');
            $table->parentNode->replaceChild($wrap, $table);
            $wrap->appendChild($table);
        }

        // Le RichEditor entoure le texte des cellules / puces de <p> : on les aplatit.
        foreach (iterator_to_array($xpath->query('.//td/p | .//th/p | .//li/p', $root)) as $p) {
            if ($p->parentNode->childNodes->length === 1 || $p->parentNode->getElementsByTagName('p')->length === 1) {
                while ($p->firstChild) {
                    $p->parentNode->insertBefore($p->firstChild, $p);
                }
                $p->parentNode->removeChild($p);
            }
        }
    }

    private function placeInlineCta(DOMDocument $dom, DOMElement $root): void
    {
        $h2s = $this->childElements($root, 'h2');
        $target = $h2s[(int) floor(count($h2s) / 2)] ?? null;

        if (count($h2s) >= 3 && $target) {
            $root->insertBefore($dom->createComment('zb-inline-cta'), $target);
        }
    }

    /** @return array<int, DOMElement> */
    private function childElements(DOMNode $parent, ?string $tag = null): array
    {
        $out = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement && ($tag === null || $child->tagName === $tag)) {
                $out[] = $child;
            }
        }

        return $out;
    }
}
