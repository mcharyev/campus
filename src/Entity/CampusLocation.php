<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CampusLocationRepository")
 */
class CampusLocation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $systemId;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CampusBuilding", mappedBy="campusLocation")
     */
    private $campusBuildings;

    public function __construct()
    {
        $this->campusBuildings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSystemId(): ?int
    {
        return $this->systemId;
    }

    public function setSystemId(int $systemId): self
    {
        $this->systemId = $systemId;

        return $this;
    }

    public function getLetterCode(): ?string
    {
        return $this->letterCode;
    }

    public function setLetterCode(?string $letterCode): self
    {
        $this->letterCode = $letterCode;

        return $this;
    }

    public function getNameEnglish(): ?string
    {
        return $this->nameEnglish;
    }

    public function setNameEnglish(?string $nameEnglish): self
    {
        $this->nameEnglish = $nameEnglish;

        return $this;
    }

    public function getNameTurkmen(): ?string
    {
        return $this->nameTurkmen;
    }

    public function setNameTurkmen(?string $nameTurkmen): self
    {
        $this->nameTurkmen = $nameTurkmen;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection|CampusBuilding[]
     */
    public function getCampusBuildings(): Collection
    {
        return $this->campusBuildings;
    }

    public function addCampusBuilding(CampusBuilding $campusBuilding): self
    {
        if (!$this->campusBuildings->contains($campusBuilding)) {
            $this->campusBuildings[] = $campusBuilding;
            $campusBuilding->setCampusLocation($this);
        }

        return $this;
    }

    public function removeCampusBuilding(CampusBuilding $campusBuilding): self
    {
        if ($this->campusBuildings->contains($campusBuilding)) {
            $this->campusBuildings->removeElement($campusBuilding);
            // set the owning side to null (unless already changed)
            if ($campusBuilding->getCampusLocation() === $this) {
                $campusBuilding->setCampusLocation(null);
            }
        }

        return $this;
    }
}
