<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TestPlayersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // テスト用のデータを挿入する前に既存のデータをクリア
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Player::truncate();
        Team::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // テスト用ディレクトリの作成
        $teamImagePath = public_path('images/teams/test');
        $playerImagePath = public_path('images/players/test');
        
        if (!File::exists($teamImagePath)) {
            File::makeDirectory($teamImagePath, 0755, true);
        }
        
        if (!File::exists($playerImagePath)) {
            File::makeDirectory($playerImagePath, 0755, true);
        }

        // テスト用のチームを作成
        $team = Team::create([
            'name' => 'テストチーム',
            'logo_path' => 'images/teams/test/test_team.png',
        ]);

        // チームロゴをコピー（既存のロゴを利用）
        $sourceLogoPath = public_path('images/teams/default_team.png');
        $targetLogoPath = public_path('images/teams/test/test_team.png');
        
        if (File::exists($sourceLogoPath)) {
            File::copy($sourceLogoPath, $targetLogoPath);
        } else {
            // デフォルトロゴがない場合は空のファイルを作成
            File::put($targetLogoPath, '');
        }

        // テスト用の選手データ（10名分）
        $players = [
            ['name' => 'テスト選手1', 'english_name' => 'Test Player 1'],
            ['name' => 'テスト選手2', 'english_name' => 'Test Player 2'],
            ['name' => 'テスト選手3', 'english_name' => 'Test Player 3'],
            ['name' => 'テスト選手4', 'english_name' => 'Test Player 4'],
            ['name' => 'テスト選手5', 'english_name' => 'Test Player 5'],
            ['name' => 'テスト選手6', 'english_name' => 'Test Player 6'],
            ['name' => 'テスト選手7', 'english_name' => 'Test Player 7'],
            ['name' => 'テスト選手8', 'english_name' => 'Test Player 8'],
            ['name' => 'テスト選手9', 'english_name' => 'Test Player 9'],
            ['name' => 'テスト選手10', 'english_name' => 'Test Player 10'],
        ];

        // デフォルト選手画像パス
        $defaultPlayerImagePath = public_path('images/players/default_player.png');

        // 選手データの挿入
        foreach ($players as $index => $playerData) {
            $playerNumber = $index + 1;
            $playerImageName = 'test_player_' . $playerNumber . '.png';
            $playerImageTargetPath = public_path('images/players/test/' . $playerImageName);
            
            // 選手画像をコピー
            if (File::exists($defaultPlayerImagePath)) {
                File::copy($defaultPlayerImagePath, $playerImageTargetPath);
            } else {
                // デフォルト画像がない場合は空のファイルを作成
                File::put($playerImageTargetPath, '');
            }
            
            // 選手データ作成
            Player::create([
                'team_id' => $team->id,
                'name' => $playerData['name'],
                'english_name' => $playerData['english_name'],
                'image_path' => 'images/players/test/' . $playerImageName,
                'detail_url' => null,
            ]);
        }

        $this->command->info('テスト用の選手データが正常に追加されました');
    }
}
