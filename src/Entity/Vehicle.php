<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $plateNumber = null;

    #[ORM\Column(length: 50)]
    private ?string $vin = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(nullable: true)]
    private ?int $mileage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fuelType = null;

    #[ORM\Column(length: 255, nullable: true, name: 'logo_marque')]
    private ?string $brandLogo = null;

    #[ORM\Column(length: 255, nullable: true, name: 'photo_modele')]
    private ?string $modelPhoto = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, VehicleMaintenance>
     */
    #[ORM\OneToMany(targetEntity: VehicleMaintenance::class, mappedBy: 'vehicle')]
    private Collection $vehicleMaintenances;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'vehicle')]
    private Collection $notifications;

    public function __construct()
    {
        $this->vehicleMaintenances = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlateNumber(): ?string
    {
        return $this->plateNumber;
    }

    public function setPlateNumber(string $plateNumber): static
    {
        $this->plateNumber = $plateNumber;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(string $vin): static
    {
        $this->vin = $vin;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(?int $mileage): static
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function getFuelType(): ?string
    {
        return $this->fuelType;
    }

    public function setFuelType(?string $fuelType): static
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    public function getBrandLogo(): ?string
    {
        return $this->brandLogo;
    }

    public function setBrandLogo(?string $brandLogo): static
    {
        $this->brandLogo = $brandLogo;

        return $this;
    }

    public function getModelPhoto(): ?string
    {
        return $this->modelPhoto;
    }

    public function setModelPhoto(?string $modelPhoto): static
    {
        $this->modelPhoto = $modelPhoto;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $vehicleMaintenance->setVehicle($this);
        }

        return $this;
    }

    public function removeVehicleMaintenance(VehicleMaintenance $vehicleMaintenance): static
    {
        if ($this->vehicleMaintenances->removeElement($vehicleMaintenance)) {
            // set the owning side to null (unless already changed)
            if ($vehicleMaintenance->getVehicle() === $this) {
                $vehicleMaintenance->setVehicle(null);
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
            $notification->setVehicle($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getVehicle() === $this) {
                $notification->setVehicle(null);
            }
        }

        return $this;
    }
}
