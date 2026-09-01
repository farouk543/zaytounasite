@extends('layouts.app')

@section('content')
@php
    $filterData = [
        'systems' => $systems ?? [],
        'filters' => [
            ['key' => 'all', 'label' => __('ui.regimes_index.filters.all')],
            ['key' => 'tunisia', 'label' => __('ui.regimes_index.filters.tunisia')],
            ['key' => 'qatar', 'label' => __('ui.regimes_index.filters.qatar')],
            ['key' => 'saudi', 'label' => __('ui.regimes_index.filters.saudi')],
            ['key' => 'quran', 'label' => __('ui.regimes_index.filters.quran')],
            ['key' => 'france', 'label' => __('ui.regimes_index.filters.france')],
        ],
    ];
@endphp

<section class="za-section za-tracksPage" aria-label="{{ __('ui.regimes_index.aria_label') }}">
    <div class="za-tracksBg" aria-hidden="true"
         style="background-image:url('{{ asset('assets/hero/hero-campus1.webp') }}');"></div>
    <div class="za-tracksOverlay" aria-hidden="true"></div>

    <script type="application/json" id="systemsIndexData">
        {!! json_encode($filterData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <div class="za-container"
         x-data="systemsIndex(JSON.parse(document.getElementById('systemsIndexData').textContent))"
         style="position:relative; z-index:2;">

        <div class="za-sectionHead za-tracksHead" data-reveal>
            <div class="pill">{{ __('ui.regimes_index.pill') }}</div>
            <h1 class="za-h2 za-tracksTitle">{{ __('ui.regimes_index.title') }}</h1>
            <p class="za-muted">{{ __('ui.regimes_index.subtitle') }}</p>
        </div>

        <div class="lux-card za-systemsFilterBox" data-reveal="delay-1">
            <div class="za-systemsFilterTop">
                <div>
                    <div class="za-filterTitle">{{ __('ui.regimes_index.filter_title') }}</div>
                    <div class="za-muted">{{ __('ui.regimes_index.filter_subtitle') }}</div>
                </div>

                <button type="button" class="btn-outline" @click="selected = 'all'">
                    {{ __('ui.regimes_index.reset') }}
                </button>
            </div>

            <div class="za-systemsFilterChips">
                <template x-for="item in filters" :key="item.key">
                    <button type="button"
                            class="za-filterChip"
                            :class="selected === item.key ? 'is-active' : ''"
                            @click="selected = item.key"
                            x-text="item.label">
                    </button>
                </template>
            </div>

            <div class="za-muted" style="margin-top:12px;">
                <strong x-text="filteredSystems.length"></strong> {{ __('ui.regimes_index.results_found') }}
            </div>
        </div>

        <div class="za-tracksGrid za-systemsGrid">
            <template x-for="system in filteredSystems" :key="system.key">
                <article class="za-trackCard za-systemCard">
                    <div class="za-trackMedia za-systemMedia">
                        <img :src="system.image" :alt="system.title" loading="lazy">
                    </div>

                    <div class="za-trackBody">
                        <h3 class="za-trackName" x-text="system.title"></h3>
                        <p class="za-trackText" x-text="system.text"></p>

                        <a class="za-systemBtn" :href="system.route">
                            {{ __('ui.regimes_index.view') }}
                        </a>
                    </div>
                </article>
            </template>
        </div>

        <div class="lux-card" x-show="filteredSystems.length === 0" style="padding:22px; margin-top:22px;">
            <div class="lux-title">{{ __('ui.regimes_index.empty_title') }}</div>
            <p class="za-muted" style="margin-top:8px;">
                {{ __('ui.regimes_index.empty_text') }}
            </p>
        </div>
    </div>
</section>

<div data-reveal>
    <x-footer-premium />
</div>

<script>
    function systemsIndex(data) {
        return {
            systems: data.systems || [],
            filters: data.filters || [],
            selected: 'all',

            get filteredSystems() {
                if (this.selected === 'all') {
                    return this.systems;
                }
                return this.systems.filter(item => item.key === this.selected);
            },
        }
    }
</script>

<style>
    .za-tracksPage{
        position: relative;
        overflow: hidden;
    }

    .za-systemsFilterBox{
        padding: 22px;
        margin: 26px auto 0;
        max-width: 980px;
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(255,255,255,.35);
        box-shadow: 0 18px 55px rgba(0,0,0,.14);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .za-systemsFilterTop{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .za-filterTitle{
        font-size: 18px;
        font-weight: 900;
        color: #0A1410;
        margin-bottom: 4px;
    }

    .za-systemsFilterChips{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        margin-top:16px;
    }

    .za-filterChip{
        border:none;
        border-radius:999px;
        padding:11px 18px;
        font-weight:700;
        background: rgba(255,255,255,.82);
        color:#0b3b2e;
        box-shadow: inset 0 0 0 1px rgba(11,59,46,.16);
        transition: .25s ease;
        cursor:pointer;
    }

    .za-filterChip:hover{
        transform: translateY(-1px);
        box-shadow: inset 0 0 0 1px rgba(11,59,46,.24), 0 10px 18px rgba(0,0,0,.06);
    }

    .za-filterChip.is-active{
        background: #064e3b;
        color: #fff;
        box-shadow: 0 14px 24px rgba(6,78,59,.24);
    }

    .za-systemsGrid{
        margin-top: 34px;
    }

    .za-systemCard{
        overflow: hidden;
        border-radius: 28px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 20px 55px rgba(0,0,0,.18);
        border: 1px solid rgba(255,255,255,.26);
    }

    .za-systemMedia{
        height: 180px;
        overflow: hidden;
    }

    .za-systemMedia img{
        width:100%;
        height:100%;
        object-fit:cover;
        display:block;
    }

    .za-systemBtn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:110px;
        margin-top:18px;
        padding:12px 24px;
        border-radius:16px;
        background:#064e3b;
        color:#fff;
        font-weight:800;
        text-decoration:none;
        transition:.25s ease;
    }

    .za-systemBtn:hover{
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(6,78,59,.25);
    }

    @media (max-width: 768px){
        .za-systemMedia{
            height: 160px;
        }
    }
</style>
@endsection