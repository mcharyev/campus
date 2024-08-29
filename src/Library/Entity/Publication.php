<?php

namespace App\Library\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

/**
 * @ORM\Entity(repositoryClass="App\Library\Repository\PublicationRepository")
 */
class Publication {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $publication;

    /**
     * @ORM\Column(type="date")
     */
    private $datePublished;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $recorder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file;

    public function getId(): ?int {
        return $this->id;
    }

    public function getAuthor(): ?User {
        return $this->author;
    }

    public function setAuthor(?User $author): self {
        $this->author = $author;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): self {
        $this->title = $title;

        return $this;
    }

    public function getPublication(): ?string {
        return $this->publication;
    }

    public function setPublication(?string $publication): self {
        $this->publication = $publication;

        return $this;
    }

    public function getDatePublished(): ?\DateTimeInterface {
        return $this->datePublished;
    }

    public function setDatePublished(\DateTimeInterface $datePublished): self {
        $this->datePublished = $datePublished;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getRecorder(): ?User {
        return $this->recorder;
    }

    public function setRecorder(?User $recorder): self {
        $this->recorder = $recorder;

        return $this;
    }

    public function getFile(): ?string {
        return $this->file;
    }

    public function setFile(?string $file): self {
        $this->file = $file;

        return $this;
    }

}
