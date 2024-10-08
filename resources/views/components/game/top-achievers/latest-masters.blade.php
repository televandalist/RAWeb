@props([
    'latestMasters' => [],
    'numMasters' => 0,
])

<?php

use App\Models\User;
use Carbon\Carbon;

$rank = $numMasters;

?>

<div class="component">
    <h2 class="text-h3">Latest Masters</h2>

    <div class="max-h-[980px] overflow-y-auto">
        <table class='table-highlight'>
            <thead>
                <tr class='do-not-highlight'>
                    <th class="text-right">#</th>
                    <th>User</th>
                    <th>Mastered</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($latestMasters as $mastery)
                    @php
                        $masteryUser = User::find($mastery['user_id']);
                        if (!$masteryUser) {
                            continue;
                        }
                        $masteryDate = Carbon::createFromTimestamp($mastery['last_unlock_hardcore_at']);
                    @endphp

                    <x-game.top-achievers.mastery-row :rank="$rank" :masteryUser="$masteryUser" :masteryDate="$masteryDate" includeTime="false" />

                    @php
                        $rank--;
                    @endphp
                @endforeach
            </tbody>
        </table>
    </div>
</div>
