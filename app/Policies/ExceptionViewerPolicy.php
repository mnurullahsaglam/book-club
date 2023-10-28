<?php

namespace App\Policies;

use App\Models\User;

class ExceptionViewerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
