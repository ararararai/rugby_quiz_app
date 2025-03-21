<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Team;
use App\Models\Player;

class ShowTeamData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-team-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'チームとプレイヤーのデータを表示します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== チーム情報 ===');
        $teams = Team::all();
        
        if ($teams->isEmpty()) {
            $this->error('チームが登録されていません');
            return 1;
        }
        
        $this->table(['ID', '名前', 'ロゴパス'], $teams->map(function ($team) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'logo_path' => $team->logo_path
            ];
        }));
        
        $this->info('=== チームごとのプレイヤー数 ===');
        $playerCounts = Player::selectRaw('team_id, count(*) as count')
            ->groupBy('team_id')
            ->get();
            
        if ($playerCounts->isEmpty()) {
            $this->error('プレイヤーが登録されていません');
            return 1;
        }
        
        $data = $playerCounts->map(function ($item) use ($teams) {
            $teamName = $teams->where('id', $item->team_id)->first()->name ?? 'Unknown';
            return [
                'team_id' => $item->team_id,
                'team_name' => $teamName,
                'player_count' => $item->count
            ];
        });
        
        $this->table(['チームID', 'チーム名', 'プレイヤー数'], $data);
        
        // サンゴリアスとスピアーズのプレイヤーを確認
        $sungoliath = Team::where('name', 'サントリーサンゴリアス')->first();
        $spears = Team::where('name', 'クボタスピアーズ船橋・東京ベイ')->first();
        
        if ($sungoliath) {
            $this->info("=== サンゴリアスのプレイヤー (最初の5件) ===");
            $players = Player::where('team_id', $sungoliath->id)->take(5)->get();
            $this->table(['ID', '名前', '英語名', '画像パス'], $players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'english_name' => $player->english_name,
                    'image_path' => $player->image_path
                ];
            }));
        }
        
        if ($spears) {
            $this->info("=== スピアーズのプレイヤー (最初の5件) ===");
            $players = Player::where('team_id', $spears->id)->take(5)->get();
            $this->table(['ID', '名前', '英語名', '画像パス'], $players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'english_name' => $player->english_name,
                    'image_path' => $player->image_path
                ];
            }));
        }
        
        return 0;
    }
}
