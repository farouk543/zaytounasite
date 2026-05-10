        @extends('layouts.app')

        @section('content')
<style>
    .za-heroFloatWrap{
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .za-floatingSideIcon{
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        width: 110px;
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(8, 14, 12, 0.72);
        border: 1px solid rgba(212, 176, 86, 0.24);
        box-shadow:
            0 18px 45px rgba(0,0,0,.30),
            inset 0 1px 0 rgba(255,255,255,.05);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        text-decoration: none;
        transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
        overflow: hidden;
    }

    .za-floatingSideIcon::before{
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: radial-gradient(circle at top left, rgba(212,176,86,.18), transparent 45%);
        pointer-events: none;
        z-index: 0;
    }

    .za-floatingSideIcon::after{
        content: "";
        position: absolute;
        inset: -8px;
        border-radius: inherit;
        border: 1px solid rgba(212,176,86,.22);
        opacity: .7;
        animation: zaIconRingPulse 2.2s ease-out infinite;
        pointer-events: none;
        z-index: 0;
    }

    .za-floatingSideIcon:hover{
        box-shadow:
            0 22px 55px rgba(0,0,0,.38),
            0 0 35px rgba(212,176,86,.22),
            inset 0 1px 0 rgba(255,255,255,.06);
        border-color: rgba(212,176,86,.42);
    }

    .za-floatingSideIcon img{
        width: 50px;
        height: 50px;
        object-fit: contain;
        display: block;
        position: relative;
        z-index: 2;
        animation: zaIconInnerPulse 2.8s ease-in-out infinite;
        filter: drop-shadow(0 0 10px rgba(212,176,86,.18));
    }

    .za-floatingSideIcon .za-iconGlow{
        position: absolute;
        inset: 14px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(212,176,86,.18) 0%, rgba(212,176,86,.07) 45%, transparent 72%);
        z-index: 1;
        animation: zaIconGlowBreath 2.6s ease-in-out infinite;
        pointer-events: none;
    }

    .za-floatingSideIcon .za-iconOrbit{
        position: absolute;
        inset: 6px;
        border-radius: 999px;
        border: 1px dashed rgba(212,176,86,.18);
        z-index: 1;
        animation: zaIconOrbitSpin 8s linear infinite;
        pointer-events: none;
    }

    .za-floatingCard--left{
        left: -300px; 
        animation: zaFloatLeft 3.2s ease-in-out infinite;
    }

    .za-floatingCard--right{
        right: -300px; 
        animation: zaFloatRight 3.5s ease-in-out infinite;
    }

    .za-floatingCard--left:hover{
        transform: translateY(-50%) scale(1.08) rotate(-4deg);
    }

    .za-floatingCard--right:hover{
        transform: translateY(-50%) scale(1.08) rotate(4deg);
    }

    @keyframes zaFloatLeft{
        0%,100%{ transform: translateY(-50%) translateY(0) rotate(0deg) scale(1); }
        25%{ transform: translateY(-50%) translateY(-12px) rotate(-2deg) scale(1.03); }
        50%{ transform: translateY(-50%) translateY(6px) rotate(2deg) scale(1.06); }
        75%{ transform: translateY(-50%) translateY(-8px) rotate(-1deg) scale(1.02); }
    }

    @keyframes zaFloatRight{
        0%,100%{ transform: translateY(-50%) translateY(0) rotate(0deg) scale(1); }
        25%{ transform: translateY(-50%) translateY(12px) rotate(2deg) scale(1.03); }
        50%{ transform: translateY(-50%) translateY(-6px) rotate(-2deg) scale(1.06); }
        75%{ transform: translateY(-50%) translateY(8px) rotate(1deg) scale(1.02); }
    }

    @keyframes zaIconInnerPulse{
        0%,100%{
            transform: scale(1);
            filter: drop-shadow(0 0 8px rgba(212,176,86,.16));
        }
        50%{
            transform: scale(1.12);
            filter: drop-shadow(0 0 18px rgba(212,176,86,.34));
        }
    }

    @keyframes zaIconGlowBreath{
        0%,100%{
            transform: scale(0.92);
            opacity: .45;
        }
        50%{
            transform: scale(1.18);
            opacity: .95;
        }
    }

    @keyframes zaIconOrbitSpin{
        from{ transform: rotate(0deg); }
        to{ transform: rotate(360deg); }
    }

    @keyframes zaIconRingPulse{
        0%{
            transform: scale(.88);
            opacity: .75;
        }
        70%{
            transform: scale(1.18);
            opacity: 0;
        }
        100%{
            transform: scale(1.18);
            opacity: 0;
        }
    }

    @media (max-width: 1400px){
        .za-floatingCard--left{ left: -180px; }
        .za-floatingCard--right{ right: -180px; }
    }

    @media (max-width: 1100px){
        .za-floatingSideIcon{
            width: 84px;
            height: 84px;
        }

        .za-floatingSideIcon img{
            width: 38px;
            height: 38px;
        }

        .za-floatingCard--left{ left: -100px; }
        .za-floatingCard--right{ right: -100px; }
    }

    @media (max-width: 900px){
        .za-floatingCard--left{ left: 10px; }
        .za-floatingCard--right{ right: 10px; }
    }

    @media (max-width: 640px){
        .za-floatingSideIcon{
            width: 64px;
            height: 64px;
        }

        .za-floatingSideIcon img{
            width: 28px;
            height: 28px;
        }

        .za-floatingCard--left,
        .za-floatingCard--right{
            top: auto;
            bottom: 18px;
            transform: none;
            animation: none;
        }

        .za-floatingCard--left{ left: 18px; }
        .za-floatingCard--right{ right: 18px; }

        .za-floatingCard--left:hover,
        .za-floatingCard--right:hover{
            transform: scale(1.06);
        }
    }
    .za-leisureSection{
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .za-leisureBg{
        position: absolute;
        inset: 0;
        background-image: url('/images/loisir.png');
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        transform: scale(1.04);
        filter: blur(2px) saturate(0.72) brightness(0.42);
        opacity: 0.55;
        z-index: 0;
    }

    .za-leisureOverlay{
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                rgba(6, 16, 14, 0.86) 0%,
                rgba(6, 16, 14, 0.78) 35%,
                rgba(6, 16, 14, 0.82) 100%
            ),
            radial-gradient(
                circle at center,
                rgba(212,176,86,0.06) 0%,
                rgba(212,176,86,0.03) 28%,
                transparent 55%
            );
        z-index: 1;
    }
    .za-tracks{
    position: relative;
    overflow: hidden;
    isolation: isolate;
}

.za-tracksBg{
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transform: scale(1.02);
    animation: zaTracksBgFloat 16s ease-in-out infinite alternate;
    will-change: transform;
    z-index: 0;
}

.za-tracksOverlay{
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            180deg,
            rgba(6, 14, 12, 0.78) 0%,
            rgba(6, 14, 12, 0.70) 35%,
            rgba(6, 14, 12, 0.82) 100%
        );
    z-index: 1;
}

.za-tracksGlow{
    position: absolute;
    inset: -20%;
    z-index: 1;
    pointer-events: none;
    background:
        radial-gradient(circle at 20% 25%, rgba(212,176,86,0.12), transparent 22%),
        radial-gradient(circle at 82% 32%, rgba(212,176,86,0.08), transparent 18%),
        radial-gradient(circle at 50% 100%, rgba(255,255,255,0.04), transparent 24%);
    animation: zaTracksGlowMove 12s ease-in-out infinite alternate;
}

.za-tracks .za-container{
    position: relative;
    z-index: 2;
}

.za-tracksHead{
    animation: zaTracksHeadIn 1s cubic-bezier(.22,1,.36,1);
}

.za-tracksTitle{
    position: relative;
    display: inline-block;
}

.za-tracksTitle::after{
    content: "";
    position: absolute;
    left: 50%;
    bottom: -12px;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(212,176,86,0.0), rgba(212,176,86,0.95), rgba(212,176,86,0.0));
    animation: zaTracksLineGrow 1.2s ease forwards;
    animation-delay: .35s;
}

.za-tracksGrid{
    position: relative;
    z-index: 2;
}

.za-trackCard{
    position: relative;
    overflow: hidden;
    border-radius: 28px;
    transform: translateY(0);
    transition:
        transform .45s cubic-bezier(.22,1,.36,1),
        box-shadow .45s ease,
        border-color .35s ease,
        background-color .35s ease;
    will-change: transform;
}

.za-trackCard::before{
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0;
    background: linear-gradient(
        120deg,
        transparent 0%,
        rgba(255,255,255,0.05) 35%,
        rgba(255,255,255,0.12) 50%,
        rgba(255,255,255,0.05) 65%,
        transparent 100%
    );
    transform: translateX(-120%);
    transition: opacity .3s ease;
    z-index: 3;
}

.za-trackCard:hover{
    transform: translateY(-10px) scale(1.015);
    box-shadow:
        0 26px 60px rgba(0,0,0,.30),
        0 10px 30px rgba(212,176,86,.10);
}

.za-trackCard:hover::before{
    opacity: 1;
    animation: zaCardShine 1.2s ease;
}

.za-trackMedia{
    overflow: hidden;
}

.za-trackMedia img{
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1);
    transition: transform .7s cubic-bezier(.22,1,.36,1), filter .45s ease;
    will-change: transform;
}

.za-trackCard:hover .za-trackMedia img{
    transform: scale(1.06);
    filter: saturate(1.03) contrast(1.03);
}

.za-trackBtn{
    transition:
        transform .3s ease,
        background-color .3s ease,
        color .3s ease,
        box-shadow .3s ease;
}

.za-trackCard:hover .za-trackBtn{
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0,0,0,.14);
}

.za-tracksFooter{
    animation: zaFadeUpSoft 1s ease both;
    animation-delay: .45s;
}

@keyframes zaTracksBgFloat{
    0%{
        transform: scale(1.02) translate3d(0, 0, 0);
    }
    100%{
        transform: scale(1.08) translate3d(0, -10px, 0);
    }
}

@keyframes zaTracksGlowMove{
    0%{
        transform: translate3d(-1%, 0, 0) scale(1);
        opacity: .75;
    }
    100%{
        transform: translate3d(1%, -1.5%, 0) scale(1.04);
        opacity: 1;
    }
}

@keyframes zaTracksHeadIn{
    0%{
        opacity: 0;
        transform: translateY(26px);
    }
    100%{
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes zaTracksLineGrow{
    0%{
        width: 0;
        opacity: 0;
    }
    100%{
        width: 180px;
        opacity: 1;
    }
}

@keyframes zaCardShine{
    0%{
        transform: translateX(-120%);
    }
    100%{
        transform: translateX(120%);
    }
}

@keyframes zaFadeUpSoft{
    0%{
        opacity: 0;
        transform: translateY(18px);
    }
    100%{
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

    <!-- HERO (Campus background) -->
    <section class="za-hero" id="top" aria-label="Zaytouna Academy Hero">
        <div class="za-heroMedia" aria-hidden="true"
             style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');"></div>
        <div class="za-heroOverlay" aria-hidden="true"></div>

        <div class="za-container za-heroInner">
            <div class="za-heroFloatWrap">
                <a href="{{ route('regimes.index') }}"
   class="za-floatingSideIcon za-floatingCard--left"
   aria-label="Cours live">
    <span class="za-iconGlow"></span>
    <span class="za-iconOrbit"></span>
    <img src="{{ asset('images/live_icon.png') }}" alt="">
</a>

                <div class="za-heroCard" data-reveal>
                    <div class="za-badge">
                        <span class="dot"></span>
                        <span>{{ __('ui.home.hero_badge') }}</span>
                    </div>

                    <h1 class="za-heroTitle">{!! __('ui.home.hero_title_html') !!}</h1>

                    <p class="za-heroText">{{ __('ui.home.hero_text') }}</p>

                    <div class="za-heroActions" data-reveal="delay-1">
                        @auth
                            <a class="btn-primary za-btnLg" href="{{ route('dashboard') }}">
                                {{ __('ui.home.go_dashboard') }}
                            </a>
                        @else
                            <a class="btn-primary za-btnLg" href="{{ route('register') }}">{{ __('ui.nav.get_started') }}</a>
                            <a class="btn-outline za-btnLg" href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                        @endauth
                    </div>

                    <div class="za-heroStats">
                        <div class="stat" data-reveal="delay-1">
                            <div class="statNum">60+</div>
                            <div class="statLabel">{{ __('ui.home.stats_tracks') }}</div>
                        </div>
                        <div class="stat" data-reveal="delay-2">
                            <div class="statNum">600+</div>
                            <div class="statLabel">{{ __('ui.home.stats_courses') }}</div>
                        </div>
                        <div class="stat" data-reveal="delay-3">
                            <div class="statNum">500+</div>
                            <div class="statLabel">{{ __('ui.home.stats_live_packs') }}</div>
                        </div>
                    </div>
                </div>

<a href="{{ route('catalog') }}"
   class="za-floatingSideIcon za-floatingCard--right"
   aria-label="Cours enregistrés">
    <span class="za-iconGlow"></span>
    <span class="za-iconOrbit"></span>
    <img src="{{ asset('images/cour_icon.png') }}" alt="">
</a>
            </div>
        </div>
    </section>

    <!-- SECTION: 3 cards -->
    <section class="za-section" data-reveal>
        <div class="za-container">
            <div class="za-sectionHead" data-reveal>
                <div class="pill">{{ __('ui.home.section1_pill') }}</div>
                <h2 class="za-h2">{{ __('ui.home.section1_title') }}</h2>
                <p class="za-muted">{{ __('ui.home.section1_text') }}</p>
            </div>

            <div class="za-grid3">
                <div class="lux-card" data-reveal="delay-1">
                    <div class="lux-num">01</div>
                    <div class="lux-title">{{ __('ui.home.card1_title') }}</div>
                    <p class="lux-text">{{ __('ui.home.card1_text') }}</p>
                    <div class="za-cardCta">
                        <a class="za-link" href="#tracks">
                            {{ __('ui.home.explore_arrow') }}
                        </a>
                    </div>
                </div>

                <div class="lux-card" data-reveal="delay-2">
                    <div class="lux-num">02</div>
                    <div class="lux-title">{{ __('ui.home.card2_title') }}</div>
                    <p class="lux-text">{{ __('ui.home.card2_text') }}</p>
                    <div class="za-cardCta">
                        <a class="za-link" href="{{ route('catalog') }}">
                            {{ __('ui.home.browse_arrow') }}
                        </a>
                    </div>
                </div>

                <div class="lux-card lux-card--story" data-reveal="delay-3">
                    <div class="lux-num">03</div>
                    <div class="lux-title">{{ __('ui.home.story_card_title') }}</div>
                    <p class="lux-text">{{ __('ui.home.story_card_text') }}</p>
                    <div class="za-cardCta">
                        <button
                            type="button"
                            class="za-link za-linkBtn"
                            onclick="openFounderModal()"
                            aria-haspopup="dialog"
                            aria-controls="founderModal"
                        >
                            {{ __('ui.home.story_card_cta') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Founder Modal -->
    <div id="founderModal" class="za-modal" aria-hidden="true">
        <div class="za-modalBackdrop" onclick="closeFounderModal()"></div>

        <div
            class="za-modalDialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="founderModalTitle"
            aria-describedby="founderModalDesc"
        >
            <button
                class="za-modalClose"
                type="button"
                onclick="closeFounderModal()"
                aria-label="{{ __('ui.close') }}"
            >
                ×
            </button>

            <div class="za-modalBadge">{{ __('ui.home.founder_badge') }}</div>
            <h3 id="founderModalTitle" class="za-modalTitle">{{ __('ui.home.founder_title') }}</h3>

            <div id="founderModalDesc" class="za-modalText">
                <p>{{ __('ui.home.founder_text_1') }}</p>
                <p>{{ __('ui.home.founder_text_2') }}</p>
            </div>

            <div class="za-modalQuote">
                {{ __('ui.home.founder_quote') }}
            </div>
        </div>
    </div>

    <!-- Chess Modal -->
    <div id="chessModal" class="za-modal" aria-hidden="true">
        <div class="za-modalBackdrop" onclick="closeChessModal()"></div>

        <div
            class="za-modalDialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="chessModalTitle"
            aria-describedby="chessModalDesc"
        >
            <button
                class="za-modalClose"
                type="button"
                onclick="closeChessModal()"
                aria-label="{{ __('ui.close') }}"
            >
                ×
            </button>

            <div class="za-modalBadge">{{ __('ui.home.chess_modal_badge') }}</div>
            <h3 id="chessModalTitle" class="za-modalTitle">{{ __('ui.home.chess_modal_title') }}</h3>

            <div id="chessModalDesc" class="za-modalText">
                <p>{{ __('ui.home.chess_modal_text') }}</p>
            </div>

            <div class="za-grid3 mt-6">
                <div class="lux-card">
                    <div class="lux-num">01</div>
                    <div class="lux-title">{{ __('ui.home.chess_pack_1_title') }}</div>
                    <p class="lux-text">{{ __('ui.home.chess_pack_1_text') }}</p>
                </div>

                <div class="lux-card">
                    <div class="lux-num">02</div>
                    <div class="lux-title">{{ __('ui.home.chess_pack_2_title') }}</div>
                    <p class="lux-text">{{ __('ui.home.chess_pack_2_text') }}</p>
                </div>

                <div class="lux-card">
                    <div class="lux-num">03</div>
                    <div class="lux-title">{{ __('ui.home.chess_pack_3_title') }}</div>
                    <p class="lux-text">{{ __('ui.home.chess_pack_3_text') }}</p>
                </div>
            </div>

            <div class="za-center mt-8">
                <a href="{{ route('catalog') }}" class="btn-primary za-btnLg">
                    {{ __('ui.home.chess_modal_cta') }}
                </a>
            </div>
        </div>
    </div>

    {{-- EDUCATIONAL TRACKS --}}
<section class="za-section za-tracks" id="tracks" aria-label="Educational Tracks">
    <div class="za-tracksBg" aria-hidden="true"
         style="background-image:url('{{ asset('assets/hero/hero-campus1.webp') }}');"></div>
    <div class="za-tracksOverlay" aria-hidden="true"></div>
    <div class="za-tracksGlow" aria-hidden="true"></div>

    <div class="za-container">
        <div class="za-sectionHead za-tracksHead" data-reveal>
            <h2 class="za-h2 za-tracksTitle">{{ __('ui.home.tracks_title') }}</h2>
            <p class="za-muted">{{ __('ui.home.tracks_subtitle') }}</p>
        </div>

        <div class="za-tracksGrid">
            <a href="{{ route('regimes.tunisia.show') }}"
               class="za-trackCard"
               data-reveal="delay-1"
               aria-label="{{ __('ui.home.track_tunisia') }}">
                <div class="za-trackMedia">
                    <img src="{{ asset('images/tunisie.png') }}"
                         alt="{{ __('ui.home.track_tunisia') }}"
                         loading="lazy">
                </div>
                <div class="za-trackBody">
                    <h3 class="za-trackName">{{ __('ui.home.track_tunisia') }}</h3>
                    <p class="za-trackText">{{ __('ui.home.track_tunisia_text') }}</p>
                    <span class="za-trackBtn">{{ __('ui.view') }}</span>
                </div>
            </a>

            <a href="{{ route('regimes.qatar.show') }}"
               class="za-trackCard"
               data-reveal="delay-2"
               aria-label="{{ __('ui.home.track_qatar') }}">
                <div class="za-trackMedia">
                    <img src="{{ asset('images/qatar.png') }}"
                         alt="{{ __('ui.home.track_qatar') }}"
                         loading="lazy">
                </div>
                <div class="za-trackBody">
                    <h3 class="za-trackName">{{ __('ui.home.track_qatar') }}</h3>
                    <p class="za-trackText">{{ __('ui.home.track_qatar_text') }}</p>
                    <span class="za-trackBtn">{{ __('ui.view') }}</span>
                </div>
            </a>

            <a href="{{ route('regimes.saudi.show') }}"
               class="za-trackCard"
               data-reveal="delay-3"
               aria-label="{{ __('ui.home.track_saudi') }}">
                <div class="za-trackMedia">
                    <img src="{{ asset('images/saudite.png') }}"
                         alt="{{ __('ui.home.track_saudi') }}"
                         loading="lazy">
                </div>
                <div class="za-trackBody">
                    <h3 class="za-trackName">{{ __('ui.home.track_saudi') }}</h3>
                    <p class="za-trackText">{{ __('ui.home.track_saudi_text') }}</p>
                    <span class="za-trackBtn">{{ __('ui.view') }}</span>
                </div>
            </a>

            <a href="{{ route('regimes.quran.show') }}"
               class="za-trackCard"
               data-reveal="delay-4"
               aria-label="{{ __('ui.home.track_quran') }}">
                <div class="za-trackMedia">
                    <img src="{{ asset('images/Quran.png') }}"
                         alt="{{ __('ui.home.track_quran') }}"
                         loading="lazy">
                </div>
                <div class="za-trackBody">
                    <h3 class="za-trackName">{{ __('ui.home.track_quran') }}</h3>
                    <p class="za-trackText">{{ __('ui.home.track_quran_text') }}</p>
                    <span class="za-trackBtn">{{ __('ui.view') }}</span>
                </div>
            </a>
        </div>

        <div class="za-tracksFooter" data-reveal="delay-2">
            <a class="btn-dark za-btnLg" href="{{ route('regimes.index') }}">
                {{ __('ui.home.see_all_systems') }}
            </a>
        </div>
    </div>
</section>

    <!-- SECTION: mini catalog cards -->
    <section class="za-section za-sectionAlt" data-reveal>
        <div class="za-container">
            <div class="za-sectionHead" data-reveal>
                <div class="pill">{{ __('ui.home.curated_pill') }}</div>
                <h2 class="za-h2">{{ __('ui.home.catalog_title') }}</h2>
                <p class="za-muted">{{ __('ui.home.catalog_subtitle') }}</p>
            </div>

            <div class="za-grid3">
                <div class="za-programCard" data-reveal="delay-1">
                    <div class="za-programMedia" data-media="elementary"></div>
                    <div class="za-programBody">
                        <div class="za-programTitle">{{ __('ui.home.program_elementary') }}</div>
                    </div>
                </div>

                <div class="za-programCard" data-reveal="delay-2">
                    <div class="za-programMedia" data-media="middle"></div>
                    <div class="za-programBody">
                        <div class="za-programTitle">{{ __('ui.home.program_middle') }}</div>
                    </div>
                </div>

                <div class="za-programCard" data-reveal="delay-3">
                    <div class="za-programMedia" data-media="high"></div>
                    <div class="za-programBody">
                        <div class="za-programTitle">{{ __('ui.home.program_high') }}</div>
                    </div>
                </div>
            </div>

            <div class="za-center mt-10" data-reveal="delay-2">
                <a class="btn-dark za-btnLg" href="{{ route('catalog') }}">{{ __('ui.home.view_full_catalog') }}</a>
            </div>
        </div>
    </section>

 <section class="za-section za-leisureSection" data-reveal>
    <div class="za-leisureBg"></div>
    <div class="za-leisureOverlay"></div>

    <div class="za-container" style="position: relative; z-index: 2;">
        <div class="za-sectionHead" data-reveal>
            <div class="pill">{{ __('ui.home.leisure_pill') }}</div>
            <h2 class="za-h2" style="color: #fff;">{{ __('ui.home.activities_title') }}</h2>
            <p class="za-muted" style="color: rgba(255,255,255,.82);">
                {{ __('ui.home.activities_text') }}
            </p>
        </div>

        <div class="activities-grid">
            <div class="za-programCard activities-card" data-reveal="delay-1">
                <div class="activities-media">
                    <img src="{{ asset('images/echec.png') }}" alt="{{ __('ui.home.chess_title') }} Zaytouna Academy" loading="lazy">
                </div>

                <div class="activities-body">
                    <span class="activities-badge">{{ __('ui.home.chess_badge') }}</span>
                    <h3 class="activities-title">{{ __('ui.home.chess_title') }}</h3>
                    <p class="activities-text">
                        {{ __('ui.home.chess_text_updated') }}
                    </p>

                    <div class="za-cardCta mt-4">
                        <button
                            type="button"
                            class="btn-dark za-btnLg"
                            onclick="openChessModal()"
                            aria-haspopup="dialog"
                            aria-controls="chessModal"
                        >
                            {{ __('ui.home.chess_cta') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="za-programCard activities-card" data-reveal="delay-2">
                <div class="activities-media">
                    <img src="{{ asset('images/summer_club.png') }}" alt="{{ __('ui.home.summer_club_alt') }}" loading="lazy">
                </div>

                <div class="activities-body">
                    <span class="activities-badge">{{ __('ui.home.summer_club_badge') }}</span>
                    <h3 class="activities-title">{{ __('ui.home.summer_club_title') }}</h3>
                    <p class="activities-text">
                        {{ __('ui.home.summer_club_text') }}
                    </p>

                    <div class="za-cardCta mt-4">
                        <a class="btn-dark za-btnLg" href="{{ route('club.ete') }}">
                            {{ __('ui.home.summer_club_cta') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- CTA -->
    <section class="za-section za-cta" data-reveal>
        <div class="za-container">
            <div class="cta-card" data-reveal>
                <div data-reveal="delay-1">
                    <div class="pill">{{ __('ui.home.cta_pill') }}</div>
                    <h3 class="za-h3">{{ __('ui.home.cta_title') }}</h3>
                    <p class="za-muted">{{ __('ui.home.cta_text') }}</p>
                </div>

                <div class="za-ctaActions" data-reveal="delay-2">
                    @auth
                        <a class="btn-primary za-btnLg" href="{{ route('dashboard') }}">{{ __('ui.nav.dashboard') }}</a>
                    @else
                        <a class="btn-primary za-btnLg" href="{{ route('register') }}">{{ __('ui.nav.get_started') }}</a>
                        <a class="btn-outline za-btnLg" href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    let lastFocusedElement = null;

    function getModalElements(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return {};

        const dialog = modal.querySelector('.za-modalDialog');
        const closeBtn = modal.querySelector('.za-modalClose');
        const focusable = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        return { modal, dialog, closeBtn, focusable };
    }

    function openModal(modalId) {
        const { modal, closeBtn } = getModalElements(modalId);
        if (!modal) return;

        lastFocusedElement = document.activeElement;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('za-modalOpen');

        if (closeBtn) {
            setTimeout(() => closeBtn.focus(), 50);
        }
    }

    function closeModal(modalId) {
        const { modal } = getModalElements(modalId);
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('za-modalOpen');

        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    }

    function openFounderModal() {
        openModal('founderModal');
    }

    function closeFounderModal() {
        closeModal('founderModal');
    }

    function openChessModal() {
        openModal('chessModal');
    }

    function closeChessModal() {
        closeModal('chessModal');
    }

    document.addEventListener('keydown', function (e) {
        const modalIds = ['founderModal', 'chessModal'];

        for (const modalId of modalIds) {
            const { modal, focusable } = getModalElements(modalId);
            if (!modal || !modal.classList.contains('is-open')) continue;

            if (e.key === 'Escape') {
                closeModal(modalId);
                return;
            }

            if (e.key === 'Tab' && focusable.length > 0) {
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }

            return;
        }
    });

    document.addEventListener('click', function (e) {
        const modalIds = ['founderModal', 'chessModal'];

        for (const modalId of modalIds) {
            const { modal } = getModalElements(modalId);
            if (!modal || !modal.classList.contains('is-open')) continue;

            if (e.target.classList.contains('za-modalBackdrop')) {
                closeModal(modalId);
                return;
            }
        }
    });
</script>
@endpush
