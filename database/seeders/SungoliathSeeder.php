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
            teamName: 'サントリーサンゴリアス',
            logoPath: 'teams/logo_sungoliath.png',
            csvPath: database_path('seeders/csv/sungoliath_members.csv'),
            columnMap: [
                'image' => 0,
                'name' => 1,
                'english_name' => 2,
                'detail_url' => 4,
            ],
            playerImagePrefix: 'players/sungoliath'
        );
    }
} 