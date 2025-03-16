<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\File;

class SungoliathPlayerSeeder extends Seeder
{
    public function run(): void
    {
        // サンゴリアスのチームを取得またはインサート
        $team = Team::firstOrCreate(
            ['name' => 'サントリーサンゴリアス'],
            ['logo_path' => 'teams/placeholder.jpg']
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

        // 各行を処理
        foreach ($csvData as $row) {
            // CSVの各列のデータを取得 (画像ファイル名と名前のみ)
            $imagePath = $row[0] ?? 'placeholder.jpg';
            $name = $row[1] ?? '';

            // 選手情報の作成
            if (!empty($name)) {
                Player::firstOrCreate(
                    ['name' => $name, 'team_id' => $team->id],
                    ['image_path' => 'players/sungoliath/' . $imagePath]
                );
            }
        }

        $this->command->info('サンゴリアス選手のデータをインポートしました');
    }
}
