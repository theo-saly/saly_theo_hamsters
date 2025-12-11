<?php

namespace App\Entity;

use App\Repository\MaintenanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceRepository::class)]
class Maintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalKm = null;

    /**
     * @var Collection<int, MaintenanceTask>
     */
    #[ORM\ManyToMany(targetEntity: MaintenanceTask::class, inversedBy: 'maintenances')]
    private Collection $tasks;

    /**
     * @var Collection<int, VehicleMaintenance>
     */
    #[ORM\OneToMany(targetEntity: VehicleMaintenance::class, mappedBy: 'maintenance')]
    private Collection $vehicleMaintenances;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'maintenance')]
    private Collection $notifications;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->vehicleMaintenances = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIntervalKm(): ?int
    {
        return $this->intervalKm;
    }

    public function setIntervalKm(?int $intervalKm): static
    {
        $this->intervalKm = $intervalKm;

        return $this;
    }

    /**
     * @return Collection<int, MaintenanceTask>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(MaintenanceTask $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
        }

        return $this;
    }

    public function removeTask(MaintenanceTask $task): static
    {
        $this->tasks->removeElement($task);

        return $this;
    }

    /**
     * @return Collection<int, VehicleMaintenance>
     */
    public function getVehicleMaintenances(): Collection
    {
        return $this->vehicleMaintenances;
    }

    public function addVehicleMaintenance(VehicleMaintenance $vehicleMaintenance): static
    {
        if (!$this->vehicleMaintenances->contains($vehicleMaintenance)) {
            $this->vehicleMaintenances->add($vehicleMaintenance);
            $vehicleMaintenance->setMaintenance($this);
        }

        return $this;
    }

    public function removeVehicleMaintenance(VehicleMaintenance $vehicleMaintenance): static
    {
        if ($this->vehicleMaintenances->removeElement($vehicleMaintenance)) {
            // set the owning side to null (unless already changed)
            if ($vehicleMaintenance->getMaintenance() === $this) {
                $vehicleMaintenance->setMaintenance(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setMaintenance($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getMaintenance() === $this) {
                $notification->setMaintenance(null);
            }
        }

        return $this;
    }
}
