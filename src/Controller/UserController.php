<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Hamster;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    // Enregistrement d'un nouvel utilisateur
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return $this->json(["error" => "Email et mot de passe sont requis"], 400);
        }

        if (strlen($data['password']) < 8) {
            return $this->json(["error" => "Le mot de passe doit contenir au minimum 8 caractères"], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($hasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
        $user->setGold(500);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em->persist($user);

        $genres = ['m', 'm', 'f', 'f'];

        foreach ($genres as $index => $g) {
            $hamster = new Hamster();
            $hamster->setName("Hamster" . ($index + 1));
            $hamster->setAge(0);
            $hamster->setHunger(100);
            $hamster->setGenre($g);
            $hamster->setActive(true);
            $hamster->setOwner($user);

            $em->persist($hamster);
        }

        $em->flush();

        return $this->json([
            "message" => "Utilisateur créé avec succès",
            "user" => [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "gold" => $user->getGold(),
                "roles" => $user->getRoles(),
            ],
            "hamsters" => array_map(fn($h) => [
                "id" => $h->getId(),
                "name" => $h->getName(),
                "genre" => $h->getGenre(),
            ], $user->getHamsters()->toArray())
        ], 201);
    }


    // Infos de l'utilisateur courant
    #[Route('/api/user', name: 'api_current_user', methods: ['GET'])]
    public function currentUser(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $hamsters = $user->getHamsters()->map(fn($h) => [
            'id' => $h->getId(),
            'name' => $h->getName(),
            'genre' => $h->getGenre(),
            'age' => $h->getAge(),
            'hunger' => $h->getHunger(),
            'active' => $h->isActive()
        ])->toArray();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'gold' => $user->getGold(),
            'roles' => $user->getRoles(),
            'hamsters' => $hamsters
        ], 200);
    }


    // Supprime l'utilisateur et ses hamsters
    #[Route('/api/delete/{id}', name: 'api_delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(["error" => "Utilisateur introuvable"], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(["message" => "Utilisateur supprimé avec tous ses hamsters"], 200);
    }
}
