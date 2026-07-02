<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CourseAccessController;
use App\Http\Controllers\MyCoursesController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\RegimeController;
use App\Http\Controllers\QuizPlayerController;
use App\Http\Controllers\ExercisePlayerController;
use App\Http\Controllers\PackProgressController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SummerClubSubscriptionRequestController;
use App\Http\Controllers\Student\SummerClubController as StudentSummerClubController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/catalog', [CatalogController::class, 'catalog'])->name('catalog');
Route::get('/courses/{course:slug}', [CatalogController::class, 'show'])->name('courses.show');

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
*/
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactSend'])
    ->middleware('throttle:5,1')
    ->name('contact.send');

Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/refunds', [PageController::class, 'refunds'])->name('refunds');
Route::get('/club-ete', [PageController::class, 'summerClub'])->name('club.ete');
Route::get('/club-islamique', function () {
    return view('club-islamique');
})->name('club.islamique');
Route::post('/club-ete/subscription-request', [SummerClubSubscriptionRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('club-ete.subscription-request.store');

/*
|--------------------------------------------------------------------------
| Regimes
|--------------------------------------------------------------------------
*/
Route::get('/regimes', [RegimeController::class, 'index'])->name('regimes.index');

Route::prefix('regimes')->name('regimes.')->group(function () {
    Route::get('/tunisia', [RegimeController::class, 'showTunisia'])->name('tunisia.show');
    Route::post('/tunisia/start', [RegimeController::class, 'startTunisia'])->name('tunisia.start');

    Route::get('/qatar', [RegimeController::class, 'showQatar'])->name('qatar.show');
    Route::post('/qatar/start', [RegimeController::class, 'startQatar'])->name('qatar.start');

    Route::get('/saudi', [RegimeController::class, 'showSaudi'])->name('saudi.show');
    Route::post('/saudi/start', [RegimeController::class, 'startSaudi'])->name('saudi.start');

    Route::get('/quran', [RegimeController::class, 'showQuran'])->name('quran.show');
    Route::post('/quran/start', [RegimeController::class, 'startQuran'])->name('quran.start');
});

/*
|--------------------------------------------------------------------------
| Placeholders
|--------------------------------------------------------------------------
*/
Route::view('/live/programs', 'live.programs')->name('live.programs');
Route::view('/library', 'library.index')->name('library');
Route::view('/live/sessions', 'live.sessions')->name('live.sessions');

/*
|--------------------------------------------------------------------------
| Language switch
|--------------------------------------------------------------------------
*/
Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['fr', 'en', 'ar'], true)) {
        abort(400);
    }

    session(['locale' => $locale]);

    $previous = url()->previous();

    $safe = str_starts_with($previous, config('app.url'))
        ? $previous
        : route('home');

    return redirect()->to($safe);
})->middleware('throttle:30,1')->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Auth-only
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/my-courses', [MyCoursesController::class, 'index'])->name('my.courses');

    Route::post('/courses/{course:slug}/buy', [CourseAccessController::class, 'buy'])->name('courses.buy');

    Route::get('/my-courses/{course:slug}/access', [CourseAccessController::class, 'access'])->name('courses.access');
    Route::get('/my-courses/{course:slug}/pdf', [CourseAccessController::class, 'pdf'])->name('courses.pdf');

    Route::get('/student/club-ete/catalogue', [StudentSummerClubController::class, 'catalogue'])
        ->name('student.club-ete.catalogue');

    Route::get('/student/club-ete/formations', [StudentSummerClubController::class, 'formations'])
        ->name('student.club-ete.formations.index');

    Route::get('/student/club-ete/formations/{resource}', [StudentSummerClubController::class, 'showFormation'])
        ->name('student.club-ete.formations.show');

    Route::get('/student/club-ete/quiz/{quiz}', [StudentSummerClubController::class, 'quiz'])
        ->name('student.club-ete.quiz.show');

    Route::post('/student/club-ete/quiz/{quiz}/submit', [StudentSummerClubController::class, 'submitQuiz'])
        ->middleware('throttle:20,1')
        ->name('student.club-ete.quiz.submit');

    Route::get('/student/club-ete/exercise/{exercise}', [StudentSummerClubController::class, 'showExercise'])
        ->name('student.club-ete.exercise.show');

    Route::post('/student/club-ete/exercise/{exercise}/submit', [StudentSummerClubController::class, 'submitExercise'])
        ->middleware('throttle:20,1')
        ->name('student.club-ete.exercise.submit');

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');

    Route::post('/cart/add/{course}', [CartController::class, 'add'])
        ->middleware('throttle:30,1')
        ->name('cart.add');

    Route::post('/cart/remove/{course}', [CartController::class, 'remove'])
        ->name('cart.remove');

    Route::post('/cart/add-exercise/{exercise}', [CartController::class, 'addExercise'])
        ->middleware('throttle:30,1')
        ->name('cart.addExercise');

    Route::post('/cart/remove-exercise/{exercise}', [CartController::class, 'removeExercise'])
        ->name('cart.removeExercise');

    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */
    Route::post('/checkout', [CheckoutController::class, 'checkout'])
        ->middleware('throttle:10,1')
        ->name('checkout');

    Route::post('/checkout/manual', [CheckoutController::class, 'requestManual'])
        ->middleware('throttle:10,1')
        ->name('checkout.manual');

    /*
    |--------------------------------------------------------------------------
    | Regimes checkout
    |--------------------------------------------------------------------------
    */
    Route::prefix('regimes')->name('regimes.')->group(function () {
        Route::get('/tunisia/checkout', [RegimeController::class, 'checkoutTunisia'])->name('tunisia.checkout');
        Route::post('/tunisia/checkout', [RegimeController::class, 'submitTunisia'])
            ->middleware('throttle:10,1')
            ->name('tunisia.submit');

        Route::get('/qatar/checkout', [RegimeController::class, 'checkoutQatar'])->name('qatar.checkout');
        Route::post('/qatar/checkout', [RegimeController::class, 'submitQatar'])
            ->middleware('throttle:10,1')
            ->name('qatar.submit');

        Route::get('/saudi/checkout', [RegimeController::class, 'checkoutSaudi'])->name('saudi.checkout');
        Route::post('/saudi/checkout', [RegimeController::class, 'submitSaudi'])
            ->middleware('throttle:10,1')
            ->name('saudi.submit');

        Route::get('/quran/checkout', [RegimeController::class, 'checkoutQuran'])->name('quran.checkout');
        Route::post('/quran/checkout', [RegimeController::class, 'submitQuran'])
            ->middleware('throttle:10,1')
            ->name('quran.submit');
    });

    /*
    |--------------------------------------------------------------------------
    | Quiz
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz/{quiz}', [QuizPlayerController::class, 'show'])->name('quiz.show');

    Route::post('/quiz/{quiz}/submit', [QuizPlayerController::class, 'submit'])
        ->middleware('throttle:20,1')
        ->name('quiz.submit');

    Route::get('/quiz-attempt/{attempt}', [QuizPlayerController::class, 'result'])->name('quiz.result');

    /*
    |--------------------------------------------------------------------------
    | Exercises
    |--------------------------------------------------------------------------
    */
    Route::get('/exercise/{exercise}', [ExercisePlayerController::class, 'show'])
        ->name('exercise.show');

    Route::post('/exercise/{exercise}/submit', [ExercisePlayerController::class, 'submit'])
        ->middleware('throttle:20,1')
        ->name('exercise.submit');

    Route::get('/exercise-attempt/{attempt}', [ExercisePlayerController::class, 'result'])
        ->name('exercise.result');

    /*
    |--------------------------------------------------------------------------
    | Pack progress
    |--------------------------------------------------------------------------
    */
    Route::post('/my-courses/{course}/items/{item}/start', [PackProgressController::class, 'start'])
        ->name('pack.items.start');

    Route::post('/my-courses/{course}/items/{item}/complete', [PackProgressController::class, 'complete'])
        ->name('pack.items.complete');
});

require __DIR__ . '/auth.php';
