<?php

use App\Http\Controllers\Public\SongPublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
| Include di routes/web.php:
|   require __DIR__.'/public_songs.php';
*/

Route::get('/', [SongPublicController::class, 'home'])->name('home');
Route::get('/cari', [SongPublicController::class, 'search'])->name('songs.search');
Route::get('/huruf/{letter}', [SongPublicController::class, 'byLetter'])->name('songs.by-letter');
Route::get('/chord/{song:slug}', [SongPublicController::class, 'show'])->name('songs.show');
