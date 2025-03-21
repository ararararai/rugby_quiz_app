<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Team;
use App\Models\Player;

class DeleteTestTeam extends Command
{
    protected $signature = 'app:delete-test-team';
    protected $description = 'テストチームとその選手データを削除します';

    public function handle()
    {
        $this->info('テストチームを削除します...');
        
        // テストチームを検索
        $team = Team::where('name', 'テストチーム')->first();
        
        if (!$team) {
            $this->warn('テストチームが見つかりません');
            return 0;
        }
        
        // チームIDを記録
        $teamId = $team->id;
        
        // 選手数を取得
        $playerCount = Player::where('team_id', $teamId)->count();
        
        if ($this->confirm("テストチームとその選手 {$playerCount}名 を削除しますか？", true)) {
            // テストチームに属する選手を削除
            $deleted = Player::where('team_id', $teamId)->delete();
            $this->info("テストチームの選手 {$deleted}名 を削除しました");
            
            // テストチームを削除
            $team->delete();
            $this->info('テストチームを削除しました');
            
            // DatabaseSeederからTestPlayersSeederの参照を削除するよう案内
            $this->warn('DatabaseSeeder.phpからTestPlayersSeeder::classの参照を削除してください');
        } else {
            $this->info('削除をキャンセルしました');
        }
        
        return 0;
    }
} 