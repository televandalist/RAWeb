<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('player_games', function (Blueprint $table) {
            $table->index(
                [
                    'game_id',
                    'achievements_unlocked',
                    'achievements_total',
                    'user_id',
                    'id',
                ],
                'player_games_completion_sample_idx' // manually named because the auto-generated name is too long
            );
        });
    }

    public function down(): void
    {
        Schema::table('player_games', function (Blueprint $table) {
            $table->dropIndex('player_games_completion_sample_idx');
        });
    }
};
