<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Landing Page
Route::get('/', [PublicController::class, 'index'])->name('home');

// 2. News Routes
Route::prefix('berita')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('show');
});

// 3. Certificate Search & Download Routes
Route::prefix('sertifikat')->name('certificates.')->group(function () {
    Route::get('/', [CertificateController::class, 'search'])
        ->middleware('throttle:40,1')
        ->name('search');

    Route::get('/unduh/{certificate}', [CertificateController::class, 'download'])
        ->middleware('throttle:30,1')
        ->name('download');
});
