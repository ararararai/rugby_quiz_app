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
            margin-bottom: 12px;
            width: 100%;
            text-align: left;
            padding: 12px 15px;
            min-height: 48px;
            white-space: normal;
            word-wrap: break-word;
            font-size: 16px;
            line-height: 1.4;
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
        <!-- 進捗表示 -->
        <div class="progress mb-4">
            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="text-center mb-3">
            <span id="question-counter">問題 <span id="current-question">0</span> / <span
                    id="total-questions">{{ $playerCount ?? 0 }}</span></span>
        </div>

        <div id="loading" class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="quiz-area" style="display: none;">
            <div class="row mb-4">
                <div class="col-md-8 offset-md-2">
                    <div class="text-center">
                        <img id="player-image" src="" alt="選手の写真" class="player-image">
                    </div>
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

        <div class="text-center mt-4">
            <a href="{{ route('team.select') }}" class="btn btn-outline-secondary">
                ← チーム選択に戻る
            </a>
        </div>
    </div>

    <!-- 結果表示モーダル -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultTitle">結果</h5>
                </div>
                <div class="modal-body" id="resultModalBody">
                    <!-- ここに結果の詳細が入ります -->
                </div>
                <div class="modal-footer">
                    <div class="text-center w-100">
                        <span>次の問題まで: <span id="countdown">3</span>秒</span>
                        <button type="button" class="btn btn-primary ms-3" id="nextBtn">次へ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingElement = document.getElementById('loading');
            const quizArea = document.getElementById('quiz-area');
            const playerImageElement = document.getElementById('player-image');
            const choicesContainer = document.getElementById('choices-container');
            const resultContainer = document.getElementById('result-container');
            const nextButton = document.getElementById('next-btn');

            // チームIDを取得
            const teamId = "{{ $teamId ?? '' }}";
            let currentQuestion = null;

            // グローバル変数
            const playerCount = {{ $playerCount ?? 0 }};
            const questionCount = "{{ $questionCount ?? 'all' }}";
            let answeredCount = 0;
            let correctCount = 0;
            let wrongAnswers = [];
            let askedPlayerIds = [];
            
            // URLパラメータから「間違えた選手のみモード」の設定を取得
            const urlParams = new URLSearchParams(window.location.search);
            const wrongOnlyParam = urlParams.get('wrong_only');
            // 文字列'true'または'1'の場合にtrueとして扱う
            const isWrongOnly = wrongOnlyParam === 'true' || wrongOnlyParam === '1';
            
            console.log('URL wrong_only parameter:', wrongOnlyParam);
            console.log('Resolved isWrongOnly value:', isWrongOnly);
            
            let wrongAnswersData = [];
            let wrongPlayerIds = [];
            
            // URLから間違えた選手の情報を取得
            const wrongAnswersParam = new URLSearchParams(window.location.search).get('wrong_answers');
            console.log('Wrong answers param from URL:', wrongAnswersParam);
            
            if (isWrongOnly && wrongAnswersParam) {
                try {
                    // wrongAnswersParamは数値の配列のみを含む文字列
                    wrongPlayerIds = JSON.parse(decodeURIComponent(wrongAnswersParam));
                    console.log('Parsed wrong player IDs:', wrongPlayerIds);
                    
                    // 型変換を確認（整数値であることを確認）
                    wrongPlayerIds = wrongPlayerIds.map(id => parseInt(id, 10));
                    console.log('Converted wrong player IDs:', wrongPlayerIds);
                    
                    // 重複を削除
                    wrongPlayerIds = [...new Set(wrongPlayerIds)];
                    console.log('Unique wrong player IDs:', wrongPlayerIds);
                } catch (e) {
                    console.error('Failed to parse wrong answers data:', e);
                }
            }

            // 進捗状況の更新
            function updateProgress() {
                answeredCount++;
                const progressBar = document.getElementById('progress-bar');
                const percentage = Math.floor((answeredCount / playerCount) * 100);
                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', percentage);

                document.getElementById('current-question').textContent = answeredCount;
            }

            // 問題を取得する関数
            function fetchQuestion() {
                // チームIDがない場合はチーム選択ページにリダイレクト
                if (!teamId || teamId === '') {
                    window.location.href = "{{ route('team.select') }}";
                    return;
                }

                // 全問題が終了した場合、結果画面に遷移
                if (answeredCount >= playerCount) {
                    // 間違えた選手の情報をJSONとしてエンコードしてURLパラメータに追加
                    const wrongAnswersParam = encodeURIComponent(JSON.stringify(wrongAnswers));
                    window.location.href = `/quiz/result/${teamId}?total=${answeredCount}&correct=${correctCount}&wrong=${wrongAnswersParam}&question_count=${questionCount}`;
                    return;
                }
                loadingElement.style.display = 'block';
                quizArea.style.display = 'none';
                resultContainer.style.display = 'none';
                nextButton.style.display = 'none';

                // 出題済みの選手IDをクエリパラメータとして追加
                const askedIdsParam = askedPlayerIds.length > 0 ? `&asked_ids=${askedPlayerIds.join(',')}` : '';
                
                let url = `/quiz/question?team_id=${teamId}${askedIdsParam}`;
                
                console.log('isWrongOnly value before check:', isWrongOnly);
                console.log('isWrongOnly type:', typeof isWrongOnly);
                
                // 間違えた選手のみモードの場合
                if (isWrongOnly) {
                    console.log('Wrong only mode active when fetching question');
                    // 間違えた選手のIDを送信
                    let idsToSend = [];
                    
                    // まず現在のwrongPlayerIdsをチェック（ページ内で記録されたもの）
                    if (wrongPlayerIds.length > 0) {
                        idsToSend = [...wrongPlayerIds];
                        console.log('Using current wrong player IDs:', idsToSend);
                    } 
                    // 次にURLパラメータをチェック（前のページから渡されたもの）
                    else if (wrongAnswersParam) {
                        try {
                            const parsedIds = JSON.parse(decodeURIComponent(wrongAnswersParam));
                            if (Array.isArray(parsedIds) && parsedIds.length > 0) {
                                idsToSend = parsedIds.map(id => parseInt(id, 10));
                                console.log('Using wrong answers param from URL:', idsToSend);
                            }
                        } catch (e) {
                            console.error('Failed to parse wrong answers param:', e);
                        }
                    }
                    
                    // 有効なIDがある場合は送信
                    if (idsToSend.length > 0) {
                        // 重複を削除して整数値に変換
                        idsToSend = [...new Set(idsToSend)].map(id => parseInt(id, 10));
                        const idsJson = JSON.stringify(idsToSend);
                        url += `&wrong_only=true&wrong_answers=${encodeURIComponent(idsJson)}`;
                        console.log('Sending wrong player IDs JSON:', idsJson);
                        console.log('Encoded URL parameter:', encodeURIComponent(idsJson));
                    } else {
                        // IDsがなくても、間違えた選手モードであることをマークする
                        url += '&wrong_only=true';
                        console.warn('No wrong player IDs available, but still using wrong_only mode');
                    }
                }
                
                console.log('Final URL being fetched:', url);

                fetch(url)
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
                        // 出題済みリストを更新
                        if (data.asked_ids) {
                            askedPlayerIds = data.asked_ids.split(',').map(Number);
                        }
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

            // 結果を表示する関数（モーダル版）
            function displayResult(data) {
                // 選択肢を無効化
                const choiceButtons = document.querySelectorAll('.choice-btn');
                choiceButtons.forEach(button => {
                    button.disabled = true;
                    if (button.dataset.id == data.correct_player.id) {
                        button.classList.remove('btn-outline-secondary');
                        button.classList.add('btn-success');
                    }
                });

                // 正解数のカウント
                if (data.correct) {
                    correctCount++;
                } else {
                    // 間違えた選手を記録
                    const correctPlayerId = parseInt(data.correct_player.id, 10);
                    console.log('Recording wrong answer:', correctPlayerId);
                    
                    wrongAnswers.push({
                        id: correctPlayerId,
                        name: data.correct_player.name,
                        team: data.correct_player.team,
                        image: currentQuestion.question.player_image
                    });
                    
                    console.log('Updated wrong answers:', wrongAnswers);

                    // 間違えた選手のIDのリストを更新（間違えた選手のみモード用）
                    if (isWrongOnly) {
                        // 間違えた選手のIDを追加（まだリストにない場合）
                        if (!wrongPlayerIds.includes(correctPlayerId)) {
                            wrongPlayerIds.push(correctPlayerId);
                            console.log('Updated wrong player IDs:', wrongPlayerIds);
                        }
                    }
                }

                // 進捗状況の更新
                updateProgress();

                // モーダルの内容を設定
                const resultTitle = document.getElementById('resultTitle');
                const resultModalBody = document.getElementById('resultModalBody');

                resultTitle.textContent = data.correct ? '正解！' : '不正解...';
                resultTitle.className = 'modal-title ' + (data.correct ? 'text-success' : 'text-danger');

                resultModalBody.innerHTML = `
                    <p class="text-center">正解は <strong>${data.correct_player.name}</strong> (${data.correct_player.team}) です。</p>
                `;

                // モーダルを表示
                const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
                resultModal.show();

                // 自動的に次の問題に進むタイマーをセット
                let secondsLeft = 3;
                document.getElementById('countdown').textContent = secondsLeft;

                const timer = setInterval(() => {
                    secondsLeft--;
                    document.getElementById('countdown').textContent = secondsLeft;

                    if (secondsLeft <= 0) {
                        clearInterval(timer);
                        resultModal.hide();
                        fetchQuestion(); // 次の問題を取得
                    }
                }, 1000);

                // 「次へ」ボタンのイベントリスナー
                document.getElementById('nextBtn').addEventListener('click', () => {
                    clearInterval(timer);
                    resultModal.hide();
                    fetchQuestion(); // 次の問題を取得
                }, {
                    once: true
                });
            }

            // 次の問題ボタンのイベントリスナー（現在は使われていませんが、念のため残しておく）
            nextButton.addEventListener('click', fetchQuestion);

            // 初回の問題を取得
            fetchQuestion();
        });
    </script>
</body>

</html>
