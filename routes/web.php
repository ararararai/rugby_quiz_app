<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;

Route::get('/', function () {
    return view('welcome');
});

// クイズ関連のルート
Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::get('/quiz/question', [QuizController::class, 'getQuestion'])->name('quiz.question');
Route::post('/quiz/check', [QuizController::class, 'checkAnswer'])->name('quiz.check');
// チーム選択ページ
Route::get('/', [App\Http\Controllers\QuizController::class, 'teamSelect'])->name('team.select');
