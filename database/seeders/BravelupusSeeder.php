<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Traits\TeamSeederTrait;

class BravelupusSeeder extends Seeder
{
    use TeamSeederTrait;

    public function run(): void
    {
        $this->seedTeam(
            teamName: '東芝ブレイブルーパス東京',
            logoPath: 'teams/logo_bravelupus.png',
            csvPath: database_path('seeders/csv/bravelupus_members.csv'),
            columnMap: [
                'image' => 0,
                'name' => 1,
            ],
            playerImagePrefix: 'players/bravelupus'
        );
    }
} 