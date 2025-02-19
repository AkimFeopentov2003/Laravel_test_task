<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TextCheckController;

Route::get('/', [TextCheckController::class, 'index'])->name('text.check');
Route::post('/check', [TextCheckController::class, 'checkText'])->name('text.process');

