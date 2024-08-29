<?php

namespace App\Library\Entity;

use App\Entity\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Library\Entity\LibraryUnit;

/**
 * @ORM\Entity(repositoryClass="App\Library\Repository\LibraryItemRepository")
 */
class LibraryItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $mainTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $secondaryTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $writerNumber;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $year;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $edition;

    /**
     * @ORM\Column(type="string", length=20, options={"default": 1})
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": "1"})
     */
    private $volume;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $copyNumber;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $callNumber;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $callNumberOriginal;

    /**
     * @ORM\Column(type="string", length=13, nullable=true)
     */
    private $isbn;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $uok;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $publisher;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $place;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     */
    private $language;

    /**
     * @ORM\Column(type="float", options={"default": 0})
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $invoice;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Library\Entity\LibraryUnit", inversedBy="libraryItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $libraryUnit;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\OneToMany(targetEntity="App\Library\Entity\LibraryLoan", mappedBy="libraryItem")
     */
    private $libraryLoans;

    public function __construct()
    {
        $this->libraryLoans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMainTitle(): ?string
    {
        return $this->mainTitle;
    }

    public function setMainTitle(string $mainTitle): self
    {
        $this->mainTitle = $mainTitle;

        return $this;
    }

    public function getSecondaryTitle(): ?string
    {
        return $this->secondaryTitle;
    }

    public function setSecondaryTitle(string $secondaryTitle): self
    {
        $this->secondaryTitle = $secondaryTitle;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getWriterNumber(): ?string
    {
        return $this->writerNumber;
    }

    public function setWriterNumber(?string $writerNumber): self
    {
        $this->writerNumber = $writerNumber;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getEdition(): ?int
    {
        return $this->edition;
    }

    public function setEdition(int $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getVolume(): ?string
    {
        return $this->volume;
    }

    public function setVolume(?string $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getCopyNumber(): ?int
    {
        return $this->copyNumber;
    }

    public function setCopyNumber(?int $copyNumber): self
    {
        $this->copyNumber = $copyNumber;

        return $this;
    }

    public function getCallNumber(): ?string
    {
        return $this->callNumber;
    }

    public function setCallNumber(string $callNumber): self
    {
        $this->callNumber = $callNumber;

        return $this;
    }

    public function getCallNumberOriginal(): ?string
    {
        return $this->callNumberOriginal;
    }

    public function setCallNumberOriginal(string $callNumberOriginal): self
    {
        $this->callNumberOriginal = $callNumberOriginal;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getUok(): ?string
    {
        return $this->uok;
    }

    public function setUok(?string $uok): self
    {
        $this->uok = $uok;

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

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getInvoice(): ?string
    {
        return $this->invoice;
    }

    public function setInvoice(?string $invoice): self
    {
        $this->invoice = $invoice;

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

    public function getLibraryUnit(): ?LibraryUnit
    {
        return $this->libraryUnit;
    }

    public function setLibraryUnit(?LibraryUnit $libraryUnit): self
    {
        $this->libraryUnit = $libraryUnit;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

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
            $libraryLoan->setLibraryItem($this);
        }

        return $this;
    }

    public function removeLibraryLoan(LibraryLoan $libraryLoan): self
    {
        if ($this->libraryLoans->contains($libraryLoan)) {
            $this->libraryLoans->removeElement($libraryLoan);
            // set the owning side to null (unless already changed)
            if ($libraryLoan->getLibraryItem() === $this) {
                $libraryLoan->setLibraryItem(null);
            }
        }

        return $this;
    }
}
