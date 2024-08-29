<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScheduleChangeRepository")
 */
class ScheduleChange {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="smallint")
     */
    private $session;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classType;

    /**
     * @ORM\Column(type="datetime")
     */
    private $newDate;

    /**
     * @ORM\Column(type="smallint")
     */
    private $newSession;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher")
     * @ORM\JoinColumn(nullable=false)
     */
    private $newTeacher;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Classroom", inversedBy="scheduleChanges")
     */
    private $classroom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ScheduleItem", inversedBy="scheduleChanges")
     * @ORM\JoinColumn(nullable=false)
     */
    private $scheduleItem;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $status;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $year;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $semester;

    public function getId(): ?int {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self {
        $this->date = $date;

        return $this;
    }

    public function getSession(): ?int {
        return $this->session;
    }

    public function setSession(int $session): self {
        $this->session = $session;

        return $this;
    }

    public function getClassType(): ?ClassType {
        return $this->classType;
    }

    public function setClassType(?ClassType $classType): self {
        $this->classType = $classType;

        return $this;
    }

    public function getNewDate(): ?\DateTimeInterface {
        return $this->newDate;
    }

    public function setNewDate(\DateTimeInterface $newDate): self {
        $this->newDate = $newDate;

        return $this;
    }

    public function getNewSession(): ?int {
        return $this->newSession;
    }

    public function setNewSession(int $newSession): self {
        $this->newSession = $newSession;

        return $this;
    }

    public function getNewTeacher(): ?Teacher {
        return $this->newTeacher;
    }

    public function setNewTeacher(?Teacher $newTeacher): self {
        $this->newTeacher = $newTeacher;

        return $this;
    }

    public function getData(): ?array {
        return $this->data;
    }

    public function setData(?array $data): self {
        $this->data = $data;

        return $this;
    }

    public function getAuthor(): ?User {
        return $this->author;
    }

    public function setAuthor(?User $author): self {
        $this->author = $author;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

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

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = $value;
        } else {
            $this->data += array($column => $value);
        }
        return $this;
    }

    public function getClassroom(): ?Classroom {
        return $this->classroom;
    }

    public function setClassroom(?Classroom $classroom): self {
        $this->classroom = $classroom;

        return $this;
    }

    public function getScheduleItem(): ?ScheduleItem {
        return $this->scheduleItem;
    }

    public function setScheduleItem(?ScheduleItem $scheduleItem): self {
        $this->scheduleItem = $scheduleItem;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getYear(): ?int {
        return $this->year;
    }

    public function setYear(int $year): self {
        $this->year = $year;

        return $this;
    }

    public function getSemester(): ?int {
        return $this->semester;
    }

    public function setSemester(int $semester): self {
        $this->semester = $semester;

        return $this;
    }

}
