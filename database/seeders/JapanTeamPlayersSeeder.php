<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\File;

class JapanTeamPlayersSeeder extends Seeder
{
    public function run(): void
    {
        $japanTeam = Team::where('name', 'Japan')->first();
        
        if (!$japanTeam) {
            $this->command->error('Japan team not found. Please run LeagueOneTeamsSeeder first.');
            return;
        }

        $csvPath = database_path('seeders/csv/japan_members.csv');

        if (!File::exists($csvPath)) {
            $this->command->error('CSVファイルが見つかりません: ' . $csvPath);
            return;
        }

        $csvData = array_map('str_getcsv', file($csvPath));
        array_shift($csvData); // ヘッダー行を削除

        foreach ($csvData as $row) {
            $imageFilename = $row[0] ?? '';
            $name = $row[1] ?? '';

            if (!empty($name)) {
                // 拡張子を小文字に統一
                $imageFilename = preg_replace('/\.(jpg|jpeg|JPG|JPEG)$/i', '.jpg', $imageFilename);

                // ファイルの存在確認
                $imagePath = public_path('players/japan/' . $imageFilename);
                if (!File::exists($imagePath)) {
                    $this->command->warn("画像ファイルが見つかりません: {$imageFilename}");
                    continue;
                }

                // ファイル名をURLエンコード
                $encodedFilename = urlencode($imageFilename);

                Player::firstOrCreate(
                    [
                        'name' => $name,
                        'team_id' => $japanTeam->id,
                    ],
                    [
                        'image_path' => 'players/japan/' . $encodedFilename,
                    ]
                );
            }
        }

        $this->command->info('Japan team players data has been added.');
    }
} 