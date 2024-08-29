<?php

namespace App\Entity;

use App\Interfaces\ReportableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionRepository")
 */
class Competition implements ReportableInterface {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $scope;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $organizer;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionResult", mappedBy="competition")
     */
    private $competitionResults;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $viewOrder;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $tags;

    public function __construct() {
        $this->competitionResults = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getNameEnglish(): ?string {
        return $this->nameEnglish;
    }

    public function setNameEnglish(?string $nameEnglish): self {
        $this->nameEnglish = $nameEnglish;

        return $this;
    }

    public function getNameTurkmen(): ?string {
        return $this->nameTurkmen;
    }

    public function setNameTurkmen(?string $nameTurkmen): self {
        $this->nameTurkmen = $nameTurkmen;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self {
        $this->endDate = $endDate;

        return $this;
    }

    public function getScope(): ?int {
        return $this->scope;
    }

    public function setScope(int $scope): self {
        $this->scope = $scope;

        return $this;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(int $type): self {
        $this->type = $type;

        return $this;
    }

    public function getPlace(): ?string {
        return $this->place;
    }

    public function setPlace(?string $place): self {
        $this->place = $place;

        return $this;
    }

    public function getOrganizer(): ?string {
        return $this->organizer;
    }

    public function setOrganizer(?string $organizer): self {
        $this->organizer = $organizer;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|CompetitionResult[]
     */
    public function getCompetitionResults(): Collection {
        return $this->competitionResults;
    }

    public function addCompetitionResult(CompetitionResult $competitionResult): self {
        if (!$this->competitionResults->contains($competitionResult)) {
            $this->competitionResults[] = $competitionResult;
            $competitionResult->setCompetition($this);
        }

        return $this;
    }

    public function removeCompetitionResult(CompetitionResult $competitionResult): self {
        if ($this->competitionResults->contains($competitionResult)) {
            $this->competitionResults->removeElement($competitionResult);
            // set the owning side to null (unless already changed)
            if ($competitionResult->getCompetition() === $this) {
                $competitionResult->setCompetition(null);
            }
        }

        return $this;
    }

    public function getViewOrder(): ?int {
        return $this->viewOrder;
    }

    public function setViewOrder(int $viewOrder): self {
        $this->viewOrder = $viewOrder;

        return $this;
    }

    public function getTags(): ?string {
        return $this->tags;
    }

    public function setTags(?string $tags): self {
        $this->tags = $tags;

        return $this;
    }

    public function isTagged(string $tag): bool {
        return (strpos($this->getTags(), $tag) !== false);
    }

}
