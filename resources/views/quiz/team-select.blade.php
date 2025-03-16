<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ラグビー選手クイズ - チーム選択</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .team-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .team-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .team-logo {
            max-height: 100px;
            object-fit: contain;
            margin: 15px auto;
        }
    </style>
</head>
<body>
    <div class="container team-container">
        <h1 class="text-center mb-4">ラグビー選手クイズ</h1>
        <p class="text-center mb-4">クイズを始めるチームを選択してください</p>

        <div class="row">
            @foreach($teams as $team)
                <div class="col-md-4">
                    <div class="card team-card" onclick="selectTeam({{ $team->id }})">
                        <div class="text-center">
                            <img src="{{ asset($team->logo_path) }}" alt="{{ $team->name }}" class="team-logo">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-center">{{ $team->name }}</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectTeam(teamId) {
            window.location.href = '/quiz?team_id=' + teamId;
        }
    </script>
</body>
</html>

