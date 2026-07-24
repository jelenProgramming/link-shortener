<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    /**
     * Determine whether the user owns the link.
     */
    public function delete(User $user, Link $link): bool
    {
        return $link->user_id === $user->id;
    }
}
