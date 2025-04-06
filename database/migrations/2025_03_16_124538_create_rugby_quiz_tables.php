<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // チームテーブル
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        // 選手テーブル
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('english_name')->nullable();
            $table->string('image_path');
            $table->string('team_name')->nullable();
            $table->string('detail_url')->nullable();
            $table->boolean('is_japanese')->default(true);
            $table->foreignId('team_id')->constrained();
            $table->timestamps();
        });

        // ポジションテーブル
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 中間テーブル
        Schema::create('player_position', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_position');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
    }
};
