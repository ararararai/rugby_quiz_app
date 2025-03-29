<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>クイズ結果 - ラグビー選手クイズ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .team-logo {
            max-width: 150px;
            max-height: 150px;
            margin-bottom: 20px;
        }
        .result-card {
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .score {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
        }
        .percentage {
            font-size: 24px;
            color: #666;
            margin-bottom: 20px;
        }
        .action-btn {
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">クイズ結果</h1>

        <div class="result-card bg-light">
            <div class="text-center">
                <img src="{{ asset($team->logo_path) }}" alt="{{ $team->name }}" class="team-logo">
                <h2>{{ $team->name }}</h2>

                <div class="score">{{ $correctCount }} / {{ $totalQuestions }}</div>
                <div class="percentage">正解率: {{ $percentage }}%</div>

                @php
                    $questionCount = request()->get('question_count', 'all');
                    $questionType = $questionCount === '20' ? '20問' : '全問題';
                @endphp
                <div class="question-type mb-3">
                    <span class="badge bg-info">{{ $questionType }}モード</span>
                </div>

                @if($percentage >= 80)
                    <div class="alert alert-success">素晴らしい！選手をよく覚えていますね！</div>
                @elseif($percentage >= 60)
                    <div class="alert alert-info">なかなかの知識です！もう少し頑張りましょう。</div>
                @else
                    <div class="alert alert-warning">もっと練習が必要かもしれません。再挑戦してみましょう！</div>
                @endif
                <!-- 間違えた選手の一覧 -->
                @if(count($wrongAnswers) > 0)
                <div class="wrong-answers mt-5">
                    <h3 class="text-center mb-4">間違えた選手</h3>
                    <div class="row">
                        @foreach($wrongAnswers as $player)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="{{ $player['image'] }}" class="card-img-top" alt="{{ $player['name'] }}">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $player['name'] }}</h5>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="text-center">
            @php
                $questionCount = request()->get('question_count', 'all');
                $replayUrl = route('quiz.play', [
                    'team_id' => $team->id,
                    'question_count' => $questionCount
                ]);
            @endphp
            <a href="{{ $replayUrl }}" class="btn btn-primary btn-lg action-btn">もう一度プレイ</a>
            @if(count($wrongAnswers) > 0)
                @php
                    // 間違えた選手のIDのみを抽出して確実に整数値に
                    $wrongPlayerIds = [];
                    foreach ($wrongAnswers as $player) {
                        if (isset($player['id']) && is_numeric($player['id'])) {
                            $wrongPlayerIds[] = (int)$player['id'];
                        }
                    }
                    // 重複を削除
                    $wrongPlayerIds = array_values(array_unique($wrongPlayerIds));
                    // 配列の値が空でないことを確認
                    $wrongPlayerIds = array_values(array_filter($wrongPlayerIds));
                    // デバッグ情報
                    error_log('Wrong player IDs for replay: ' . json_encode($wrongPlayerIds));
                @endphp
                @if(count($wrongPlayerIds) > 0)
                    @php
                        // JSON形式に変換して安全にエンコード
                        $encodedWrongPlayerIds = json_encode($wrongPlayerIds, JSON_NUMERIC_CHECK);
                        error_log('Encoded wrong player IDs: ' . $encodedWrongPlayerIds);
                        
                        // 間違えた選手で再度プレイするルートを構築
                        $wrongOnlyUrl = route('quiz.play', [
                            'team_id' => $team->id, 
                            'wrong_only' => 'true', 
                            'wrong_answers' => $encodedWrongPlayerIds,
                            'question_count' => $questionCount
                        ]);
                        error_log('Replay URL: ' . $wrongOnlyUrl);
                    @endphp
                    <a href="{{ $wrongOnlyUrl }}" class="btn btn-warning btn-lg action-btn">間違えた選手で再度プレイ</a>
                @endif
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
