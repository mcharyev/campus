<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FreedayRepository")
 */
class Freeday
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $type;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $newDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $session;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $newSession;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getNewDate(): ?\DateTimeInterface
    {
        return $this->newDate;
    }

    public function setNewDate(\DateTimeInterface $newDate): self
    {
        $this->newDate = $newDate;

        return $this;
    }

    public function getSession(): ?int
    {
        return $this->session;
    }

    public function setSession(?int $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getNewSession(): ?int
    {
        return $this->newSession;
    }

    public function setNewSession(?int $newSession): self
    {
        $this->newSession = $newSession;

        return $this;
    }
}
