<?php

use App\Http\Controllers\Admin\SongController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
| Tinggal include file ini di routes/web.php:
|
|   require __DIR__.'/admin.php';
|
| Middleware 'auth' asumsinya kamu sudah punya sistem login admin
| (Breeze/Jetstream/custom). Kalau belum, ganti/hapus middleware ini dulu.
*/

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('songs/import-preview', [SongController::class, 'importPreview'])->name('songs.import-preview');
        Route::resource('songs', SongController::class);
    });
