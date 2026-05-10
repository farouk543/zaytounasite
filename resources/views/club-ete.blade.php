@extends('layouts.app')

@section('title', __('ui.summer_club.page_title'))

@section('content')
<div class="summer-club-page">
    @if(session('success'))
        <div class="summer-club-successBanner">{{ session('success') }}</div>
    @endif

    <section class="summer-club-hero">
        <div class="za-container">
            <div class="summer-club-heroGrid">
                <div>
                    <span class="summer-club-kicker">{{ __('ui.summer_club.hero_badge') }}</span>
                    <h1 class="summer-club-title">{{ __('ui.summer_club.hero_title') }}</h1>
                    <p class="summer-club-subtitle">{{ __('ui.summer_club.hero_text') }}</p>

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

    <section class="summer-club-section summer-club-catalogueSection">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">Club d’été</span>
                <h2 class="summer-club-heading">Aperçu du catalogue pédagogique</h2>
                <p class="summer-club-lead">
                    Quatre formations mises en avant, verrouillées jusqu’à confirmation de l’abonnement Club d’été.
                </p>
            </div>

            @if($resources->isNotEmpty())
                <div class="summer-club-featuredGrid">
                    @foreach($resources as $resource)
                        @php
                            $coverUrl = $resource->cover_image_path
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($resource->cover_image_path)
                                : null;
                        @endphp

                        <button
                            type="button"
                            class="summer-club-resourceCard summer-club-resourceCard--formation"
                            onclick="window.scrollToSummerClubPacks && window.scrollToSummerClubPacks()"
                        >
                            <span class="summer-club-resourceMedia" aria-hidden="true">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="" loading="lazy">
                                @else
                                    <span class="summer-club-resourceFallback">@svg('heroicon-o-academic-cap')</span>
                                @endif

                                <span class="summer-club-resourceType">Formation</span>
                            </span>

                            <span class="summer-club-resourceBody">
                                <span class="summer-club-resourceTitle">{{ $resource->title }}</span>
                                <span class="summer-club-resourceMeta">
                                    @if($resource->subject)
                                        <span>{{ $resource->subject }}</span>
                                    @endif
                                    @if($resource->level)
                                        <span>{{ $resource->level }}</span>
                                    @endif
                                </span>

                                @if($resource->description)
                                    <span class="summer-club-resourceDescription">
                                        {{ \Illuminate\Support\Str::limit($resource->description, 95) }}
                                    </span>
                                @endif

                                <span class="summer-club-lockBadge">
                                    @svg('heroicon-o-lock-closed')
                                    Disponible après confirmation
                                </span>
                                <span class="summer-club-disabledButton">Débloquer avec un pack</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="summer-club-emptyCatalogue">Le catalogue Club d’été sera bientôt disponible.</div>
            @endif
        </div>
    </section>

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

            <div class="summer-club-grid3 summer-club-packGrid">
                @foreach($packs as $packKey => $pack)
                    <div class="summer-club-card summer-club-pack">
                        @if($pack['badge'])
                            <span class="summer-club-packBadge">{{ $pack['badge'] }}</span>
                        @endif

                        <h3 class="summer-club-packName">{{ $pack['name'] }}</h3>
                        <p class="summer-club-packFeature">{{ $pack['feature'] }}</p>

                        <div class="summer-club-priceLine">
                            <span class="summer-club-price">{{ number_format($pack['price'], 0) }} DT</span>
                            @if($pack['old_price'])
                                <span class="summer-club-oldPrice">{{ number_format($pack['old_price'], 0) }} DT</span>
                                <span class="summer-club-saving">Économie {{ number_format($pack['old_price'] - $pack['price'], 0) }} DT</span>
                            @endif
                        </div>

                        <p class="summer-club-duration">Durée : {{ $pack['duration_months'] }} mois</p>
                        <p class="summer-club-cardText">{{ $pack['description'] }}</p>
                        <button
                            type="button"
                            class="summer-club-cardButton"
                            data-pack-key="{{ $packKey }}"
                            onclick="window.openSummerClubRequestModal && window.openSummerClubRequestModal(this.dataset.packKey)"
                        >
                            Choisir ce pack
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="summer-club-modal" id="summer-club-subscription-modal" aria-hidden="true">
        <div class="summer-club-modalBackdrop" data-close-summer-modal></div>
        <div class="summer-club-modalPanel" role="dialog" aria-modal="true" aria-labelledby="summer-club-modal-title">
            <button type="button" class="summer-club-modalClose" data-close-summer-modal aria-label="Fermer">@svg('heroicon-o-x-mark')</button>

            <div class="summer-club-modalHead">
                <span class="summer-club-eyebrow">Demande d’abonnement</span>
                <h2 class="summer-club-modalTitle" id="summer-club-modal-title"></h2>
                <p class="summer-club-modalMeta" id="summer-club-modal-meta"></p>
            </div>

            <form class="summer-club-requestForm" id="summer-club-request-form" method="POST" action="{{ route('club-ete.subscription-request.store') }}">
                @csrf
                <input type="hidden" name="pack_key" id="summer-club-pack-key">

                <div class="summer-club-formGrid">
                    <label>
                        <span>Nom parent</span>
                        <input type="text" name="parent_name" required>
                    </label>
                    <label>
                        <span>Nom élève</span>
                        <input type="text" name="student_name" required>
                    </label>
                    <label>
                        <span>Téléphone</span>
                        <input type="tel" name="phone" required>
                    </label>
                    <label>
                        <span>Email optionnel</span>
                        <input type="email" name="email">
                    </label>
                </div>

                <div class="summer-club-subjectBlock" id="summer-club-subject-block">
                    <span class="summer-club-subjectTitle" id="summer-club-subject-title"></span>
                    <div class="summer-club-subjectChoices" id="summer-club-subject-choices"></div>
                </div>

                <div class="summer-club-formMessage" id="summer-club-form-message"></div>

                <div class="summer-club-modalActions">
                    <button type="submit" class="summer-club-submitButton">Envoyer la demande</button>
                    <button type="button" class="summer-club-cancelButton" data-close-summer-modal>Annuler</button>
                </div>
            </form>
        </div>
    </div>

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
                <p class="summer-club-ctaText">{{ __('ui.summer_club.cta_text') }}</p>
                <a class="summer-club-actionPrimary" href="#summer-club-packs">{{ __('ui.summer_club.reserve') }}</a>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    window.summerClubPacks = @json($packs);
    window.summerClubSubjects = @json($subjects);

    window.scrollToSummerClubPacks = function () {
        var packs = document.getElementById('summer-club-packs');
        if (!packs) return;

        packs.scrollIntoView({ behavior: 'smooth', block: 'start' });
        packs.classList.remove('summer-club-packsSection--pulse');
        void packs.offsetWidth;
        packs.classList.add('summer-club-packsSection--pulse');

        window.showSummerClubToast('Choisissez un pack pour débloquer ce contenu.');
    };

    window.showSummerClubToast = function (message) {
        var existingToast = document.querySelector('.summer-club-toast');
        if (existingToast) existingToast.remove();

        var toast = document.createElement('div');
        toast.className = 'summer-club-toast';
        toast.textContent = message;
        document.body.appendChild(toast);

        window.setTimeout(function () {
            toast.classList.add('is-visible');
        }, 20);

        window.setTimeout(function () {
            toast.classList.remove('is-visible');
            window.setTimeout(function () {
                toast.remove();
            }, 260);
        }, 3000);
    };

    window.openSummerClubRequestModal = function (packKey) {
        var modal = document.getElementById('summer-club-subscription-modal');
        var form = document.getElementById('summer-club-request-form');
        var pack = window.summerClubPacks[packKey];
        if (!modal || !form || !pack) return;

        form.reset();
        document.getElementById('summer-club-pack-key').value = packKey;
        document.getElementById('summer-club-modal-title').textContent = pack.name;
        document.getElementById('summer-club-modal-meta').textContent = pack.price + ' DT · Durée : ' + pack.duration_months + ' mois';
        document.getElementById('summer-club-form-message').textContent = '';
        document.getElementById('summer-club-form-message').className = 'summer-club-formMessage';

        renderSummerClubSubjects(packKey, pack);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('summer-club-modalOpen');
    };

    function closeSummerClubModal() {
        var modal = document.getElementById('summer-club-subscription-modal');
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('summer-club-modalOpen');
    }

    function renderSummerClubSubjects(packKey, pack) {
        var title = document.getElementById('summer-club-subject-title');
        var choices = document.getElementById('summer-club-subject-choices');
        choices.innerHTML = '';

        if (packKey === 'complete') {
            title.textContent = 'Matières incluses';
            window.summerClubSubjects.forEach(function (subject) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_subjects[]';
                input.value = subject;
                choices.appendChild(input);

                var chip = document.createElement('span');
                chip.className = 'summer-club-fixedSubject';
                chip.textContent = subject;
                choices.appendChild(chip);
            });
            return;
        }

        title.textContent = packKey === 'essential'
            ? 'Sélectionnez 1 matière'
            : 'Sélectionnez 2 matières';

        window.summerClubSubjects.forEach(function (subject) {
            var label = document.createElement('label');
            label.className = 'summer-club-subjectChoice';
            label.innerHTML = '<input type="checkbox" name="selected_subjects[]" value="' + subject + '"><span>' + subject + '</span>';
            choices.appendChild(label);
        });

        choices.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var checked = choices.querySelectorAll('input[type="checkbox"]:checked');
                if (checked.length > pack.subject_count) {
                    checkbox.checked = false;
                }
            });
        });
    }

    document.querySelectorAll('[data-close-summer-modal]').forEach(function (button) {
        button.addEventListener('click', closeSummerClubModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSummerClubModal();
    });

    document.getElementById('summer-club-request-form')?.addEventListener('submit', function (event) {
        event.preventDefault();

        var form = event.currentTarget;
        var message = document.getElementById('summer-club-form-message');
        var submit = form.querySelector('button[type="submit"]');
        var formData = new FormData(form);

        message.textContent = '';
        message.className = 'summer-club-formMessage';
        submit.disabled = true;

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw data;
                    return data;
                });
            })
            .then(function (data) {
                message.textContent = data.message || "Votre demande a été envoyée. L'équipe Zaytouna vous contactera pour confirmer l'abonnement.";
                message.classList.add('is-success');
                form.reset();
                window.setTimeout(closeSummerClubModal, 1800);
                window.showSummerClubToast(message.textContent);
            })
            .catch(function (error) {
                var errors = error.errors || {};
                var firstKey = Object.keys(errors)[0];
                message.textContent = firstKey ? errors[firstKey][0] : "Impossible d'envoyer la demande. Vérifiez les champs.";
                message.classList.add('is-error');
            })
            .finally(function () {
                submit.disabled = false;
            });
    });
</script>
@endpush
