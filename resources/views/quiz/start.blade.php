<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>クイズ開始 - {{ $team->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .team-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .team-logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
        }
        .team-card {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .team-card:hover {
            transform: translateY(-5px);
        }
        .quiz-options {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container team-container">
        <h1 class="text-center mb-4">{{ $team->name }} クイズ</h1>
        
        <div class="quiz-options">
            <h3 class="text-center mb-4">クイズの設定</h3>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="questionCount" class="form-label">問題数</label>
                        <select class="form-select" id="questionCount">
                            <option value="all">全問題 ({{ $playerCount }}問)</option>
                            <option value="20">20問</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('team.select') }}" class="btn btn-outline-secondary me-2">チーム選択に戻る</a>
            <button onclick="startQuiz()" class="btn btn-primary">クイズを開始</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function startQuiz() {
            const questionCount = document.getElementById('questionCount').value;
            const url = new URL("{{ route('quiz.play', ['team_id' => $team->id]) }}", window.location.origin);
            url.searchParams.append('question_count', questionCount);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
