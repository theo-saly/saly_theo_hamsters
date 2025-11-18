<?php

namespace App\Controller;

use App\Entity\Hamster;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\HamsterRepository;
use App\Service\HamsterManager;
use App\Service\GameManager;

class HamsterController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    // Récupère tous les hamsters du user
    #[Route('/api/hamsters', name: 'api_hamsters_list', methods: ['GET'])]
    public function getAllHamsters(HamsterRepository $repo, GameManager $gameManager): JsonResponse
    {
        $user = $this->getUser();

        if ($gameManager->checkGameOver($user)) {
            return $this->json(['error' => 'Vous avez perdu, vous n\'avez plus de gold'], 400);
        }

        $hamsters = $repo->findBy(['owner' => $user]);

        return $this->json([
            'listHamsters' => $hamsters
        ], Response::HTTP_OK, [], ['groups' => ['hamsterlist']]);
    }

    // Récupère un hamster spécifique
    #[Route('/api/hamsters/{id}', name: 'api_hamster_detail', methods: ['GET'])]
    public function getHamsterById(int $id, HamsterRepository $repo, GameManager $gameManager): JsonResponse
    {
        $user = $this->getUser();

        if ($gameManager->checkGameOver($user)) {
            return $this->json(['error' => 'Vous avez perdu, vous n\'avez plus de gold'], 400);
        }

        $hamster = $repo->find($id);

        if (!$hamster) {
            return $this->json(['error' => 'Hamster introuvable'], 404);
        }

        if ($hamster->getOwner()->getId() !== $user->getId() && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => 'Accès refusé, rôle ADMIN necessaire'], 403);
        }

        return $this->json([
            'hamster' => $hamster
        ], Response::HTTP_OK, [], ['groups' => ['hamsterlist']]);
    }

    // POST /api/hamsters/reproduce
    // Body: { "idHamster1": xx, "idHamster2": yy }
    #[Route('/api/hamsters/reproduce', name: 'api_hamster_reproduce', methods: ['POST'])]
    public function reproduce(Request $request, HamsterRepository $repo, GameManager $gameManager, HamsterManager $hamsterManager, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if ($gameManager->checkGameOver($user)) {
            return $this->json(['error' => 'Vous avez perdu, vous n\'avez plus de gold'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['idHamster1'], $data['idHamster2'])) {
            return $this->json(['error' => 'idHamster1 et idHamster2 sont requis'], 400);
        }

        $h1 = $repo->find($data['idHamster1']);
        $h2 = $repo->find($data['idHamster2']);

        if (!$h1 || !$h2) {
            return $this->json(['error' => 'Un des hamsters est introuvable'], 404);
        }

        // check droits utilisateur
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && ($h1->getOwner()->getId() !== $user->getId() || $h2->getOwner()->getId() !== $user->getId())) {
            return $this->json(['error' => 'Un des hamsters n\'appartient pas à l\'utilisateur'], 403);
        }

        // check sexe
        if ($h1->getGenre() === $h2->getGenre()) {
            return $this->json(['error' => 'Les hamsters doivent être de sexe opposé'], 400);
        }

        // check actifs
        if (!$h1->isActive() || !$h2->isActive()) {
            return $this->json(['error' => 'Les deux hamsters doivent être actifs'], 400);
        }

        $newHamster = new Hamster();
        $newHamster->setName('Hamster_' . bin2hex(random_bytes(3)));
        $newHamster->setAge(0);
        $newHamster->setHunger(100);
        $newHamster->setGenre(rand(0,1) ? 'm' : 'f');
        $newHamster->setActive(true);
        $newHamster->setOwner($isAdmin ? $h1->getOwner() : $user);

        $em->persist($newHamster);

        // Vieillissement auto
        $hamsterManager->ageHamsters($newHamster->getOwner());

        $em->flush();

        return $this->json([
            'message' => "Nouveau hamster créé avec succès",
            'hamster' => $newHamster
        ], Response::HTTP_OK, [], ['groups' => ['hamsterlist']]);
    }

    // POST /api/hamsters/{id}/feed
    #[Route('/api/hamsters/{id}/feed', name: 'api_hamster_feed', methods: ['POST'])]
    public function feed(int $id, HamsterRepository $repo, GameManager $gameManager, HamsterManager $hamsterManager, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($gameManager->checkGameOver($user)) {
            return $this->json(['error' => 'Vous avez perdu, vous n\'avez plus de gold'], 400);
        }

        $hamster = $repo->find($id);

        if (!$hamster) {
            return $this->json(['error' => 'Hamster introuvable'], 404);
        }

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && $hamster->getOwner()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Un des hamsters n\'appartient pas à l\'utilisateur'], 403);
        }

        if (!$hamster->isActive()) {
            return $this->json(['error' => 'Le hamster n\'est pas actif'], 400);
        }

        // Vieillissement auto
        $hamsterManager->ageHamsters($hamster->getOwner());

        // Calcul du coût
        $currentHunger = $hamster->getHunger();
        $cost = 100 - $currentHunger;

        if (!$isAdmin && $user->getGold() < $cost) {
            return $this->json(['error' => 'Vous n\'avez pas assez de gold'], 400);
        }

        // Débite le coût
        if (!$isAdmin) {
            $user->setGold($user->getGold() - $cost);
        }

        // Remplit le hunger
        $hamster->setHunger(100);

        $em->flush();

        return $this->json([
            'Gold_restant' => $user->getGold(),
            'message' => 'Hamster nourri avec succès'

        ], Response::HTTP_OK);
    }

    // POST /api/hamsters/{id}/sell
    #[Route('/api/hamsters/{id}/sell', name: 'api_hamster_sell', methods: ['POST'])]
    public function sell(int $id, HamsterRepository $repo, GameManager $gameManager, HamsterManager $hamsterManager, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($gameManager->checkGameOver($user)) {
            return $this->json(['error' => 'Vous avez perdu, vous n\'avez plus de gold'], 400);
        }

        $hamster = $repo->find($id);

        if (!$hamster) {
            return $this->json(['error' => 'Hamster introuvable'], 404);
        }

        // Vérifie que l'utilisateur est propriétaire ou admin
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && $hamster->getOwner()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Un des hamsters n\'appartient pas à l\'utilisateur'], 403);
        }

        if (!$hamster->isActive()) {
            return $this->json(['error' => 'Le hamster n\'est pas actif'], 400);
        }

        $owner = $hamster->getOwner();

        // Créditer
        if (!$isAdmin) {
            $owner->setGold($owner->getGold() + 300);
        }

        // Delete
        $em->remove($hamster);
        $em->flush();

        // Vieillissement auto
        $hamsterManager->ageHamsters($owner);

        return $this->json([
            'Gold_restant' => $owner->getGold(),
            'message' => 'Hamster vendu avec succès'
        ], Response::HTTP_OK);
    }

    // POST /api/hamsters/sleep/{nbDays}
    #[Route('/api/hamsters/sleep/{nbDays}', name: 'api_hamster_sleep', methods: ['POST'])]
    public function sleep(int $nbDays, GameManager $gameManager, HamsterManager $hamsterManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($gameManager->checkGameOver($user)) {
            return $this->json(['error' => 'Vous avez perdu, vous n\'avez plus de gold'], 400);
        }

        if ($nbDays <= 0) {
            return $this->json(['error' => 'Le nombre de jours doit être supérieur à 0'], 400);
        }

        // Vieillissement auto
        $hamsterManager->ageHamsters($user, $nbDays, $nbDays);

        return $this->json([
            'message' => "Tous les hamsters ont vieilli de $nbDays jours et perdu $nbDays points de faim."
        ], Response::HTTP_OK);
    }

    // PUT /api/hamsters/{id}/rename
    #[Route('/api/hamsters/{id}/rename', name: 'api_hamster_rename', methods: ['PUT'])]
    public function rename(int $id, Request $request, GameManager $gameManager, HamsterRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($gameManager->checkGameOver($user)) {
            return $this->json(['error' => 'Vous avez perdu, vous n\'avez plus de gold'], 400);
        }

        $hamster = $repo->find($id);

        if (!$hamster) {
            return $this->json(['error' => 'Hamster introuvable'], 404);
        }

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && $hamster->getOwner()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Un des hamsters n\'appartient pas à l\'utilisateur'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $newName = $data['name'] ?? null;

        if (!$newName || strlen($newName) < 2) {
            return $this->json(['error' => 'Le nom doit contenir au moins 2 caractères'], 400);
        }

        $hamster->setName($newName);
        $em->flush();

        return $this->json([
            'message' => "Hamster renommé avec succès",
            'hamster' => $hamster
        ], Response::HTTP_OK, [], ['groups' => ['hamsterlist']]);
    }


}
