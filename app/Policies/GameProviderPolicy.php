<?php

namespace App\Policies;

use App\Models\GameProvider;
use App\Models\User;

class GameProviderPolicy
{
    protected function isAllowed(User $user): bool
    {
        // Super admin passa
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        // Permissão granular
        if (method_exists($user, 'can') && $user->can('manage providers')) {
            return true;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function view(User $user, GameProvider $gameProvider): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, GameProvider $gameProvider): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, GameProvider $gameProvider): bool
    {
        return $this->isAllowed($user);
    }
}
