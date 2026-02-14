<?php

namespace App\Policies;

use App\Models\User;
use App\Models\GameProvider;

class GameProviderPolicy
{
    /**
     * Antes de qualquer checagem: admin pode tudo.
     */
    public function before(User $user, string $ability): bool|null
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, GameProvider $gameProvider): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, GameProvider $gameProvider): bool
    {
        return false;
    }

    public function delete(User $user, GameProvider $gameProvider): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, GameProvider $gameProvider): bool
    {
        return false;
    }

    public function forceDelete(User $user, GameProvider $gameProvider): bool
    {
        return false;
    }
}