<?php

namespace App\Controller;

use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VehicleController extends AbstractController
{
    private const API_URL = 'https://api.apiplaqueimmatriculation.com/plaque';
    private const API_TOKEN = 'TokenDemo2025A';
    private const API_COUNTRY = 'FR';

    /**
     * Enregistrer un véhicule via sa plaque d'immatriculation
     */
    #[Route('/api/vehicles/register', name: 'api_vehicle_register', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function registerVehicleByPlate(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['plateNumber'])) {
            return $this->json(["error" => "La plaque d'immatriculation est requise"], 400);
        }

        $plateNumber = trim($data['plateNumber']);

        // Valider le format de la plaque
        if (empty($plateNumber)) {
            return $this->json(["error" => "La plaque d'immatriculation ne peut pas être vide"], 400);
        }

        try {
            // Construire l'URL de l'API
            $url = self::API_URL . '?' . http_build_query([
                'immatriculation' => $plateNumber,
                'token' => self::API_TOKEN,
                'pays' => self::API_COUNTRY,
            ]);

            // Appeler l'API de plaques d'immatriculation
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return $this->json([
                    "error" => "Impossible de récupérer les informations de la plaque d'immatriculation"
                ], 500);
            }

            $apiData = json_decode($response, true);

            if ($apiData === null) {
                return $this->json([
                    "error" => "Réponse invalide de l'API"
                ], 500);
            }

            // Les données sont dans 'data'
            if (!isset($apiData['data']) || empty($apiData['data'])) {
                return $this->json([
                    "error" => "Plaque d'immatriculation invalide ou non trouvée"
                ], 404);
            }

            $vehicleData = $apiData['data'];

            // Vérifier si l'API a retourné une erreur
            if (!empty($vehicleData['erreur'])) {
                return $this->json([
                    "error" => "Erreur API: " . $vehicleData['erreur']
                ], 404);
            }

            // Créer le véhicule
            $vehicle = new Vehicle();
            $vehicle->setPlateNumber($plateNumber);
            $vehicle->setBrand($vehicleData['marque'] ?? null);
            $vehicle->setModel($vehicleData['modele'] ?? null);

            // Extraire l'année de 'debut_modele' (format: YYYY-MM)
            $year = null;
            if (!empty($vehicleData['debut_modele'])) {
                $year = (int) explode('-', $vehicleData['debut_modele'])[0];
            }
            $vehicle->setYear($year);

            $vehicle->setVin($vehicleData['vin'] ?? null);
            $vehicle->setFuelType($vehicleData['energieNGC'] ?? $vehicleData['type_moteur'] ?? null);
            $vehicle->setBrandLogo($vehicleData['logo_marque'] ?? null);
            $vehicle->setModelPhoto($vehicleData['photo_modele'] ?? null);
            $vehicle->setMileage(0);
            $vehicle->setUser($this->getUser());
            $vehicle->setCreatedAt(new \DateTime());
            $vehicle->setUpdatedAt(new \DateTime());

            $em->persist($vehicle);
            $em->flush();

            return $this->json([
                "message" => "Véhicule enregistré avec succès",
                "vehicle" => [
                    "id" => $vehicle->getId(),
                    "plateNumber" => $vehicle->getPlateNumber(),
                    "brand" => $vehicle->getBrand(),
                    "model" => $vehicle->getModel(),
                    "year" => $vehicle->getYear(),
                    "vin" => $vehicle->getVin(),
                    "fuelType" => $vehicle->getFuelType(),
                    "logo_marque" => $vehicle->getBrandLogo(),
                    "photo_modele" => $vehicle->getModelPhoto(),
                    "mileage" => $vehicle->getMileage(),
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json([
                "error" => "Erreur lors de la récupération des données du véhicule",
                "details" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer tous les véhicules de l'utilisateur
     */
    #[Route('/api/vehicles', name: 'api_vehicles_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listVehicles(EntityManagerInterface $em): JsonResponse
    {
        $vehicles = $em->getRepository(Vehicle::class)->findBy(['user' => $this->getUser()]);

        $vehicleData = array_map(fn($vehicle) => [
            "id" => $vehicle->getId(),
            "plateNumber" => $vehicle->getPlateNumber(),
            "brand" => $vehicle->getBrand(),
            "model" => $vehicle->getModel(),
            "year" => $vehicle->getYear(),
            "vin" => $vehicle->getVin(),
            "fuelType" => $vehicle->getFuelType(),
            "logo_marque" => $vehicle->getBrandLogo(),
            "photo_modele" => $vehicle->getModelPhoto(),
            "mileage" => $vehicle->getMileage(),
            "createdAt" => $vehicle->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $vehicles);

        return $this->json([
            "count" => count($vehicleData),
            "vehicles" => $vehicleData
        ], 200);
    }

    /**
     * Supprimer un véhicule
     */
    #[Route('/api/vehicles/{id}', name: 'api_vehicle_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteVehicle(int $id, EntityManagerInterface $em): JsonResponse
    {
        $vehicle = $em->getRepository(Vehicle::class)->find($id);

        if (!$vehicle) {
            return $this->json(["error" => "Véhicule non trouvé"], 404);
        }

        // Vérifier que l'utilisateur est propriétaire du véhicule
        if ($vehicle->getUser() !== $this->getUser()) {
            return $this->json(["error" => "Accès non autorisé"], 403);
        }

        // Supprimer les maintenances associées au véhicule
        foreach ($vehicle->getVehicleMaintenances() as $vehicleMaintenance) {
            $em->remove($vehicleMaintenance);
        }

        $em->remove($vehicle);
        $em->flush();

        return $this->json(["message" => "Véhicule supprimé avec succès"], 200);
    }
}
