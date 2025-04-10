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

            // 間違えた選手のIDを取得
            $wrongPlayerIds = [];
            $isWrongOnly = false;

            // クエリパラメータから間違えた選手モードを検出
            $wrongOnlyParam = $request->query('wrong_only');
            if ($wrongOnlyParam === 'true' || $wrongOnlyParam === '1') {
                $isWrongOnly = true;
                Log::info('Wrong only mode detected from URL parameter: ' . $wrongOnlyParam);
                
                // 間違えた選手のIDを取得
                $wrongAnswers = $request->query('wrong_answers');
                Log::info('Wrong answers received: ' . ($wrongAnswers ?: 'none'));
                
                if ($wrongAnswers) {
                    try {
                        // このパラメータは直接IDの配列を含む
                        $wrongPlayerIds = json_decode($wrongAnswers, true) ?: [];
                        
                        // 配列であることを確認し、整数値に変換
                        if (is_array($wrongPlayerIds)) {
                            $wrongPlayerIds = array_map('intval', $wrongPlayerIds);
                            // 重複を削除
                            $wrongPlayerIds = array_values(array_unique($wrongPlayerIds));
                        } else {
                            $wrongPlayerIds = [];
                        }
                        
                        Log::info('Decoded wrong player IDs: ' . json_encode($wrongPlayerIds));
                        
                        // idが実際に存在するか確認
                        $existingPlayerIds = Player::whereIn('id', $wrongPlayerIds)->pluck('id')->toArray();
                        $wrongPlayerIds = $existingPlayerIds; // 実際に存在するIDだけを使用
                        Log::info('Validated player IDs: ' . json_encode($wrongPlayerIds));
                        
                        // 間違えた選手のIDが空の場合はエラーを返す
                        if (empty($wrongPlayerIds)) {
                            Log::warning('Wrong only mode active but no valid player IDs found');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error decoding wrong answers: ' . $e->getMessage());
                        $wrongPlayerIds = [];
                    }
                }
            }

            Log::info('Is wrong only mode: ' . ($isWrongOnly ? 'true' : 'false'));
            Log::info('Wrong player IDs before query: ' . json_encode($wrongPlayerIds));

            // クエリを初期化
            if ($isWrongOnly && !empty($wrongPlayerIds)) {
                // 間違えた選手のみモード: 指定された選手IDから選択
                Log::info('Using wrong only mode with IDs: ' . json_encode($wrongPlayerIds));
                $query = Player::where('team_id', $teamId)->whereIn('id', $wrongPlayerIds);
                
                // 出題済みの選手を除外
                if (!empty($askedIds)) {
                    $query->whereNotIn('id', $askedIds);
                }
                
                // 出題可能な選手がいない場合、出題済みリストをリセット
                if ($query->count() === 0) {
                    $askedIds = [];
                    $query = Player::where('team_id', $teamId)->whereIn('id', $wrongPlayerIds);
                    Log::info('Reset asked IDs as no more players available');
                }
            } else {
                // 通常モード: まだ出題されていない選手から選択
                $query = Player::where('team_id', $teamId);
                if (!empty($askedIds)) {
                    $query->whereNotIn('id', $askedIds);
                }
            }
            
            // プレイヤーを取得する前に、最終的なクエリを記録
            Log::info('Final query SQL: ' . $query->toSql());
            Log::info('Final query bindings: ' . json_encode($query->getBindings()));
            
            $correctPlayer = $query->inRandomOrder()->first();
            
            if ($correctPlayer) {
                Log::info('Selected player: ID=' . $correctPlayer->id . ', Name=' . $correctPlayer->name);
            } else {
                Log::info('No player found with the query');
            }

            // 未出題の選手がいない場合、全出題完了とみなす
            if (!$correctPlayer) {
                return response()->json(['error' => '全ての選手が出題されました', 'completed' => true], 404);
            }

            // 正解の選手を出題済みリストに追加
            $askedIds[] = $correctPlayer->id;

            $teamName = $correctPlayer->team->name;

            // 選択肢の生成（同じチームの他の選手から選択）
            // 問題の選手と同じ国籍（日本人か外国人か）の選手のみを選択
            $isJapanese = $correctPlayer->is_japanese;
            
            $teamPlayers = Player::where('team_id', $correctPlayer->team_id)
                ->where('id', '!=', $correctPlayer->id)
                ->where('is_japanese', $isJapanese) // 同じ国籍の選手のみを選択
                ->inRandomOrder()
                ->take(3)
                ->get();
                
            // 同じ国籍の選手が3人に満たない場合、ダミーデータで補完
            while ($teamPlayers->count() < 3) {
                $dummyPlayer = new Player();
                $dummyPlayer->id = -1 * ($teamPlayers->count() + 1);
                $dummyPlayer->name = ($isJapanese ? '日本人選手' : '外国人選手') . ($teamPlayers->count() + 1);
                $teamPlayers->push($dummyPlayer);
            }

            // 正解の選手を選択肢に追加してシャッフル
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

            $teamName = $correctPlayer->team_name ?? 'チーム情報なし';

            return response()->json([
                'correct' => $isCorrect,
                'correct_player' => [
                    'id' => $correctPlayer->id,
                    'name' => $correctPlayer->name,
                    'team' => $teamName,
                    'is_japanese' => $correctPlayer->is_japanese
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
        $totalPlayerCount = Player::where('team_id', $teamId)->count();
        
        // 問題数の設定を取得
        $questionCount = request()->get('question_count', 'all');
        
        // 間違えた選手のみモードの場合、playerCountを間違えた選手の数に設定
        $wrongOnly = request()->get('wrong_only');
        $wrongAnswers = request()->get('wrong_answers');
        
        Log::info('Play method - wrong_only value: ' . ($wrongOnly ?: 'null'));
        
        // 'true'または'1'の場合に間違えた選手モードとして扱う
        $isWrongOnly = $wrongOnly === 'true' || $wrongOnly === '1';
        Log::info('Play method - isWrongOnly resolved to: ' . ($isWrongOnly ? 'true' : 'false'));
        
        // 間違えた選手のみモードの場合
        if ($isWrongOnly && $wrongAnswers) {
            try {
                $wrongPlayerIds = json_decode($wrongAnswers, true) ?: [];
                if (is_array($wrongPlayerIds)) {
                    $wrongPlayerIds = array_map('intval', $wrongPlayerIds);
                    $wrongPlayerIds = array_values(array_unique($wrongPlayerIds));
                    // 間違えた選手の数に基づいて問題数を設定
                    $playerCount = min(count($wrongPlayerIds), $questionCount === '20' ? 20 : count($wrongPlayerIds));
                    Log::info('Wrong only mode - player count set to: ' . $playerCount);
                } else {
                    $playerCount = $questionCount === '20' ? min(20, $totalPlayerCount) : $totalPlayerCount;
                }
            } catch (\Exception $e) {
                Log::error('Error processing wrong answers: ' . $e->getMessage());
                $playerCount = $questionCount === '20' ? min(20, $totalPlayerCount) : $totalPlayerCount;
            }
        } else {
            // 通常モードの場合
            $playerCount = $questionCount === '20' ? min(20, $totalPlayerCount) : $totalPlayerCount;
        }
        
        return view('quiz.index', compact('team', 'teamId', 'playerCount', 'questionCount'));
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
            $wrongData = $request->query('wrong');
            Log::info('Wrong answers in result: ' . $wrongData);
            $wrongAnswers = json_decode($wrongData, true) ?: [];
            Log::info('Decoded wrong answers: ' . json_encode($wrongAnswers));
        }

        return view('quiz.result', compact('team', 'totalQuestions', 'correctCount', 'percentage', 'wrongAnswers'));
    }
}
