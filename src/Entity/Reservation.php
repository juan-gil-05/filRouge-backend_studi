<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[Groups(["Reservation:read", "User:read"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["Reservation:read", "User:read"])]
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $guestNumber = null;

    #[Groups(["Reservation:read", "User:read"])]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[Groups(["Reservation:read", "User:read"])]
    #[ORM\Column(length: 10)]
    private ?string $hour = null;

    #[Groups(["Reservation:read"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $allergy = null;

    #[Groups(["Reservation:read"])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(["Reservation:read"])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(["Reservation:read"])]
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?restaurant $restaurant = null;

    #[Groups(["Reservation:read"])]
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGuestNumber(): ?int
    {
        return $this->guestNumber;
    }

    public function setGuestNumber(int $guestNumber): static
    {
        $this->guestNumber = $guestNumber;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHour(): ?string
    {
        return $this->hour;
    }

    public function setHour(string $hour): static
    {
        $this->hour = $hour;

        return $this;
    }

    public function getAllergy(): ?string
    {
        return $this->allergy;
    }

    public function setAllergy(?string $allergy): static
    {
        $this->allergy = $allergy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRestaurant(): ?restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }
}
