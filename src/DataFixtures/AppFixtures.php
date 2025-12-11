<?php

namespace App\DataFixtures;

use App\Entity\Maintenance;
use App\Entity\MaintenanceTask;
use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleMaintenance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Users
        $user = (new User())
            ->setEmail('demo@mycarcare.test')
            ->setPassword('password')
            ->setFirstname('Demo')
            ->setLastname('User')
            ->setCreatedAt(new \DateTime('-10 days'));
        $manager->persist($user);

        // Vehicles
        $vehicle1 = (new Vehicle())
            ->setUser($user)
            ->setPlateNumber('EH-640-FW')
            ->setVin('VF3XXXXXXXX123456')
            ->setBrand('Peugeot')
            ->setModel('308')
            ->setYear(2019)
            ->setFuelType('Essence')
            ->setMileage(84500)
            ->setCreatedAt(new \DateTime('-5 days'))
            ->setUpdatedAt(new \DateTime('-1 day'));
        $manager->persist($vehicle1);

        $vehicle2 = (new Vehicle())
            ->setUser($user)
            ->setPlateNumber('AA-123-BB')
            ->setVin('WVWZZZ1JZXW000001')
            ->setBrand('Volkswagen')
            ->setModel('Golf')
            ->setYear(2016)
            ->setFuelType('Diesel')
            ->setMileage(124500)
            ->setCreatedAt(new \DateTime('-60 days'))
            ->setUpdatedAt(new \DateTime('-5 days'));
        $manager->persist($vehicle2);

        // Maintenance Tasks
        $tOilChange = (new MaintenanceTask())
            ->setName('Vidange moteur')
            ->setDescription('Remplacement de l\'huile moteur et du filtre');
        $manager->persist($tOilChange);

        $tAirFilter = (new MaintenanceTask())
            ->setName('Filtre à air')
            ->setDescription('Remplacement du filtre à air de l\'admission');
        $manager->persist($tAirFilter);

        $tBrakeCheck = (new MaintenanceTask())
            ->setName('Contrôle freins')
            ->setDescription('Contrôle disques/plaquettes et niveau de liquide');
        $manager->persist($tBrakeCheck);

        $tRotateTires = (new MaintenanceTask())
            ->setName('Permutation des pneus')
            ->setDescription('Permutation AV/AR si nécessaire');
        $manager->persist($tRotateTires);

        // Maintenances (packages)
        $mService20k = (new Maintenance())
            ->setName('Révision 20 000 km')
            ->setDescription('Entretien périodique')
            ->setIntervalKm(20000)
            ->addTask($tOilChange)
            ->addTask($tAirFilter)
            ->addTask($tBrakeCheck);
        $manager->persist($mService20k);

        $mService60k = (new Maintenance())
            ->setName('Révision 60 000 km')
            ->setDescription('Entretien majeur')
            ->setIntervalKm(60000)
            ->addTask($tOilChange)
            ->addTask($tAirFilter)
            ->addTask($tBrakeCheck)
            ->addTask($tRotateTires);
        $manager->persist($mService60k);

        // VehicleMaintenances (history/planned)
        $vm1 = (new VehicleMaintenance())
            ->setVehicle($vehicle1)
            ->setMaintenance($mService20k);
        $manager->persist($vm1);

        $vm2 = (new VehicleMaintenance())
            ->setVehicle($vehicle2)
            ->setMaintenance($mService60k);
        $manager->persist($vm2);

        // Notifications d'entretien (exemple)
        $notif = (new Notification())
            ->setUser($user)
            ->setVehicle($vehicle1)
            ->setMaintenance($mService20k)
            ->setScheduledAt((new \DateTime('+10 days'))->setTime(9, 0))
            ->setIsSent(false);
        $manager->persist($notif);

        $manager->flush();
    }
}
