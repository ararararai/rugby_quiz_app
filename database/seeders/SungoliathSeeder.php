<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Traits\TeamSeederTrait;

class SungoliathSeeder extends Seeder
{
    use TeamSeederTrait;

    public function run(): void
    {
        $this->seedTeam(
            teamName: '東京サントリーサンゴリアス',
            logoPath: 'teams/logo_sungoliath.png',
            csvPath: database_path('seeders/csv/sungoliath_members.csv'),
            columnMap: [
                'image' => 0,
                'name' => 1,
                'team' => 2,
                'is_japanese' => 3,
            ],
            playerImagePrefix: 'players/sungoliath'
        );
    }
} 