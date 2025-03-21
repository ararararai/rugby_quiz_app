<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;

// チーム選択ページ
Route::get('/', [QuizController::class, 'teamSelect'])->name('team.select');

// クイズ開始ページ
Route::get('/quiz/start/{team_id}', [QuizController::class, 'start'])->name('quiz.start');

// クイズプレイページ
Route::get('/quiz/play/{team_id}', [QuizController::class, 'play'])->name('quiz.play');

// 既存のルート
Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::get('/quiz/question', [QuizController::class, 'getQuestion'])->name('quiz.question');
Route::post('/quiz/check', [QuizController::class, 'checkAnswer'])->name('quiz.check');
// 結果表示ページ
Route::get('/quiz/result/{team_id}', [QuizController::class, 'result'])->name('quiz.result');

// サンゴリアスとスピアーズのクイズへのリンク
Route::get('/quiz/sungoliath', function () {
    $team = \App\Models\Team::where('name', 'サントリーサンゴリアス')->first();
    return redirect()->route('quiz.play', ['team_id' => $team->id]);
})->name('quiz.sungoliath');

Route::get('/quiz/spears', function () {
    $team = \App\Models\Team::where('name', 'クボタスピアーズ船橋・東京ベイ')->first();
    return redirect()->route('quiz.play', ['team_id' => $team->id]);
})->name('quiz.spears');
