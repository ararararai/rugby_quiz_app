<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $teamId = $request->query('team_id');
        return view('quiz.index', compact('teamId'));
    }

    public function getQuestion(Request $request)
    {
        try {
            $teamId = $request->query('team_id');

            if (!$teamId) {
                return response()->json(['error' => 'チームIDが指定されていません'], 400);
            }

            // 指定されたチームの選手からランダムに1人を選択（正解）
            $correctPlayer = Player::where('team_id', $teamId)
                                  ->inRandomOrder()
                                  ->first();

            if (!$correctPlayer) {
                return response()->json(['error' => 'このチームの選手データがありません'], 404);
            }

            $teamName = $correctPlayer->team->name;

            // 同じチームの他の選手から3人をランダムに選択（選択肢）
            $teamPlayers = Player::where('team_id', $correctPlayer->team_id)
                                ->where('id', '!=', $correctPlayer->id)
                                ->inRandomOrder()
                                ->take(3)
                                ->get();

            // 選択肢が3人に満たない場合、ダミーデータで補完
            while ($teamPlayers->count() < 3) {
                $dummyPlayer = new Player();
                $dummyPlayer->id = -1 * ($teamPlayers->count() + 1); // ダミーIDはマイナス値
                $dummyPlayer->name = '選手' . ($teamPlayers->count() + 1);
                $teamPlayers->push($dummyPlayer);
            }

            // 全ての選択肢をまとめる
            $choices = $teamPlayers->push($correctPlayer)->shuffle();

            return response()->json([
                'question' => [
                    'player_image' => asset($correctPlayer->image_path),
                    'team_name' => $teamName,
                    'correct_id' => $correctPlayer->id
                ],
                'choices' => $choices->map(function($player) {
                    return [
                        'id' => $player->id,
                        'name' => $player->name
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Quiz error: ' . $e->getMessage());
            return response()->json(['error' => 'エラーが発生しました: ' . $e->getMessage()], 500);
        }
    }


    public function checkAnswer(Request $request)
    {
        try {
            $request->validate([
                'player_id' => 'required|exists:players,id',
                'answer_id' => 'required|exists:players,id',
            ]);

            $correctPlayer = Player::with('team')->findOrFail($request->player_id);
            $answered = Player::findOrFail($request->answer_id);

            $isCorrect = $correctPlayer->id === $answered->id;

            $teamName = $correctPlayer->team ? $correctPlayer->team->name : 'チーム情報なし';

            return response()->json([
                'correct' => $isCorrect,
                'correct_player' => [
                    'id' => $correctPlayer->id,
                    'name' => $correctPlayer->name,
                    'team' => $teamName
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Answer check error: ' . $e->getMessage());
            return response()->json(['error' => 'エラーが発生しました: ' . $e->getMessage()], 500);
        }
    }
    public function teamSelect()
    {
        $teams = Team::all();
        return view('quiz.team-select', compact('teams'));
    }

}
