<?php

namespace App\Hr\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Hr\Repository\MovementRepository")
 */
class Movement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $movementType;

    /**
     * @ORM\Column(type="integer")
     */
    private $employeeNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $movementDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovementType(): ?int
    {
        return $this->movementType;
    }

    public function setMovementType(int $movementType): self
    {
        $this->movementType = $movementType;

        return $this;
    }

    public function getEmployeeNumber(): ?int
    {
        return $this->employeeNumber;
    }

    public function setEmployeeNumber(int $employeeNumber): self
    {
        $this->employeeNumber = $employeeNumber;

        return $this;
    }

    public function getMovementDate(): ?\DateTimeInterface
    {
        return $this->movementDate;
    }

    public function setMovementDate(\DateTimeInterface $movementDate): self
    {
        $this->movementDate = $movementDate;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(\DateTimeInterface $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }
}
