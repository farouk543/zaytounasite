{{--
  Statistiques de clics (back-office › Statistiques du site).
  Envoie en arrière-plan le libellé de chaque lien / bouton cliqué. Aucun cookie.
  Pour nommer précisément un bouton dans les stats : data-track="Nom lisible".
--}}
@unless(auth()->user()?->hasRole('admin'))
<script>
(function () {
  if (!navigator.sendBeacon) return;
  var endpoint = @json(route('analytics.click'));
  var token = @json(csrf_token());

  document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('[data-track], a[href], button, [role="button"], input[type="submit"]');
    if (!el || el.closest('[data-no-track]')) return;

    var label = el.getAttribute('data-track') || el.getAttribute('aria-label') || el.innerText || el.value || '';
    if (!label.trim()) {
      var img = el.querySelector('img[alt]');
      label = img ? img.alt : (el.getAttribute('title') || '');
    }
    label = label.replace(/\s+/g, ' ').trim().slice(0, 140);
    if (!label) return;

    var href = el.getAttribute('href') || (el.form && el.form.getAttribute('action')) || '';
    var kind = el.hasAttribute('data-track') ? 'cta'
      : (el.tagName === 'A' ? (el.host && el.host !== location.host ? 'external' : 'link') : 'button');
    if (href.indexOf('javascript:') === 0 || href === '#') href = '';

    var fd = new FormData();
    fd.append('_token', token);
    fd.append('p', location.pathname);
    fd.append('l', label);
    fd.append('k', kind);
    if (href) fd.append('h', el.href || href);
    try { navigator.sendBeacon(endpoint, fd); } catch (err) {}
  }, { capture: true, passive: true });
})();
</script>
@endunless
