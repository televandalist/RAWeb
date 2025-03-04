<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessageThreadPolicy
{
    use HandlesAuthorization;

    public function manage(User $user): bool
    {
        return false;
    }

    public function view(User $user, MessageThread $messageThread): bool
    {
        // To view the message thread, the user must be one of the participants.
        return $messageThread->participants()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MessageThread $messageThread): bool
    {
        return false;
    }

    public function delete(User $user, MessageThread $messageThread): bool
    {
        $isParticipant = MessageThreadParticipant::where('thread_id', $messageThread->id)
            ->where('user_id', $user->id)
            ->exists();

        return $isParticipant;
    }

    public function restore(User $user, MessageThread $messageThread): bool
    {
        return false;
    }

    public function forceDelete(User $user, MessageThread $messageThread): bool
    {
        return false;
    }
}
