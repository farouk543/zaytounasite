<style>
  :root{
    --zb-ink:#0b1f17; --zb-emerald:#062e20; --zb-emerald2:#0b3b2e; --zb-green:#0f7656;
    --zb-gold:#c9a227; --zb-gold2:#e9cf7f; --zb-cream:#faf7ef; --zb-line:rgba(11,59,46,.10); --zb-muted:#5b6b64;
  }
  html{scroll-behavior:smooth}

  /* ================= Liste des articles ================= */
  .zb-wrap{max-width:1180px;margin:0 auto;padding:0 16px}
  .zb-toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin:-28px auto 32px;position:relative;z-index:3}
  .zb-cats{display:flex;flex-wrap:wrap;gap:8px}
  .zb-chip{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:8px 14px;font-size:.85rem;font-weight:700;
    background:#fff;color:var(--zb-emerald2);border:1px solid rgba(11,59,46,.14);box-shadow:0 6px 18px rgba(0,0,0,.06);text-decoration:none;transition:.2s}
  .zb-chip:hover{border-color:var(--zb-gold);color:var(--zb-emerald)}
  .zb-chip.is-active{background:var(--zb-emerald);color:#fff;border-color:var(--zb-emerald)}
  .zb-chip small{opacity:.6;font-weight:600}
  .zb-search{display:flex;gap:8px;flex:1 1 280px;max-width:420px}
  .zb-search input{flex:1;border-radius:999px;border:1px solid rgba(11,59,46,.18);padding:10px 16px;font-size:.9rem;background:#fff}
  .zb-search input:focus{outline:none;border-color:var(--zb-gold);box-shadow:0 0 0 3px rgba(201,162,39,.18)}
  .zb-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:26px}
  @media (max-width:980px){.zb-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media (max-width:640px){.zb-grid{grid-template-columns:1fr}}
  .zb-card{display:flex;flex-direction:column;background:#fff;border-radius:22px;overflow:hidden;border:1px solid var(--zb-line);
    box-shadow:0 14px 40px rgba(6,46,32,.07);text-decoration:none;color:inherit;transition:transform .3s,box-shadow .3s}
  .zb-card:hover{transform:translateY(-5px);box-shadow:0 26px 60px rgba(6,46,32,.15)}
  .zb-cover{aspect-ratio:1200/630;background:linear-gradient(135deg,var(--zb-emerald),var(--zb-emerald2));position:relative;overflow:hidden}
  .zb-cover img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s cubic-bezier(.2,.7,.2,1)}
  .zb-card:hover .zb-cover img{transform:scale(1.06)}
  .zb-cover::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 55%,rgba(4,20,14,.45))}
  .zb-cover .zb-coverMark{position:absolute;inset:0;display:grid;place-items:center;color:rgba(201,162,39,.85);font:700 1.4rem 'Playfair Display',serif}
  .zb-badge{position:absolute;top:12px;inset-inline-start:12px;z-index:2;background:linear-gradient(135deg,#d4b056,#f4df9a);color:var(--zb-emerald);
    border-radius:999px;padding:5px 11px;font-size:.72rem;font-weight:900;box-shadow:0 6px 16px rgba(0,0,0,.2)}
  .zb-body{padding:20px 22px 22px;display:flex;flex-direction:column;gap:10px;flex:1}
  .zb-meta{display:flex;flex-wrap:wrap;gap:8px;align-items:center;font-size:.76rem;color:#64748b;font-weight:600}
  .zb-cat{color:var(--zb-green);text-transform:uppercase;letter-spacing:.05em;font-weight:800}
  .zb-title{font:700 1.18rem/1.35 'Playfair Display',serif;color:var(--zb-emerald);margin:0}
  .zb-excerpt{font-size:.9rem;line-height:1.6;color:#475569;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
  .zb-more{margin-top:auto;font-weight:800;font-size:.85rem;color:var(--zb-emerald2);display:inline-flex;gap:6px;align-items:center}
  .zb-card:hover .zb-more{color:var(--zb-green)}
  .zb-card--featured{grid-column:1/-1;flex-direction:row}
  .zb-card--featured .zb-cover{flex:0 0 56%;aspect-ratio:auto;min-height:340px}
  .zb-card--featured .zb-body{padding:36px;justify-content:center;gap:14px}
  .zb-card--featured .zb-title{font-size:1.85rem}
  .zb-card--featured .zb-excerpt{-webkit-line-clamp:4;font-size:1rem}
  @media (max-width:820px){.zb-card--featured{flex-direction:column}.zb-card--featured .zb-cover{min-height:0;aspect-ratio:1200/630}.zb-card--featured .zb-body{padding:22px}.zb-card--featured .zb-title{font-size:1.35rem}}
  .zb-forYou{margin-bottom:44px;padding:26px;border-radius:26px;background:linear-gradient(135deg,#fbf6e6,#f3ecd6);border:1px solid rgba(201,162,39,.3)}
  .zb-forYouHead{display:flex;gap:16px;align-items:center;margin-bottom:20px}
  .zb-forYouFlag{font-size:2.4rem;line-height:1}
  .zb-forYouHead h2{font:700 1.45rem 'Playfair Display',serif;color:var(--zb-emerald);margin:0}
  :lang(ar) .zb-forYouHead h2{font-family:'Cairo',sans-serif}
  .zb-forYouHead p{margin:4px 0 0;color:var(--zb-muted);font-size:.9rem}
  .zb-aAudience{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:18px;font-size:.8rem;color:rgba(255,255,255,.65)}
  .zb-aCountry{border-radius:999px;padding:5px 11px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);color:#fff;font-weight:700}
  .zb-empty{text-align:center;padding:60px 20px;color:#64748b;background:#fff;border-radius:22px;border:1px dashed rgba(11,59,46,.2)}
  .zb-pagination{margin-top:36px}

  /* ================= Article : en-tête ================= */
  .zb-progress{position:fixed;top:0;left:0;right:0;height:3px;z-index:70;pointer-events:none}
  .zb-progress span{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--zb-gold),var(--zb-gold2));box-shadow:0 0 12px rgba(201,162,39,.6)}
  .zb-ahero{position:relative;overflow:hidden;color:#fff;padding:122px 16px 64px;
    background:radial-gradient(1200px 500px at 85% -10%,rgba(201,162,39,.22),transparent 60%),
               radial-gradient(900px 500px at -10% 110%,rgba(15,118,86,.35),transparent 60%),
               linear-gradient(160deg,#03130d 0%,#062e20 55%,#0b3b2e 100%)}
  .zb-aheroGlow{position:absolute;inset:0;opacity:.07;pointer-events:none;
    background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px}
  .zb-aheroInner{position:relative;max-width:1180px;margin:0 auto;display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:center}
  .zb-aheroInner.is-solo{grid-template-columns:1fr;max-width:860px;text-align:center}
  .zb-aheroInner.is-solo .zb-aMeta,.zb-aheroInner.is-solo .zb-crumbs{justify-content:center}
  @media (max-width:960px){.zb-aheroInner{grid-template-columns:1fr;gap:30px}.zb-ahero{padding-top:108px}}
  .zb-crumbs{display:flex;flex-wrap:wrap;gap:8px;font-size:.78rem;color:rgba(255,255,255,.6);margin-bottom:18px}
  .zb-crumbs a{color:rgba(255,255,255,.82);text-decoration:none}
  .zb-crumbs a:hover{color:var(--zb-gold2)}
  .zb-aCat{display:inline-block;border:1px solid rgba(233,207,127,.45);background:rgba(201,162,39,.12);color:var(--zb-gold2);
    border-radius:999px;padding:6px 14px;font-size:.74rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;text-decoration:none;margin-bottom:16px}
  .zb-aTitle{font:700 clamp(30px,4.2vw,52px)/1.12 'Playfair Display',serif;margin:0;color:#fff;letter-spacing:-.01em}
  :lang(ar) .zb-aTitle,[dir=rtl] .zb-aTitle{line-height:1.45}
  .zb-aDek{margin:18px 0 0;font-size:1.12rem;line-height:1.65;color:rgba(255,255,255,.8);max-width:620px}
  .zb-aheroInner.is-solo .zb-aDek{margin-inline:auto}
  .zb-aMeta{display:flex;align-items:center;gap:12px;margin-top:26px}
  .zb-aAvatar{width:44px;height:44px;border-radius:50%;background:#fff;padding:4px;object-fit:contain;box-shadow:0 0 0 2px rgba(233,207,127,.55)}
  .zb-aAuthor{font-weight:800;font-size:.92rem}
  .zb-aMetaLine{display:flex;flex-wrap:wrap;gap:6px;font-size:.8rem;color:rgba(255,255,255,.65)}
  .zb-aCover{margin:0;position:relative}
  .zb-aCover::before{content:"";position:absolute;inset:-10px -10px 10px 10px;border:1px solid rgba(233,207,127,.35);border-radius:28px}
  [dir=rtl] .zb-aCover::before{inset:-10px 10px 10px -10px}
  .zb-aCover img{position:relative;width:100%;aspect-ratio:1200/630;object-fit:cover;border-radius:24px;display:block;box-shadow:0 40px 80px rgba(0,0,0,.45)}

  /* ================= Article : corps ================= */
  .zb-aPage{background:linear-gradient(180deg,#fbfaf6 0%,#ffffff 420px);color:#22312b}
  .zb-aBody{max-width:1180px;margin:0 auto;padding:56px 16px 0;display:grid;grid-template-columns:250px minmax(0,740px);gap:64px;justify-content:center}
  .zb-aBody:not(:has(.zb-toc)){grid-template-columns:minmax(0,760px)}
  @media (max-width:1060px){.zb-aBody{grid-template-columns:minmax(0,760px);gap:0}.zb-toc{display:none}}
  .zb-toc{position:relative}
  .zb-tocBox{position:sticky;top:104px;border-inline-start:2px solid var(--zb-line);padding-inline-start:18px}
  .zb-tocTitle{font-size:.72rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:var(--zb-gold);margin-bottom:12px}
  .zb-toc ol{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:2px}
  .zb-toc a{display:block;padding:7px 10px;margin-inline-start:-12px;border-radius:10px;font-size:.84rem;line-height:1.4;color:var(--zb-muted);text-decoration:none;transition:.2s}
  .zb-toc a:hover{color:var(--zb-emerald);background:rgba(11,59,46,.05)}
  .zb-toc a.is-active{color:var(--zb-emerald);font-weight:800;background:rgba(201,162,39,.12)}
  .zb-tocMobile{display:none;border:1px solid var(--zb-line);border-radius:16px;padding:14px 18px;margin-bottom:28px;background:#fff}
  @media (max-width:1060px){.zb-tocMobile{display:block}}
  .zb-tocMobile summary{font-weight:800;color:var(--zb-emerald);cursor:pointer}
  .zb-tocMobile ol{margin:12px 0 0;padding-inline-start:20px;display:flex;flex-direction:column;gap:6px;font-size:.9rem}
  .zb-tocMobile a{color:var(--zb-green);text-decoration:none}
  .zb-notice{background:#fff7e0;border:1px solid #f4df9a;color:#7a5b00;border-radius:14px;padding:12px 16px;font-size:.88rem;font-weight:600;margin-bottom:20px}

  .zb-prose{font-size:1.08rem;line-height:1.85;color:#22312b;counter-reset:zbh2}
  .zb-prose > * + *{margin-top:1.15em}
  .zb-prose .zb-lead{font-size:1.24rem;line-height:1.75;color:var(--zb-ink)}
  .zb-prose .zb-lead::first-letter{float:left;font:700 4.1em/.82 'Playfair Display',serif;color:var(--zb-gold);margin:.06em .12em 0 0}
  [dir=rtl] .zb-prose .zb-lead::first-letter,:lang(ar) .zb-prose .zb-lead::first-letter{float:none;font:inherit;color:inherit;margin:0}
  .zb-prose h2{position:relative;counter-increment:zbh2;font:700 1.75rem/1.28 'Playfair Display',serif;color:var(--zb-emerald);margin-top:2.2em;scroll-margin-top:100px;padding-top:.15em}
  .zb-prose h2::before{content:counter(zbh2,decimal-leading-zero);display:block;font:800 .78rem/1 'Montserrat',sans-serif;letter-spacing:.14em;color:var(--zb-gold);margin-bottom:.7em}
  .zb-prose h3{font:700 1.3rem/1.35 'Playfair Display',serif;color:var(--zb-emerald2);margin-top:1.7em;display:flex;gap:.55em;align-items:baseline}
  .zb-prose h3::before{content:"";flex:0 0 18px;height:3px;border-radius:3px;background:var(--zb-gold);transform:translateY(-.3em)}
  :lang(ar) .zb-prose h2,:lang(ar) .zb-prose h3{font-family:'Cairo',system-ui,sans-serif;line-height:1.6}
  :lang(ar) .zb-prose{font-size:1.12rem;line-height:2.05}
  .zb-prose strong{color:var(--zb-ink);font-weight:700}
  .zb-prose a{color:var(--zb-green);font-weight:700;text-decoration:underline;text-decoration-color:rgba(201,162,39,.55);text-decoration-thickness:2px;text-underline-offset:4px;transition:.2s}
  .zb-prose a:hover{color:var(--zb-emerald);text-decoration-color:var(--zb-gold)}
  .zb-prose ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:.55em}
  .zb-prose ul > li{position:relative;padding-inline-start:2em}
  .zb-prose ul > li::before{content:"";position:absolute;inset-inline-start:0;top:.42em;width:1.2em;height:1.2em;border-radius:50%;
    background:rgba(201,162,39,.16) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath d='M5 10.5l3 3 7-7' fill='none' stroke='%23a07f12' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") center/70% no-repeat}
  .zb-prose ol{list-style:none;padding:0;counter-reset:zbol;display:flex;flex-direction:column;gap:.7em}
  .zb-prose ol > li{position:relative;counter-increment:zbol;padding-inline-start:2.6em;min-height:1.8em}
  .zb-prose ol > li::before{content:counter(zbol);position:absolute;inset-inline-start:0;top:.12em;width:1.8em;height:1.8em;border-radius:50%;
    display:grid;place-items:center;background:var(--zb-emerald);color:var(--zb-gold2);font:800 .82rem/1 'Montserrat',sans-serif}
  .zb-prose .zb-callout{position:relative;margin:2em 0;padding:22px 24px 20px;border-radius:20px;font-style:normal;color:var(--zb-ink);
    background:linear-gradient(135deg,#fbf6e6,#f7f1de);border:1px solid rgba(201,162,39,.35);box-shadow:0 14px 34px rgba(160,127,18,.10)}
  .zb-prose .zb-callout::before{content:"💡  " attr(data-label);display:block;font:800 .74rem/1 'Montserrat',sans-serif;letter-spacing:.12em;text-transform:uppercase;color:#8a6a0c;margin-bottom:10px}
  :lang(ar) .zb-prose .zb-callout::before{letter-spacing:0;font-family:'Cairo',sans-serif;font-size:.85rem}
  .zb-prose .zb-callout p{margin:0}
  .zb-prose .zb-callout p + p{margin-top:.6em}
  .zb-tableWrap{overflow-x:auto;border-radius:18px;border:1px solid var(--zb-line);box-shadow:0 12px 30px rgba(6,46,32,.06);margin:1.8em 0}
  .zb-prose table{width:100%;border-collapse:collapse;font-size:.93rem;line-height:1.5;min-width:520px;background:#fff}
  .zb-prose th{background:var(--zb-emerald);color:#fff;font-weight:700;text-align:start;padding:13px 16px;font-size:.84rem;letter-spacing:.02em}
  .zb-prose th:first-child{color:var(--zb-gold2)}
  .zb-prose td{padding:12px 16px;border-top:1px solid var(--zb-line);vertical-align:top}
  .zb-prose tr:nth-child(even) td{background:#fbfaf6}
  .zb-prose td:first-child{font-weight:700;color:var(--zb-emerald)}
  .zb-prose hr{border:0;height:auto;text-align:center;margin:2.4em 0;color:var(--zb-gold);overflow:visible}
  .zb-prose hr::after{content:"✦  ✦  ✦";letter-spacing:.4em;font-size:.9rem}
  .zb-prose img{border-radius:18px;max-width:100%;height:auto}
  .zb-prose pre{background:#0b1f17;color:#e5e7eb;padding:14px;border-radius:12px;overflow-x:auto;font-size:.9rem}

  .zb-inlineCta{display:flex;align-items:center;gap:18px;margin:2.4em 0;padding:20px 22px;border-radius:22px;color:#fff;
    background:linear-gradient(120deg,var(--zb-emerald),var(--zb-emerald2) 60%,#145a43);box-shadow:0 22px 50px rgba(6,46,32,.25)}
  .zb-inlineIcon{flex:0 0 52px;height:52px;border-radius:16px;display:grid;place-items:center;font-size:1.6rem;background:rgba(255,255,255,.1);border:1px solid rgba(233,207,127,.35)}
  .zb-inlineText{flex:1;display:flex;flex-direction:column;gap:4px;font-size:.92rem;line-height:1.5;color:rgba(255,255,255,.82)}
  .zb-inlineText strong{color:#fff;font-size:1.05rem}
  .zb-prose .zb-inlineCta a{flex:0 0 auto;text-decoration:none;color:var(--zb-emerald);background:linear-gradient(135deg,#d4b056,#f4df9a);
    padding:11px 18px;border-radius:999px;font-weight:800;font-size:.86rem;white-space:nowrap;box-shadow:0 10px 24px rgba(0,0,0,.2)}
  @media (max-width:640px){.zb-inlineCta{flex-direction:column;text-align:center}}

  .zb-faq{margin-top:3.2em}
  .zb-faq h2{font:700 1.75rem 'Playfair Display',serif;color:var(--zb-emerald);margin:0 0 18px;scroll-margin-top:100px}
  :lang(ar) .zb-faq h2{font-family:'Cairo',sans-serif}
  .zb-faqItem{border:1px solid var(--zb-line);border-radius:18px;background:#fff;margin-bottom:12px;overflow:hidden;transition:box-shadow .2s}
  .zb-faqItem[open]{box-shadow:0 16px 36px rgba(6,46,32,.08);border-color:rgba(201,162,39,.4)}
  .zb-faqItem summary{list-style:none;cursor:pointer;padding:18px 22px;font-weight:800;color:var(--zb-emerald);display:flex;justify-content:space-between;gap:14px;align-items:center}
  .zb-faqItem summary::-webkit-details-marker{display:none}
  .zb-faqItem summary::after{content:"+";flex:0 0 30px;height:30px;border-radius:50%;display:grid;place-items:center;background:rgba(201,162,39,.15);color:#8a6a0c;font-size:1.2rem;transition:transform .25s}
  .zb-faqItem[open] summary::after{transform:rotate(45deg)}
  .zb-faqAnswer{padding:0 22px 20px;color:#334155;line-height:1.75}
  .zb-faqAnswer p{margin:0}
  .zb-faqAnswer p + p{margin-top:.6em}

  .zb-authorBox{display:flex;gap:18px;align-items:flex-start;margin-top:48px;padding:24px;border-radius:22px;background:var(--zb-cream);border:1px solid rgba(201,162,39,.25)}
  .zb-authorBox img{flex:0 0 64px;height:64px;border-radius:50%;background:#fff;padding:6px;object-fit:contain;box-shadow:0 0 0 2px rgba(201,162,39,.4)}
  .zb-authorName{font-weight:800;color:var(--zb-emerald);margin-bottom:4px}
  .zb-authorBox p{margin:0 0 8px;font-size:.9rem;line-height:1.6;color:#475569}
  .zb-authorBox a{font-size:.85rem;font-weight:800;color:var(--zb-green);text-decoration:none}

  .zb-share{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:32px 0 0}
  .zb-share strong{margin-inline-end:6px;color:var(--zb-emerald)}
  .zb-share a,.zb-share button{border-radius:999px;padding:8px 15px;font-size:.82rem;font-weight:700;border:1px solid rgba(11,59,46,.15);background:#fff;color:var(--zb-emerald2);cursor:pointer;text-decoration:none;transition:.2s}
  .zb-share a:hover,.zb-share button:hover{border-color:var(--zb-gold);transform:translateY(-1px)}
  .zb-share .is-wa{background:#25d366;border-color:#25d366;color:#fff}
  .zb-share .is-fb{background:#1877f2;border-color:#1877f2;color:#fff}
  .zb-cta{margin:40px 0 0;border-radius:26px;padding:36px 30px;color:#fff;text-align:center;position:relative;overflow:hidden;
    background:radial-gradient(600px 220px at 50% -40%,rgba(201,162,39,.35),transparent 70%),linear-gradient(135deg,var(--zb-emerald),var(--zb-emerald2));box-shadow:0 24px 60px rgba(6,46,32,.25)}
  .zb-cta h2{font:700 1.65rem 'Playfair Display',serif;margin:0 0 8px;color:#fff}
  .zb-cta p{margin:0 auto 20px;color:rgba(255,255,255,.82);max-width:520px}
  .zb-cta .zb-ctaBtns{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
  .zb-cta a{border-radius:999px;padding:12px 22px;font-weight:800;font-size:.9rem;text-decoration:none;transition:.2s}
  .zb-cta .zb-ctaPrimary{background:linear-gradient(135deg,#d4b056,#f4df9a);color:var(--zb-emerald)}
  .zb-cta .zb-ctaPrimary:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(0,0,0,.25)}
  .zb-cta .zb-ctaGhost{border:1px solid rgba(255,255,255,.4);color:#fff}
  .zb-related{margin-top:80px}
  .zb-related h2{font:700 1.7rem 'Playfair Display',serif;color:var(--zb-emerald);margin-bottom:22px}
</style>
