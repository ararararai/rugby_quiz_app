<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>クイズ開始 - ラグビー選手クイズ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .team-info {
            margin: 30px 0;
            text-align: center;
        }
        .team-logo {
            max-width: 150px;
            max-height: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">ラグビー選手クイズ</h1>

        <div class="text-center mb-3">
            <a href="{{ route('team.select') }}" class="btn btn-outline-secondary btn-sm">
                ← チーム選択に戻る
            </a>
        </div>

        <div class="team-info">
            <img src="{{ asset($team->logo_path) }}" alt="{{ $team->name }}" class="team-logo">
            <h2>{{ $team->name }}</h2>
            <p>このチームには {{ $playerCount }} 人の選手がいます。全ての選手を当てられるでしょうか？</p>
        </div>

        <div class="text-center">
            <a href="{{ route('quiz.play', ['team_id' => $team->id]) }}" class="btn btn-primary btn-lg">クイズを始める</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
