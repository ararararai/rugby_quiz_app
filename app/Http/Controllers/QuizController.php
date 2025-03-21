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

            // 出題済みの選手IDを取得
            $askedIds = [];
            if ($request->has('asked_ids')) {
                $askedIds = explode(',', $request->query('asked_ids'));
            }

            // 未出題の選手からランダムに1人を選択（正解）
            $query = Player::where('team_id', $teamId);
            if (!empty($askedIds)) {
                $query->whereNotIn('id', $askedIds);
            }
            $correctPlayer = $query->inRandomOrder()->first();

            // 未出題の選手がいない場合、全出題完了とみなす
            if (!$correctPlayer) {
                return response()->json(['error' => '全ての選手が出題されました', 'completed' => true], 404);
            }

            // 正解の選手を出題済みリストに追加
            $askedIds[] = $correctPlayer->id;

            $teamName = $correctPlayer->team->name;

            // 同じチームの他の選手から3人をランダムに選択（選択肢）
            $teamPlayersQuery = Player::where('team_id', $correctPlayer->team_id)
                ->where('id', '!=', $correctPlayer->id);

            $teamPlayers = $teamPlayersQuery->inRandomOrder()
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
                'choices' => $choices->map(function ($player) {
                    return [
                        'id' => $player->id,
                        'name' => $player->name
                    ];
                }),
                'asked_ids' => implode(',', $askedIds) // 出題済みリストを返す
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

    public function start($teamId)
    {
        $team = Team::findOrFail($teamId);
        $playerCount = Player::where('team_id', $teamId)->count();

        return view('quiz.start', compact('team', 'playerCount'));
    }

    public function play($teamId)
    {
        $team = Team::findOrFail($teamId);
        $playerCount = Player::where('team_id', $teamId)->count();

        return view('quiz.index', compact('teamId', 'playerCount'));
    }
    public function result(Request $request, $teamId)
    {
        $team = Team::findOrFail($teamId);
        $totalQuestions = $request->query('total', 0);
        $correctCount = $request->query('correct', 0);
        $percentage = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        // 間違えた選手の情報を取得
        $wrongAnswers = [];
        if ($request->has('wrong')) {
            $wrongAnswers = json_decode($request->query('wrong'), true) ?: [];
        }

        return view('quiz.result', compact('team', 'totalQuestions', 'correctCount', 'percentage', 'wrongAnswers'));
    }
}
