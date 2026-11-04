# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Zaytouna Academy** — a French-first e-learning platform (courses, packs, quizzes, interactive
exercises, live tutoring "regimes", and a seasonal "Club d'été"). Laravel 12 / PHP 8.2, Filament 5.3
admin panel, SQLite, Blade + Tailwind 3 + Alpine. Auth scaffolding is Laravel Breeze (Blade stack).

## Commands

```bash
# Full local dev (server + queue worker + `pail` log tail + Vite, concurrently)
composer dev

# Frontend only
npm run dev            # Vite dev server
npm run build          # production assets

# Tests (Pest). `composer test` clears config first.
composer test
php artisan test
php artisan test --filter=RegistrationTest          # single test class / name
php artisan test tests/Feature/Auth/ProfileTest.php # single file

# Lint / format (no pint.json — uses Pint defaults)
vendor/bin/pint            # apply
vendor/bin/pint --test     # check only

# DB (SQLite file at database/database.sqlite; tests use :memory:)
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# First-time bootstrap
composer setup
```

Seeding creates roles/taxonomy; only in `local`/`testing` (and when no admin exists) it adds a dev admin
`admin@zaytouna.local` / `Admin@12345`. Real admin accounts: `php artisan zaytouna:admin <email> [--replace=old@mail]
[--demote-others] [--reset-2fa]` (password prompted, never stored in code). Filament admin panel lives at `/admin`
(`admin` role only, `->profile()` page). Two-factor authentication is **temporarily disabled**: the
`->multiFactorAuthentication()` call is commented out in `AdminPanelProvider`; the `User` traits and secret columns are kept for re-enabling.

## Architecture

### Roles & access
- `spatie/laravel-permission`. Roles: `admin`, `student`. New registrants are auto-assigned
  `student` and admins are notified — see `AppServiceProvider::boot()` (listens on `Registered`).
- `User::canAccessPanel()` gates Filament to `admin`.
- Course access is checked via `Enrollment` (`status = active`, `access_ends_at` null or future) —
  `User::hasActiveEnrollmentForCourse()` / `Enrollment::isActive()`.

### Commerce flow (manual approval, not live Stripe)
`stripe/stripe-php` is installed but checkout does **not** charge. `CheckoutController::checkout()`
delegates to `requestManual()`: it creates an `Order` with `status = pending_manual`, `OrderItem`
rows, a `Payment` (`provider = manual`, `status = pending`), clears the session cart, and notifies
admins (`ManualOrderPending`). An admin then approves the order in Filament, which is what grants the
`Enrollment`. Money is always stored as integer `*_cents` fields with a `currency` string.

### Catalog / content model
- Taxonomy: `Track` → `Level`; `Subject` belongs to a `Branch`; `Course` belongs to a `Subject`.
- `Course.delivery_type`: `course` / `series` / `exam` are "simple" courses (`isSimpleCourse()`);
  `pack` is a bundle (`isPack()`).
- A pack owns ordered `CoursePackItem` rows (FK `pack_id`). `item_type` ∈
  `course, series, quiz, exam_prep, resource, exercise`. A `course`/`series` item points at another
  `Course` via `linked_course_id`; `quiz` / `exercise` items own a `Quiz` / `Exercise`
  (`course_pack_item_id`).

### Pack progression & sequential unlock
- `CoursePackItemProgress` (per user + pack + item) tracks `is_started` / `is_completed` /
  `progress_percent`.
- `PackProgressService` — `startItem()`, `completeItem()`, `touchItem()`, `getPackProgressSummary()`.
- `PackUnlockService::isUnlocked()` — a `is_required` item is locked until the previous required
  item (by `sort_order`) is completed. Non-required items are always open.
- Routes: `POST /my-courses/{course}/items/{item}/start|complete` (`PackProgressController`).

### Quizzes vs. Exercises (two parallel engines)
- **Quiz** = graded assessment. `Quiz` → `QuizQuestion` → `QuizOption`; attempts in
  `QuizAttempt` / `QuizAttemptAnswer`. Scoring lives in `QuizScoringService::submit()` (question
  types: `single_choice`, `true_false`, `multiple_choice` all-or-nothing, `short_answer` ungraded,
  `fill_blank` case-insensitive text, `matching` partial credit, `ordering`). Player:
  `QuizPlayerController`, routes `/quiz/{quiz}`, `/quiz/{quiz}/submit`, `/quiz-attempt/{attempt}`.
- **Exercise** = practice with immediate correction + retry. `Exercise` → `ExerciseItem` →
  `ExerciseItemAnswer`; attempts in `ExerciseAttempt` / `ExerciseAttemptAnswer`. 10 item types
  (`single_choice, multiple_choice, true_false, matching, ordering, fill_blank, word_bank,
  categorization, short_answer, calculation`), plus `exercise_type = 'pdf'` for a static PDF.
  Scoring is inline in `ExercisePlayerController::evaluate()` (not a service). Exercises may be sold
  standalone (`is_paid` / `price_cents`) and added to the cart separately (`cart/add-exercise`).
- An exercise/quiz attaches to **either** a `course_id` **or** a `course_pack_item_id`.

### Regimes (live tutoring)
`RegimeController` handles four programs: `tunisia`, `qatar`, `saudi`, `quran`. Each has
`show` / `start` / `checkout` / `submit` routes under `/regimes/*`. Pricing (hourly rates, pack
prices, native currency per regime) is hard-coded in `App\Services\CurrencyService` constants (CAD added for Canada: derived from TND via PPP like USD/GBP).

### Club d'été (Summer Club) — a self-contained parallel subsystem
Prefix `SummerClub*` on ~10 models, Filament resources, and student routes under
`/student/club-ete/*` (`Student\SummerClubController`). Its own resources (`SummerClubResource`),
quizzes (`SummerClubQuiz` + questions + `SummerClubQuizAttempt`), exercises (`SummerClubExercise` +
items + `SummerClubExerciseAttempt`), `SummerClubEnrollment`, and a public lead form
`SummerClubSubscriptionRequest` (`POST /club-ete/subscription-request`). Treat it as separate from
the main course engine — do not assume shared tables. "Club islamique" (`/club-islamique`) is just a
static Blade view.

### Blog, SEO & site analytics (Filament group "Marketing & Blog")
- **Blog**: `BlogPost` / `BlogCategory` (FR required, `_ar` / `_en` optional; `localized()`,
  `availableLocales()`). Content is Filament `RichEditor` HTML rendered via `contentHtml()`.
  Routes `/blog`, `/blog/categorie/{slug}`, `/blog/{slug}` (`BlogController`). Drafts and future-dated posts
  404 except for admins (preview). `App\Services\Blog\ArticleRenderer` post-processes the editor HTML: H2 anchors +
  table of contents, a "Questions fréquentes" H2 (then H3/answer pairs) becomes an accordion + FAQPage JSON-LD,
  blockquote = "À retenir" callout, mid-article CTA, and strips the `nofollow`/`target` RichEditor adds to internal
  links. Write articles with only p/h2/h3/ul/ol/blockquote/table/a — the RichEditor drops `div`s and attributes.
- **Priority markets** (« pays de vision »): TN, FR, SA, QA, CA in `config/marketing.php` (+ `App\Support\Markets`,
  flags as SVG in `public/images/flags`). Used for blog `target_countries`, regional hreflang
  (`App\Support\Hreflang`: fr-TN, fr-CA, ar-SA…), the blog's « pour votre pays » block and analytics.
- **Marketing goals**: `MarketingGoal` (month AAAA-MM, metric, optional country, target) managed at
  `/admin/objectifs`; `GoalTracker` computes actuals, month-end projection, a /100 strategy score and
  rule-based recommendations shown on `/admin/statistiques`. Dates use `marketing.timezone` (Africa/Tunis).
- **Platform stats**: `PlatformStats` calls the teaching platform's `GET {PLATFORM_URL}/api/marketing/stats?month=`
  (Bearer `PLATFORM_STATS_TOKEN` = platform's `MARKETING_API_TOKEN`), aggregated only. Feeds goal metrics
  `active_students` (per country, from timezone, no projection), `retention`, `attendance` and the « Plateforme » block
  of `/admin/statistiques`; API down → goals « Indisponible » (not scored).
  Reverse direction: `GET /api/site/stats?period=&month=` (`routes/api.php`, `Api\SiteStatsController`, same Bearer token)
  feeds the platform's admin page « Site principal » — field whitelist only, never names/e-mails/visitor hashes.
- **Site ↔ platform bridge** (`App\Services\PlatformBridge`, same token, platform routes `/api/site/*`):
  every `AdminNotifier::send()` alert is relayed (deferred, no personal data) to the platform admins' bell;
  LiveSubscriptions action « Créer le compte sur la plateforme » creates/links the student (`site_order_id` on the
  platform `users`) and pre-fills login + temp password (encrypted in cache 2 h) in « Accepter & envoyer les accès »;
  `testimonials:import-platform` (weekly + button in Témoignages) imports public 4–5★ student reviews as UNPUBLISHED
  testimonials (`source = plateforme`, `external_ref`).
- **Funnel** (`Analytics\Funnel`): visitors → regime orders → accepted → platform accounts → active → retained, per
  market (order country = country of the parent's page views); shown on `/admin/statistiques` and in `/api/site/stats`.
- **Monthly report**: `marketing:monthly-report [--month=] [--to=]` (`MonthlyReport` + `MonthlyMarketingReport` mail),
  scheduled the 1st at 08:00 Tunis; button « Recevoir le bilan par e-mail » on `/admin/statistiques`. Scheduler needs the
  Hostinger cron `php artisan schedule:run` every minute.
- **Security**: `SecurityHeaders` (safe CSP: frame-ancestors/base-uri/object-src/form-action, no X-Powered-By). Paid
  exercise PDF/audio/video live on the `private` disk, served by `exercise.media` (access-checked, admins allowed).
- **Conversion tools**: floating WhatsApp button (`partials/whatsapp-button`, number `WHATSAPP_NUMBER` →
  `config('zaytouna.whatsapp_number')`, hide with `$hideWhatsapp = true`); clicks to `wa.me/<number>` feed the
  `whatsapp` goal metric. Parent reviews: `Testimonial` (`/admin/temoignages`, public form `/avis` → unpublished +
  admin notification), shown by `partials/testimonials` on regime pages + home only once published, with
  Course/AggregateRating JSON-LD from 3 reviews. Never seed or invent reviews.
- **Seeded articles**: `BlogArticlesSeeder` (11 scheduled posts, content in `database/seeders/content/blog/<slug>/
  {fr,ar,en}.html`, covers in `database/seeders/assets/blog/`). Idempotent: never overwrites an existing slug.
  In production run `php artisan db:seed --class=MarketingSeeder --force` (categories + articles + launch goals).
- **SEO head**: every page using `layouts.app` includes `partials/seo` (description, canonical, hreflang,
  OG/Twitter, JSON-LD). Views override via `@section('title'|'meta_description'|'meta_image'|'hreflang'|
  'canonical'|'robots')` and `@push('head')`. Section values must never be `null` (Blade would open an
  unclosed buffer). Titles/descriptions live in `lang/*/seo.php`.
- **Language in URL**: `SetLocale` honours `?lang=fr|en|ar` (stored in session) — this is what hreflang
  alternates point to. `/sitemap.xml` and `/robots.txt` are dynamic (`SeoController`, `SitemapBuilder`);
  robots disallows everything outside production. Don't recreate `public/robots.txt` (it would shadow the route).
- **Analytics (no cookies, no IP stored)**: `TrackPageView` middleware (web group) writes `page_views` for
  human GET HTML 200 responses, skipping bots, admins and `/admin`. `partials/click-tracker` beacons clicks
  to `POST /t/c` → `click_events` (name a button with `data-track="…"`, opt out with `data-no-track`).
  `visitor_hash` = sha256(app key + month + IP + UA). Reports: `AnalyticsReport` →
  `Filament\Pages\SiteAnalytics` (`/admin/statistiques`). Rows pruned after 25 months (`model:prune`, daily).

### i18n & currency (middleware, appended to the `web` group in `bootstrap/app.php`)
- `SetLocale` — resolves locale from `session('locale')`, else `Accept-Language`, else
  `config('app.locale')` = `fr`. Switch via `GET /lang/{fr|en|ar}`. UI strings in
  `lang/{fr,en,ar}/ui.php`.
- The Filament panel forces French via `ForceFrenchLocale` middleware.
- `SecurityHeaders` adds response headers.
- `DetectCurrency` — sets `session('app_country'/'app_currency')` from Cloudflare `CF-IPCountry`
  (defaults to `TN` / `TND`).
- **Model i18n pattern:** translatable fields are stored as `field`, `field_ar`, `field_en`
  columns. Read them through the `getFieldDisplayAttribute()` accessor / `field_i18n` alias
  (`Course`, `CoursePackItem`, `Exercise`, …), never the raw column.

### Filament resource layout (v5 split style)
Each resource is a directory under `app/Filament/Resources/<Name>/`:
`XResource.php` + `Pages/` (List/Create/Edit) + `Schemas/XForm.php` + `Tables/XTable.php` +
`RelationManagers/`. Resources, pages, and widgets are auto-discovered by `AdminPanelProvider`.
Some resources (e.g. `ExerciseItemResource`, `QuizQuestionResource`) hide their nav and are reached
only through a parent's RelationManager. Dashboard widgets: `AdminKpiOverview`,
`SalesLast14DaysChart`, `LearnerStatsWidget`, `RecentActivitiesWidget`; custom page
`LearnerReports`.

### Misc conventions
- Notifications are `database`-channel and target admins for lifecycle events (new user, manual /
  regime order pending, order approved/paid/refunded). `notifications` table migration exists.
- Public write endpoints are rate-limited with `throttle:` middleware (contact, cart, checkout,
  quiz/exercise submit, lang switch).
- Queue, cache, and sessions all use the `database` driver; `composer dev` runs a `queue:listen`.
- The repo has no CI config and only the default Breeze auth/profile tests — feature tests for the
  domain engines above do not exist yet.
