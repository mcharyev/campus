<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\WorkColumnEnum;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReportedWorkRepository")
 */
class ReportedWork {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="reportedWorks")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\OrderBy({"lastname" = "ASC"})
     */
    private $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $type;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeacherWorkItem", inversedBy="reportedWorks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workitem;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $status;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    public function getId(): ?int {
        return $this->id;
    }

    public function getTeacher(): ?Teacher {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self {
        $this->teacher = $teacher;

        return $this;
    }

    public function getAuthor(): ?User {
        return $this->author;
    }

    public function setAuthor(?User $author): self {
        $this->author = $author;

        return $this;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(int $type): self {
        $this->type = $type;

        return $this;
    }

    public function getTypeName(): ?string {
        return WorkColumnEnum::getTypeName($this->type);
    }

    public function getAmount(): ?int {
        return $this->amount;
    }

    public function setAmount(int $amount): self {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self {
        $this->date = $date;

        return $this;
    }

    public function getWorkitem(): ?TeacherWorkItem {
        return $this->workitem;
    }

    public function setWorkitem(?TeacherWorkItem $workitem): self {
        $this->workitem = $workitem;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

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

    public function getDataField(?string $fieldName): ?string {
        if (array_key_exists($fieldName, $this->data)) {
            if (json_decode($this->data[$fieldName]) == null || json_decode($this->data[$fieldName]) == 'null') {
                if ($this->data[$fieldName] == 'null') {
                    return "";
                } else {
                    return $this->data[$fieldName];
                }
            } else {
                return json_decode($this->data[$fieldName]);
            }
        } else {
            return "";
        }
    }

    public function getNote(): ?string {
        return $this->getDataField("note");
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

}
