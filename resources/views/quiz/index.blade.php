<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ラグビー選手クイズ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .player-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .choice-btn {
            margin-bottom: 10px;
            width: 100%;
            text-align: left;
            padding: 10px 15px;
        }
        .result-container {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
        }
        .correct {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .incorrect {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container quiz-container">
        <h1 class="text-center mb-4">ラグビー選手クイズ</h1>

        <div class="text-center mb-3">
            <a href="{{ route('team.select') }}" class="btn btn-outline-secondary btn-sm">
                ← チーム選択に戻る
            </a>
        </div>

        <div id="loading" class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="quiz-area" style="display: none;">
            <div class="row mb-4">
                <div class="col-md-8 offset-md-2">
                    <h3 id="team-name" class="text-center mb-3"></h3>
                    <div class="text-center">
                        <img id="player-image" src="" alt="選手の写真" class="player-image">
                    </div>
                    <h4 class="text-center mb-3">この選手は誰でしょう？</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div id="choices-container"></div>
                </div>
            </div>

            <div id="result-container" class="result-container text-center" style="display: none;"></div>

            <div class="text-center mt-4">
                <button id="next-btn" class="btn btn-primary" style="display: none;">次の問題</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingElement = document.getElementById('loading');
            const quizArea = document.getElementById('quiz-area');
            const teamNameElement = document.getElementById('team-name');
            const playerImageElement = document.getElementById('player-image');
            const choicesContainer = document.getElementById('choices-container');
            const resultContainer = document.getElementById('result-container');
            const nextButton = document.getElementById('next-btn');

            // チームIDを取得
            const teamId = "{{ $teamId ?? '' }}";
            let currentQuestion = null;

            // 問題を取得する関数
            function fetchQuestion() {
                // チームIDがない場合はチーム選択ページにリダイレクト
                if (!teamId || teamId === '') {
                    window.location.href = "{{ route('team.select') }}";
                    return;
                }

                loadingElement.style.display = 'block';
                quizArea.style.display = 'none';
                resultContainer.style.display = 'none';
                nextButton.style.display = 'none';

                fetch('/quiz/question?team_id=' + teamId)
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`Status: ${response.status}, Message: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Question data:', data);
                        currentQuestion = data;
                        displayQuestion(data);
                        loadingElement.style.display = 'none';
                        quizArea.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error details:', error);
                        loadingElement.style.display = 'none';
                        alert('問題の取得に失敗しました。ページをリロードしてください。\nエラー: ' + error.message);
                    });
            }

            // 問題を表示する関数
            function displayQuestion(data) {
                teamNameElement.textContent = data.question.team_name + 'の選手';
                playerImageElement.src = data.question.player_image;

                choicesContainer.innerHTML = '';
                data.choices.forEach(choice => {
                    const button = document.createElement('button');
                    button.className = 'btn btn-outline-secondary choice-btn';
                    button.textContent = choice.name;
                    button.dataset.id = choice.id;
                    button.addEventListener('click', () => checkAnswer(choice.id));
                    choicesContainer.appendChild(button);
                });
            }

            // 回答をチェックする関数
            function checkAnswer(answerId) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/quiz/check', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        player_id: currentQuestion.question.correct_id,
                        answer_id: answerId,
                        team_id: teamId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Status: ${response.status}, Message: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    displayResult(data);
                })
                .catch(error => {
                    console.error('Error checking answer:', error);
                    alert('回答の確認に失敗しました。\nエラー: ' + error.message);
                });
            }

            // 結果を表示する関数
            function displayResult(data) {
                resultContainer.innerHTML = '';
                resultContainer.className = 'result-container text-center ' + (data.correct ? 'correct' : 'incorrect');

                const resultText = document.createElement('h4');
                resultText.textContent = data.correct ? '正解！' : '不正解...';

                const playerInfo = document.createElement('p');
                playerInfo.textContent = `正解は ${data.correct_player.name} (${data.correct_player.team})`;

                resultContainer.appendChild(resultText);
                resultContainer.appendChild(playerInfo);
                resultContainer.style.display = 'block';

                // 選択肢を無効化
                const choiceButtons = document.querySelectorAll('.choice-btn');
                choiceButtons.forEach(button => {
                    button.disabled = true;
                    if (button.dataset.id == data.correct_player.id) {
                        button.classList.remove('btn-outline-secondary');
                        button.classList.add('btn-success');
                    }
                });

                nextButton.style.display = 'inline-block';
            }

            // 次の問題ボタンのイベントリスナー
            nextButton.addEventListener('click', fetchQuestion);

            // 初回の問題を取得
            fetchQuestion();
        });
    </script>
</body>
</html>
