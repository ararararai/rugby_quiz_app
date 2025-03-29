<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class LeagueOneTeamsSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'サントリーサンゴリアス', 'logo_path' => 'teams/logo_sungoliath.png'],
            ['name' => '東芝ブレイブルーパス東京', 'logo_path' => 'teams/logo_bravelupus.png'],
            ['name' => 'クボタスピアーズ船橋・東京ベイ', 'logo_path' => 'teams/logo_spears.png'],
            ['name' => '静岡ブルーレブズ', 'logo_path' => 'teams/logo_bluerevs.png'],
            ['name' => '神戸製鋼コベルコスティーラーズ神戸', 'logo_path' => 'teams/logo_kobesteelers.png'],
            ['name' => '浦安D-Rocks', 'logo_path' => 'teams/logo_d-rocks.png'],
            ['name' => '横浜キヤノンイーグルス', 'logo_path' => 'teams/logo_eagles.png'],
            ['name' => '埼玉ワイルドナイツ', 'logo_path' => 'teams/logo_wind_nights.png'],
            ['name' => 'ブラックラムズ東京', 'logo_path' => 'teams/logo_blackrams.png'],
            ['name' => '三重ホンダヒート', 'logo_path' => 'teams/logo_heat.png'],
            ['name' => 'トヨタベルブリッツ', 'logo_path' => 'teams/logo_verblitz.png'],
            ['name' => '三菱重工相模原ダイナボアーズ', 'logo_path' => 'teams/logo_dynaboars.png']
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
