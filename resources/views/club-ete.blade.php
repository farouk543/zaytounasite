@extends('layouts.app')

@section('title', __('ui.summer_club.page_title'))

@section('content')
<div class="summer-club-page">
    <section class="summer-club-hero">
        <div class="za-container">
            <div class="summer-club-heroGrid">
                <div>
                    <span class="summer-club-kicker">{{ __('ui.summer_club.hero_badge') }}</span>
                    <h1 class="summer-club-title">{{ __('ui.summer_club.hero_title') }}</h1>
                    <p class="summer-club-subtitle">
                        {{ __('ui.summer_club.hero_text') }}
                    </p>

                    <div class="summer-club-badges" aria-label="{{ __('ui.summer_club.subjects_label') }}">
                        <span class="summer-club-chip">{{ __('ui.summer_club.badge_french') }}</span>
                        <span class="summer-club-chip">{{ __('ui.summer_club.badge_english') }}</span>
                        <span class="summer-club-chip">{{ __('ui.summer_club.badge_math') }}</span>
                        <span class="summer-club-chip">{{ __('ui.summer_club.badge_levels') }}</span>
                    </div>

                    <div class="summer-club-actions">
                        <a class="summer-club-actionPrimary" href="#summer-club-packs">{{ __('ui.summer_club.reserve') }}</a>
                        <a class="summer-club-actionSecondary" href="#summer-club-packs">{{ __('ui.summer_club.see_packs') }}</a>
                    </div>
                </div>

                <div class="summer-club-visual">
                    <img src="{{ asset('images/summer_club.png') }}" alt="{{ __('ui.home.summer_club_alt') }}">
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-section">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">{{ __('ui.summer_club.benefits_eyebrow') }}</span>
                <h2 class="summer-club-heading">{{ __('ui.summer_club.benefits_title') }}</h2>
            </div>

            <div class="summer-club-grid4">
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">{{ __('ui.summer_club.benefit_1_title') }}</h3>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.benefit_1_text') }}</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">{{ __('ui.summer_club.benefit_2_title') }}</h3>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.benefit_2_text') }}</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">{{ __('ui.summer_club.benefit_3_title') }}</h3>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.benefit_3_text') }}</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">{{ __('ui.summer_club.benefit_4_title') }}</h3>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.benefit_4_text') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-section summer-club-soft">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">{{ __('ui.summer_club.program_eyebrow') }}</span>
                <h2 class="summer-club-heading">{{ __('ui.summer_club.program_title') }}</h2>
            </div>

            <div class="summer-club-grid3">
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">{{ __('ui.summer_club.badge_french') }}</h3>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.french_text') }}</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">{{ __('ui.summer_club.badge_english') }}</h3>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.english_text') }}</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">{{ __('ui.summer_club.badge_math') }}</h3>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.math_text') }}</p>
                </div>
            </div>
        </div>
    </section>

    @if($catalogItems->isNotEmpty())
        <section class="summer-club-section summer-club-catalogueSection">
            <div class="za-container">
                <div class="summer-club-head">
                    <span class="summer-club-eyebrow">Club d’été</span>
                    <h2 class="summer-club-heading">Catalogue pédagogique inclus après abonnement</h2>
                    <p class="summer-club-lead">
                        Après confirmation de l’abonnement, l’élève aura accès à des exercices, quiz et fiches de révision adaptés à son niveau.
                    </p>
                </div>

                <div class="summer-club-resourceGrid">
                    @foreach($catalogItems as $item)
                        @php
                            $coverUrl = $item['cover_image_path']
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($item['cover_image_path'])
                                : null;

                            $resourceIcon = match ($item['kind']) {
                                'quiz' => 'heroicon-o-question-mark-circle',
                                'fiche' => 'heroicon-o-document-text',
                                default => 'heroicon-o-pencil-square',
                            };

                            $resourceTypeLabel = match ($item['kind']) {
                                'quiz' => $item['label'],
                                'fiche' => 'Fiche de révision',
                                default => $item['label'],
                            };
                        @endphp

                        <button
                            type="button"
                            class="summer-club-resourceCard summer-club-resourceCard--{{ $item['kind'] }}"
                            onclick="window.scrollToSummerClubPacks && window.scrollToSummerClubPacks()"
                        >
                            <span class="summer-club-resourceMedia" aria-hidden="true">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="" loading="lazy">
                                @else
                                    <span class="summer-club-resourceFallback">
                                        @svg($resourceIcon)
                                    </span>
                                @endif

                                <span class="summer-club-resourceType">{{ $resourceTypeLabel }}</span>
                            </span>

                            <span class="summer-club-resourceBody">
                                <span class="summer-club-resourceTitle">{{ $item['title'] }}</span>

                                <span class="summer-club-resourceMeta">
                                    @if($item['subject'])
                                        <span>{{ $item['subject'] }}</span>
                                    @endif

                                    @if($item['level'])
                                        <span>{{ $item['level'] }}</span>
                                    @endif
                                </span>

                                @if($item['description'])
                                    <span class="summer-club-resourceDescription">
                                        {{ \Illuminate\Support\Str::limit($item['description'], 125) }}
                                    </span>
                                @endif

                                <span class="summer-club-lockBadge">
                                    @svg('heroicon-o-lock-closed')
                                    Disponible après confirmation
                                </span>

                                <span class="summer-club-disabledButton">
                                    Débloqué après abonnement
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="summer-club-section">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">{{ __('ui.summer_club.method_eyebrow') }}</span>
                <h2 class="summer-club-heading">{{ __('ui.summer_club.method_title') }}</h2>
            </div>

            <div class="summer-club-steps">
                <div class="summer-club-step">
                    <span class="summer-club-stepNumber">1</span>
                    <h3 class="summer-club-stepTitle">{{ __('ui.summer_club.step_1') }}</h3>
                </div>
                <div class="summer-club-step">
                    <span class="summer-club-stepNumber">2</span>
                    <h3 class="summer-club-stepTitle">{{ __('ui.summer_club.step_2') }}</h3>
                </div>
                <div class="summer-club-step">
                    <span class="summer-club-stepNumber">3</span>
                    <h3 class="summer-club-stepTitle">{{ __('ui.summer_club.step_3') }}</h3>
                </div>
                <div class="summer-club-step">
                    <span class="summer-club-stepNumber">4</span>
                    <h3 class="summer-club-stepTitle">{{ __('ui.summer_club.step_4') }}</h3>
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-section summer-club-soft summer-club-packsSection" id="summer-club-packs">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">{{ __('ui.summer_club.packs_eyebrow') }}</span>
                <h2 class="summer-club-heading">{{ __('ui.summer_club.packs_title') }}</h2>
            </div>

            <div class="summer-club-grid3">
                <div class="summer-club-card summer-club-pack">
                    <h3 class="summer-club-packName">{{ __('ui.summer_club.pack_essential') }}</h3>
                    <p class="summer-club-packFeature">{{ __('ui.summer_club.pack_essential_feature') }}</p>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.pack_essential_text') }}</p>
                    <a class="summer-club-cardButton" href="#">{{ __('ui.summer_club.choose_pack') }}</a>
                </div>
                <div class="summer-club-card summer-club-pack">
                    <h3 class="summer-club-packName">{{ __('ui.summer_club.pack_duo') }}</h3>
                    <p class="summer-club-packFeature">{{ __('ui.summer_club.pack_duo_feature') }}</p>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.pack_duo_text') }}</p>
                    <a class="summer-club-cardButton" href="#">{{ __('ui.summer_club.choose_pack') }}</a>
                </div>
                <div class="summer-club-card summer-club-pack">
                    <span class="summer-club-packBadge">{{ __('ui.summer_club.recommended') }}</span>
                    <h3 class="summer-club-packName">{{ __('ui.summer_club.pack_complete') }}</h3>
                    <p class="summer-club-packFeature">{{ __('ui.summer_club.pack_complete_feature') }}</p>
                    <p class="summer-club-cardText">{{ __('ui.summer_club.pack_complete_text') }}</p>
                    <a class="summer-club-cardButton" href="#">{{ __('ui.summer_club.choose_pack') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-section">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">{{ __('ui.summer_club.faq_eyebrow') }}</span>
                <h2 class="summer-club-heading">{{ __('ui.summer_club.faq_title') }}</h2>
            </div>

            <div class="summer-club-faqList">
                <div class="summer-club-faqItem">
                    <h3 class="summer-club-question">{{ __('ui.summer_club.faq_1_q') }}</h3>
                    <p class="summer-club-answer">{{ __('ui.summer_club.faq_1_a') }}</p>
                </div>
                <div class="summer-club-faqItem">
                    <h3 class="summer-club-question">{{ __('ui.summer_club.faq_2_q') }}</h3>
                    <p class="summer-club-answer">{{ __('ui.summer_club.faq_2_a') }}</p>
                </div>
                <div class="summer-club-faqItem">
                    <h3 class="summer-club-question">{{ __('ui.summer_club.faq_3_q') }}</h3>
                    <p class="summer-club-answer">{{ __('ui.summer_club.faq_3_a') }}</p>
                </div>
                <div class="summer-club-faqItem">
                    <h3 class="summer-club-question">{{ __('ui.summer_club.faq_4_q') }}</h3>
                    <p class="summer-club-answer">{{ __('ui.summer_club.faq_4_a') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-cta">
        <div class="za-container">
            <div class="summer-club-ctaBox">
                <h2 class="summer-club-ctaTitle">{{ __('ui.summer_club.cta_title') }}</h2>
                <p class="summer-club-ctaText">
                    {{ __('ui.summer_club.cta_text') }}
                </p>
                <a class="summer-club-actionPrimary" href="#summer-club-packs">{{ __('ui.summer_club.reserve') }}</a>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    window.scrollToSummerClubPacks = function () {
        var packs = document.getElementById('summer-club-packs');
        if (!packs) return;

        packs.scrollIntoView({ behavior: 'smooth', block: 'start' });
        packs.classList.remove('summer-club-packsSection--pulse');
        void packs.offsetWidth;
        packs.classList.add('summer-club-packsSection--pulse');

        var existingToast = document.querySelector('.summer-club-toast');
        if (existingToast) existingToast.remove();

        var toast = document.createElement('div');
        toast.className = 'summer-club-toast';
        toast.textContent = 'Choisissez un pack pour débloquer ce contenu.';
        document.body.appendChild(toast);

        window.setTimeout(function () {
            toast.classList.add('is-visible');
        }, 20);

        window.setTimeout(function () {
            toast.classList.remove('is-visible');
            window.setTimeout(function () {
                toast.remove();
            }, 260);
        }, 2600);
    };
</script>
@endpush
