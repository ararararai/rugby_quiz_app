<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class LeagueOneTeamsSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'サントリーサンゴリアス', 'logo_path' => 'teams/sungoliath.jpg'],
            ['name' => '東芝ブレイブルーパス東京', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => 'クボタスピアーズ船橋・東京ベイ', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => '静岡ブルーレブズ', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => '神戸製鋼コベルコスティーラーズ神戸', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => '浦安D-Rocks', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => '横浜キヤノンイーグルス', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => '埼玉ワイルドナイツ', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => 'ブラックラムズ東京', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => '三重ホンダヒート', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => 'トヨタグリーンロケッツ', 'logo_path' => 'teams/placeholder.jpg'],
            ['name' => '三菱重工相模原ダイナボアーズ', 'logo_path' => 'teams/placeholder.jpg']
        ];

        foreach ($teams as $team) {
            Team::firstOrCreate(
                ['name' => $team['name']],
                ['logo_path' => $team['logo_path']]
            );
        }

        $this->command->info('リーグワン ディビジョン1のチームデータを追加しました');
    }
}
