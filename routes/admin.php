<?php

use App\Http\Controllers\Admin\SongController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
| Tinggal include file ini di routes/web.php:
|
|   require __DIR__.'/admin.php';
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Login/logout -- TIDAK pakai middleware 'auth'
        Route::middleware('guest')->group(function () {
            Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
            Route::post('login', [AuthController::class, 'login'])->name('login.submit');
        });
        Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

        // Semua CRUD admin wajib login
        Route::middleware('auth')->group(function () {
            Route::get('songs/import-preview', [SongController::class, 'importPreview'])->name('songs.import-preview');
            Route::resource('songs', SongController::class);
            Route::resource('genres', GenreController::class)->except(['show']);
        });
    });
