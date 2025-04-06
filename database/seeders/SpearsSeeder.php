<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Traits\TeamSeederTrait;

class SpearsSeeder extends Seeder
{
    use TeamSeederTrait;

    public function run(): void
    {
        $this->seedTeam(
            teamName: 'クボタスピアーズ船橋・東京ベイ',
            logoPath: 'teams/logo_spears.png',
            csvPath: database_path('seeders/csv/spears_members.csv'),
            columnMap: [
                'image' => 0,
                'name' => 1,
                'team' => 2,
                'is_japanese' => 3,
            ],
            playerImagePrefix: 'players/spears'
        );
    }
} 