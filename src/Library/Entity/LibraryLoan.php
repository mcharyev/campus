<?php

namespace App\Library\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use \App\Library\Entity\LibraryUnit;
use \App\Library\Entity\LibraryItem;

/**
 * @ORM\Entity(repositoryClass="App\Library\Repository\LibraryLoanRepository")
 */
class LibraryLoan
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="libraryLoans")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Library\Entity\LibraryUnit", inversedBy="libraryLoans")
     * @ORM\JoinColumn(nullable=false)
     */
    private $libraryUnit;

    /**
     * @ORM\Column(type="datetime")
     */
    private $loanDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $returnDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Library\Entity\LibraryItem", inversedBy="libraryLoans")
     * @ORM\JoinColumn(nullable=false)
     */
    private $libraryItem;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getLoanDate(): ?\DateTimeInterface
    {
        return $this->loanDate;
    }

    public function setLoanDate(\DateTimeInterface $loanDate): self
    {
        $this->loanDate = $loanDate;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(\DateTimeInterface $returnDate): self
    {
        $this->returnDate = $returnDate;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getLibraryItem(): ?LibraryItem
    {
        return $this->libraryItem;
    }

    public function setLibraryItem(?LibraryItem $libraryItem): self
    {
        $this->libraryItem = $libraryItem;

        return $this;
    }
}
