<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'サントリーサンゴリアス', 'logo_path' => 'teams/sungoliath.jpg']
            // 他のチームも必要に応じて追加
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
