<?php

namespace App\Controller;

use App\Entity\Maintenance;
use App\Entity\Vehicle;
use App\Entity\VehicleMaintenance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MaintenanceController extends AbstractController
{
    /**
     * Récupérer toutes les maintenances avec leurs tâches
     */
    #[Route('/api/maintenances', name: 'api_maintenances_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listMaintenances(EntityManagerInterface $em): JsonResponse
    {
        $maintenances = $em->getRepository(Maintenance::class)->findAll();

        $data = array_map(function ($maintenance) {
            return [
                'id' => $maintenance->getId(),
                'name' => $maintenance->getName(),
                'description' => $maintenance->getDescription(),
                'intervalKm' => $maintenance->getIntervalKm(),
                'tasks' => array_map(function ($task) {
                    return [
                        'id' => $task->getId(),
                        'name' => $task->getName(),
                        'description' => $task->getDescription(),
                        'cost' => $task->getCost(),
                    ];
                }, $maintenance->getTasks()->toArray()),
            ];
        }, $maintenances);

        return $this->json(['maintenances' => $data], 200);
    }

    /**
     * Récupérer les maintenances déjà réalisées pour un véhicule
     */
    #[Route('/api/vehicles/{vehicleId}/maintenances/done', name: 'api_vehicle_maintenances_done', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDoneMaintenances(int $vehicleId, EntityManagerInterface $em): JsonResponse
    {
        $vehicle = $em->getRepository(Vehicle::class)->find($vehicleId);
        if (!$vehicle) {
            return $this->json(['error' => 'Véhicule non trouvé'], 404);
        }
        if ($vehicle->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Accès non autorisé'], 403);
        }
        $vehicleMaintenances = $em->getRepository(VehicleMaintenance::class)->findBy(['vehicle' => $vehicle]);
        $data = array_map(function ($vm) {
            $maintenance = $vm->getMaintenance();
            return [
                'id' => $maintenance->getId(),
                'name' => $maintenance->getName(),
                'description' => $maintenance->getDescription(),
                'intervalKm' => $maintenance->getIntervalKm(),
                'tasks' => array_map(function ($task) {
                    return [
                        'id' => $task->getId(),
                        'name' => $task->getName(),
                        'description' => $task->getDescription(),
                        'cost' => $task->getCost(),
                    ];
                }, $maintenance->getTasks()->toArray()),
            ];
        }, $vehicleMaintenances);
        return $this->json(['maintenances_done' => $data], 200);
    }

    /**
     * Ajouter une maintenance réalisée à un véhicule
     */
    #[Route('/api/vehicles/{vehicleId}/maintenances', name: 'api_vehicle_add_maintenance', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addMaintenanceToVehicle(int $vehicleId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $vehicle = $em->getRepository(Vehicle::class)->find($vehicleId);
        if (!$vehicle) {
            return $this->json(['error' => 'Véhicule non trouvé'], 404);
        }
        if ($vehicle->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Accès non autorisé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['maintenanceId'])) {
            return $this->json(['error' => 'L\'ID de la maintenance est requis'], 400);
        }

        $maintenance = $em->getRepository(Maintenance::class)->find($data['maintenanceId']);
        if (!$maintenance) {
            return $this->json(['error' => 'Maintenance non trouvée'], 404);
        }

        // Vérifier si la maintenance n'est pas déjà enregistrée pour ce véhicule
        $existing = $em->getRepository(VehicleMaintenance::class)->findOneBy([
            'vehicle' => $vehicle,
            'maintenance' => $maintenance
        ]);

        if ($existing) {
            return $this->json(['error' => 'Cette maintenance est déjà enregistrée pour ce véhicule'], 400);
        }

        $vehicleMaintenance = new VehicleMaintenance();
        $vehicleMaintenance->setVehicle($vehicle);
        $vehicleMaintenance->setMaintenance($maintenance);

        $em->persist($vehicleMaintenance);
        $em->flush();

        return $this->json([
            'message' => 'Maintenance ajoutée avec succès',
            'maintenance' => [
                'id' => $maintenance->getId(),
                'name' => $maintenance->getName(),
                'description' => $maintenance->getDescription(),
                'intervalKm' => $maintenance->getIntervalKm(),
            ]
        ], 201);
    }

    /**
     * Supprimer une maintenance réalisée d'un véhicule
     */
    #[Route('/api/vehicles/{vehicleId}/maintenances/{maintenanceId}', name: 'api_vehicle_remove_maintenance', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function removeMaintenanceFromVehicle(int $vehicleId, int $maintenanceId, EntityManagerInterface $em): JsonResponse
    {
        $vehicle = $em->getRepository(Vehicle::class)->find($vehicleId);
        if (!$vehicle) {
            return $this->json(['error' => 'Véhicule non trouvé'], 404);
        }
        if ($vehicle->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Accès non autorisé'], 403);
        }

        $maintenance = $em->getRepository(Maintenance::class)->find($maintenanceId);
        if (!$maintenance) {
            return $this->json(['error' => 'Maintenance non trouvée'], 404);
        }

        $vehicleMaintenance = $em->getRepository(VehicleMaintenance::class)->findOneBy([
            'vehicle' => $vehicle,
            'maintenance' => $maintenance
        ]);

        if (!$vehicleMaintenance) {
            return $this->json(['error' => 'Cette maintenance n\'est pas enregistrée pour ce véhicule'], 404);
        }

        $em->remove($vehicleMaintenance);
        $em->flush();

        return $this->json(['message' => 'Maintenance supprimée avec succès'], 200);
    }
}
