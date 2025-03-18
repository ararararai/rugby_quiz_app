<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\File;

class SpearsPlayerSeeder extends Seeder
{
    public function run(): void
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

        // 各行を処理
        foreach ($csvData as $row) {
            // CSVの各列のデータを取得 (画像ファイル名と名前のみ)
            $imagePath = $row[0] ?? 'logo_spears.png';
            $name = $row[1] ?? '';

            // 選手情報の作成
            if (!empty($name)) {
                Player::firstOrCreate(
                    ['name' => $name, 'team_id' => $team->id],
                    ['image_path' => 'players/spears/' . $imagePath]
                );
            }
        }

        $this->command->info('スピアーズ選手のデータをインポートしました');
    }
}
