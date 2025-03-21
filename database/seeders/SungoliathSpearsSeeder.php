<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\File;

class SungoliathSpearsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSungoliath();
        $this->seedSpears();
        
        $this->command->info('サンゴリアスとスピアーズの選手データをインポートしました');
    }
    
    protected function seedSungoliath(): void
    {
        // サンゴリアスのチームを取得またはインサート
        $team = Team::firstOrCreate(
            ['name' => 'サントリーサンゴリアス'],
            ['logo_path' => 'teams/logo_sungoliath.png']
        );

        // CSVファイルのパス
        $csvPath = database_path('seeders/csv/sungoliath_members.csv');

        if (!File::exists($csvPath)) {
            $this->command->error('CSVファイルが見つかりません: ' . $csvPath);
            return;
        }

        // CSVファイルを読み込む
        $csvData = array_map('str_getcsv', file($csvPath));

        // ヘッダー行をスキップするため、1行目を削除
        $headers = array_shift($csvData);
        
        // デバッグ用にヘッダー情報を出力
        $this->command->info('ヘッダー情報: ' . implode(', ', $headers));
        
        // サンゴリアスCSVの列は以下の順序で固定されている
        // 0: 画像ファイル名, 1: 名前, 2: 英語名, 3: ポジション, 4: 詳細URL
        $imageIdx = 0;
        $nameIdx = 1;
        $englishNameIdx = 2;
        $detailUrlIdx = 4;

        // 各行を処理
        foreach ($csvData as $row) {
            // データを取得（配列の添字を使う前に各列の存在を確認）
            $imagePath = (isset($row[$imageIdx]) && !empty($row[$imageIdx])) 
                ? $row[$imageIdx] 
                : 'logo_sungoliath.png';
            
            $name = (isset($row[$nameIdx]) && !empty($row[$nameIdx])) 
                ? $row[$nameIdx] 
                : '';
            
            $englishName = (isset($row[$englishNameIdx])) 
                ? $row[$englishNameIdx] 
                : null;
            
            $detailUrl = (isset($row[$detailUrlIdx])) 
                ? $row[$detailUrlIdx] 
                : null;

            // 選手情報の作成
            if (!empty($name)) {
                Player::updateOrCreate(
                    ['name' => $name, 'team_id' => $team->id],
                    [
                        'image_path' => 'players/sungoliath/' . $imagePath,
                        'english_name' => $englishName,
                        'detail_url' => $detailUrl
                    ]
                );
                $this->command->info("選手追加: $name ($imagePath)");
            }
        }
        
        $this->command->info('サンゴリアスの選手データをインポートしました');
    }
    
    protected function seedSpears(): void
    {
        // スピアーズのチームを取得またはインサート
        $team = Team::firstOrCreate(
            ['name' => 'クボタスピアーズ船橋・東京ベイ'],
            ['logo_path' => 'teams/logo_spears.png']
        );

        // CSVファイルのパス
        $csvPath = database_path('seeders/csv/spears_members.csv');

        if (!File::exists($csvPath)) {
            $this->command->error('CSVファイルが見つかりません: ' . $csvPath);
            return;
        }

        // CSVファイルを読み込む
        $csvData = array_map('str_getcsv', file($csvPath));

        // ヘッダー行をスキップするため、1行目を削除
        $headers = array_shift($csvData);
        
        // デバッグ用にヘッダー情報を出力
        $this->command->info('スピアーズヘッダー情報: ' . implode(', ', $headers));
        
        // スピアーズCSVの列は以下の順序で固定されている
        // 0: image, 1: name_ja
        $imageIdx = 0;
        $nameIdx = 1;

        // 各行を処理
        foreach ($csvData as $row) {
            // データを取得
            $imagePath = (isset($row[$imageIdx]) && !empty($row[$imageIdx])) 
                ? $row[$imageIdx] 
                : 'logo_spears.png';
            
            $name = (isset($row[$nameIdx]) && !empty($row[$nameIdx])) 
                ? $row[$nameIdx] 
                : '';
            
            // 英語名や詳細URLがCSVになければ空値を設定
            $englishName = null;
            $detailUrl = null;

            // 選手情報の作成
            if (!empty($name)) {
                Player::updateOrCreate(
                    ['name' => $name, 'team_id' => $team->id],
                    [
                        'image_path' => 'players/spears/' . $imagePath,
                        'english_name' => $englishName,
                        'detail_url' => $detailUrl
                    ]
                );
                $this->command->info("選手追加: $name ($imagePath)");
            }
        }
        
        $this->command->info('スピアーズの選手データをインポートしました');
    }
} 