<?php

namespace Database\Seeders\Traits;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\File;

trait TeamSeederTrait
{
    protected function seedTeam(
        string $teamName,
        string $logoPath,
        string $csvPath,
        array $columnMap,
        string $playerImagePrefix
    ): void {
        // チームを取得またはインサート
        $team = Team::firstOrCreate(
            ['name' => $teamName],
            ['logo_path' => $logoPath]
        );

        // 既存の選手データを削除
        Player::where('team_id', $team->id)->delete();

        if (!File::exists($csvPath)) {
            $this->command->error('CSVファイルが見つかりません: ' . $csvPath);
            return;
        }

        // CSVファイルを読み込む
        $csvData = array_map('str_getcsv', file($csvPath));

        // ヘッダー行をスキップするため、1行目を削除
        $headers = array_shift($csvData);
        
        // デバッグ用にヘッダー情報を出力
        $this->command->info("{$teamName}ヘッダー情報: " . implode(', ', $headers));

        // 各行を処理
        foreach ($csvData as $row) {
            // データを取得
            $imagePath = (isset($row[$columnMap['image']]) && !empty($row[$columnMap['image']])) 
                ? $row[$columnMap['image']] 
                : basename($logoPath);
            
            $name = (isset($row[$columnMap['name']]) && !empty($row[$columnMap['name']])) 
                ? $row[$columnMap['name']] 
                : '';
            
            $englishName = (isset($columnMap['english_name']) && isset($row[$columnMap['english_name']])) 
                ? $row[$columnMap['english_name']] 
                : null;
            
            $teamName = (isset($columnMap['team']) && isset($row[$columnMap['team']])) 
                ? $row[$columnMap['team']] 
                : $teamName;
            
            $detailUrl = (isset($columnMap['detail_url']) && isset($row[$columnMap['detail_url']])) 
                ? $row[$columnMap['detail_url']] 
                : null;

            // 日本人選手かどうかの情報を取得
            $isJapanese = (isset($columnMap['is_japanese']) && isset($row[$columnMap['is_japanese']])) 
                ? (int)$row[$columnMap['is_japanese']] 
                : 1; // デフォルトは日本人選手とする

            // 選手情報の作成
            if (!empty($name)) {
                Player::create([
                    'name' => $name,
                    'team_id' => $team->id,
                    'image_path' => $playerImagePrefix . '/' . $imagePath,
                    'english_name' => $englishName,
                    'team_name' => $teamName,
                    'detail_url' => $detailUrl,
                    'is_japanese' => $isJapanese
                ]);
                $this->command->info("選手追加: $name ($imagePath) - " . ($isJapanese ? "日本人" : "外国人"));
            }
        }
        
        $this->command->info("{$teamName}の選手データをインポートしました");
    }
} 