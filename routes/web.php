<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialMediaPostController;
use App\Http\Controllers\SocialMediaAccountController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\FacebookController;

/* ===== Blokir register (pilih opsi A) ===== */
Route::match(['get','post','put','patch','delete'], '/register', function () {
    abort(404);
})->name('register.blocked');
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/* ===== Halaman depan ===== */
Route::get('/', fn () => view('welcome'))->name('home');

/* ===== Dashboard (hanya 1x, pakai controller) ===== */
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', [SocialMediaPostController::class, 'index'])->name('dashboard');

    Route::post('/post', [SocialMediaPostController::class, 'post'])->name('post');
    Route::get('/export', [SocialMediaPostController::class, 'export'])->name('export');

    Route::resource('/accounts', SocialMediaAccountController::class)->except(['show']);

    Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::get('/broadcast/create', [BroadcastController::class, 'create'])->name('broadcast.create');
    Route::post('/broadcast/send', [BroadcastController::class, 'send'])->name('broadcast.send');
    Route::get('/broadcast/{broadcast}/retry', [BroadcastController::class, 'retry'])->name('broadcast.retry');
    Route::post('/broadcast/{broadcast}/retry', [BroadcastController::class, 'retrySend'])->name('broadcast.retry.send');

    // Social login callback
    Route::get('/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* ===== Test Twitter (biarkan publik/terproteksi sesuai kebutuhan) ===== */
Route::get('/test-twitter', function () {
    $account = \App\Models\SocialMediaAccount::where('platform', 'twitter')->first();
    if (!$account) return '❌ Tidak ada akun Twitter ditemukan';

    $mediaPath = Storage::disk('public')->path('broadcasts/sample.png');
    if (!file_exists($mediaPath)) return '❌ File sample.png tidak ditemukan di /storage/app/public/broadcasts';

    $twitter = new TwitterOAuth(
        config('services.twitter.consumer_key'),
        config('services.twitter.consumer_secret'),
        $account->access_token,
        $account->access_token_secret
    );

    $check = $twitter->get('account/verify_credentials');
    if ($twitter->getLastHttpCode() !== 200) return response()->json(['❌ Token Invalid', $check]);

    $media = $twitter->upload('media/upload', ['media' => $mediaPath]);
    if ($twitter->getLastHttpCode() !== 200) return response()->json(['❌ Gagal upload media', $media]);

    $tweet = $twitter->post('statuses/update', [
        'status' => '🔴 Tes tweet dari Laravel',
        'media_ids' => $media->media_id_string ?? '',
    ]);

    return response()->json([
        '✅ Tweet berhasil?' => $twitter->getLastHttpCode(),
        'Tweet' => $tweet,
    ]);
});

/* ===== Auth routes bawaan (login, logout, dll.) ===== */
require __DIR__.'/auth.php';

/* ===== Fallback 404 ===== */
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
