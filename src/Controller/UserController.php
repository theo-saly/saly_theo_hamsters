<?php

namespace App\Controller;

use App\Entity\User;
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

        // Champs optionnels
        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }
        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        // Date de création automatique
        $user->setCreatedAt(new \DateTime());

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em->persist($user);
        $em->flush();

        return $this->json([
            "message" => "Utilisateur créé avec succès",
            "user" => [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "firstname" => $user->getFirstname(),
                "lastname" => $user->getLastname(),
                "createdAt" => $user->getCreatedAt()?->format('Y-m-d H:i:s')
            ]
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

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s')
        ], 200);
    }


    // Supprime l'utilisateur
    #[Route('/api/delete/{id}', name: 'api_delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(["error" => "Utilisateur introuvable"], 404);
        }

        // Supprimer tous les véhicules de l'utilisateur
        foreach ($user->getVehicles() as $vehicle) {
            // Supprimer les maintenances du véhicule
            foreach ($vehicle->getVehicleMaintenances() as $maintenance) {
                $em->remove($maintenance);
            }
            // Supprimer les notifications du véhicule
            foreach ($vehicle->getNotifications() as $notification) {
                $em->remove($notification);
            }
            // Supprimer le véhicule
            $em->remove($vehicle);
        }

        // Supprimer toutes les notifications de l'utilisateur
        foreach ($user->getNotifications() as $notification) {
            $em->remove($notification);
        }

        // Supprimer l'utilisateur
        $em->remove($user);
        $em->flush();

        return $this->json(["message" => "Utilisateur et ses données supprimés avec succès"], 200);
    }
}
