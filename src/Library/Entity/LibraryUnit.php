<?php

namespace App\Library\Entity;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Library\Repository\LibraryUnitRepository")
 */
class LibraryUnit
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
    private $systemId;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Library\Entity\LibraryItem", mappedBy="libraryUnit")
     */
    private $libraryItems;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="libraryUnits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Library\Entity\LibraryLoan", mappedBy="libraryUnit")
     */
    private $libraryLoans;

    /**
     * @ORM\OneToMany(targetEntity="App\Library\Entity\LibraryAccess", mappedBy="libraryUnit")
     */
    private $libraryAccesses;

    public function __construct()
    {
        $this->libraryItems = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->libraryAccesses = new ArrayCollection();
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

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|LibraryItem[]
     */
    public function getLibraryItems(): Collection
    {
        return $this->libraryItems;
    }

    public function addLibraryItem(LibraryItem $libraryItem): self
    {
        if (!$this->libraryItems->contains($libraryItem)) {
            $this->libraryItems[] = $libraryItem;
            $libraryItem->setLibraryUnit($this);
        }

        return $this;
    }

    public function removeLibraryItem(LibraryItem $libraryItem): self
    {
        if ($this->libraryItems->contains($libraryItem)) {
            $this->libraryItems->removeElement($libraryItem);
            // set the owning side to null (unless already changed)
            if ($libraryItem->getLibraryUnit() === $this) {
                $libraryItem->setLibraryUnit(null);
            }
        }

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Collection|LibraryLoan[]
     */
    public function getLibraryLoans(): Collection
    {
        return $this->libraryLoans;
    }

    public function addLibraryLoan(LibraryLoan $libraryLoan): self
    {
        if (!$this->libraryLoans->contains($libraryLoan)) {
            $this->libraryLoans[] = $libraryLoan;
            $libraryLoan->setUnit($this);
        }

        return $this;
    }

    public function removeLibraryLoan(LibraryLoan $libraryLoan): self
    {
        if ($this->libraryLoans->contains($libraryLoan)) {
            $this->libraryLoans->removeElement($libraryLoan);
            // set the owning side to null (unless already changed)
            if ($libraryLoan->getUnit() === $this) {
                $libraryLoan->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LibraryAccess[]
     */
    public function getLibraryAccesses(): Collection
    {
        return $this->libraryAccesses;
    }

    public function addLibraryAccess(LibraryAccess $libraryAccess): self
    {
        if (!$this->libraryAccesses->contains($libraryAccess)) {
            $this->libraryAccesses[] = $libraryAccess;
            $libraryAccess->setLibraryUnit($this);
        }

        return $this;
    }

    public function removeLibraryAccess(LibraryAccess $libraryAccess): self
    {
        if ($this->libraryAccesses->contains($libraryAccess)) {
            $this->libraryAccesses->removeElement($libraryAccess);
            // set the owning side to null (unless already changed)
            if ($libraryAccess->getLibraryUnit() === $this) {
                $libraryAccess->setLibraryUnit(null);
            }
        }

        return $this;
    }
}
