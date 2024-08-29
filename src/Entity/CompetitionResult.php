<?php

namespace App\Entity;

use App\Interfaces\ReportableInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionResultRepository")
 */
class CompetitionResult implements ReportableInterface {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition", inversedBy="competitionResults")
     * @ORM\JoinColumn(nullable=false)
     */
    private $competition;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $person;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $resultType;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $resultLevel;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $awardType;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $viewOrder;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $tags;

    public function getId(): ?int {
        return $this->id;
    }

    public function getCompetition(): ?Competition {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self {
        $this->competition = $competition;

        return $this;
    }

    public function getPerson(): ?string {
        return $this->person;
    }

    public function setPerson(string $person): self {
        $this->person = $person;

        return $this;
    }

    public function getResultType(): ?int {
        return $this->resultType;
    }

    public function setResultType(int $resultType): self {
        $this->resultType = $resultType;

        return $this;
    }

    public function getResultLevel(): ?int {
        return $this->resultLevel;
    }

    public function setResultLevel(int $resultLevel): self {
        $this->resultLevel = $resultLevel;

        return $this;
    }

    public function getAwardType(): ?int {
        return $this->awardType;
    }

    public function setAwardType(int $awardType): self {
        $this->awardType = $awardType;

        return $this;
    }

    public function getData(): ?array {
        return $this->data;
    }

    public function setData(?array $data): self {
        $this->data = $data;

        return $this;
    }

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = $value;
        } else {
            $this->data += array($column => $value);
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

    public function isTagged(?string $tag): bool {
        return strpos($this->getTags(), $tag) !== false;
    }

}
