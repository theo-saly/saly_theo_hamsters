<?php

namespace App\Service;

use App\Entity\Hamster;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class HamsterManager
{
    public function __construct(private EntityManagerInterface $em) {}

    public function ageHamsters(User $user, int $days = 5, int $hungerLoss = 5): void
    {
        $hamsters = $user->getHamsters();

        foreach ($hamsters as $hamster) {
            $hamster->setAge($hamster->getAge() + $days);
            $hamster->setHunger($hamster->getHunger() - $hungerLoss);

            // inactif si age > 500 ou faim < 0
            if ($hamster->getAge() > 500 || $hamster->getHunger() < 0) {
                $hamster->setActive(false);
            }
        }

        $this->em->flush();
    }
}
