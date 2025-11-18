<?php

namespace App\Service;

use App\Entity\User;

class GameManager
{
    // Si gold < 0 et pas admin => game over
    public function checkGameOver(User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        return $user->getGold() <= 0;
    }
}
