<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HostelRepository")
 */
class Hostel
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
     * @ORM\Column(type="string", length=50)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\HostelRoom", mappedBy="hostel")
     */
    private $hostelRooms;

    public function __construct()
    {
        $this->hostelRooms = new ArrayCollection();
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

    public function setLetterCode(string $letterCode): self
    {
        $this->letterCode = $letterCode;

        return $this;
    }

    public function getNameEnglish(): ?string
    {
        return $this->nameEnglish;
    }

    public function setNameEnglish(string $nameEnglish): self
    {
        $this->nameEnglish = $nameEnglish;

        return $this;
    }

    public function getNameTurkmen(): ?string
    {
        return $this->nameTurkmen;
    }

    public function setNameTurkmen(string $nameTurkmen): self
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

    /**
     * @return Collection|HostelRoom[]
     */
    public function getHostelRooms(): Collection
    {
        return $this->hostelRooms;
    }

    public function addHostelRoom(HostelRoom $hostelRoom): self
    {
        if (!$this->hostelRooms->contains($hostelRoom)) {
            $this->hostelRooms[] = $hostelRoom;
            $hostelRoom->setHostel($this);
        }

        return $this;
    }

    public function removeHostelRoom(HostelRoom $hostelRoom): self
    {
        if ($this->hostelRooms->contains($hostelRoom)) {
            $this->hostelRooms->removeElement($hostelRoom);
            // set the owning side to null (unless already changed)
            if ($hostelRoom->getHostel() === $this) {
                $hostelRoom->setHostel(null);
            }
        }

        return $this;
    }
}
