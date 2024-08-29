<?php

namespace App\Library\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use \App\Library\Entity\LibraryUnit;

/**
 * @ORM\Entity(repositoryClass="App\Library\Repository\LibraryAccessRepository")
 */
class LibraryAccess
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="libraryAccesses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Library\Entity\LibraryUnit", inversedBy="libraryAccesses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $libraryUnit;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $type;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $status;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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
}
