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

// テスト用クイズルート
Route::get('/test-quiz', function () {
    $team = App\Models\Team::where('name', 'テストチーム')->first();
    if ($team) {
        return redirect()->route('quiz.play', ['team_id' => $team->id]);
    }
    return redirect()->route('team.select');
})->name('test.quiz');
