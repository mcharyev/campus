<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\WorkloadEnum;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeacherWorkSetRepository")
 */
class TeacherWorkSet {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="teacherWorkSets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teacher;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $year;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $workload;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $viewOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="teacherWorkSets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $status;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherWorkItem", mappedBy="teacherWorkSet")
     * @ORM\OrderBy({"viewOrder" = "ASC"})
     */
    private $teacherWorkItems;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    public function __construct() {
        $this->teacherWorkItems = new ArrayCollection();
    }

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

    public function getYear(): ?int {
        return $this->year;
    }

    public function setYear(int $year): self {
        $this->year = $year;

        return $this;
    }

    public function getWorkload(): ?int {
        return $this->workload;
    }

    public function setWorkload(int $workload): self {
        $this->workload = $workload;

        return $this;
    }

    public function getViewOrder(): ?int {
        return $this->viewOrder;
    }

    public function setViewOrder(int $viewOrder): self {
        $this->viewOrder = $viewOrder;

        return $this;
    }

    public function getDepartment(): ?Department {
        return $this->department;
    }

    public function setDepartment(?Department $department): self {
        $this->department = $department;

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
            $this->data[$column] = json_encode($value);
        } else {
            $this->data += array($column => json_encode($value));
        }
        return $this;
    }

    /**
     * @return Collection|TeacherWorkItem[]
     */
    public function getTeacherWorkItems(): Collection {
        return $this->teacherWorkItems;
    }

    public function addTeacherWorkItem(TeacherWorkItem $teacherWorkItem): self {
        if (!$this->teacherWorkItems->contains($teacherWorkItem)) {
            $this->teacherWorkItems[] = $teacherWorkItem;
            $teacherWorkItem->setTeacherWorkSet($this);
        }

        return $this;
    }

    public function removeTeacherWorkItem(TeacherWorkItem $teacherWorkItem): self {
        if ($this->teacherWorkItems->contains($teacherWorkItem)) {
            $this->teacherWorkItems->removeElement($teacherWorkItem);
            // set the owning side to null (unless already changed)
            if ($teacherWorkItem->getTeacherWorkSet() === $this) {
                $teacherWorkItem->setTeacherWorkSet(null);
            }
        }

        return $this;
    }

    public function getTitle(): string {
        if (!empty($this->getDataField('note'))) {
            return $this->getTeacher()->getFullname() . " - " . WorkloadEnum::getTypeName($this->getWorkload()) . " - (" . $this->getDataField('note') . ")";
        } else {
            return $this->getTeacher()->getFullname() . " - " . WorkloadEnum::getTypeName($this->getWorkload());
        }
    }

    public function getShortTitle(): string {
        if (!empty($this->getDataField('note'))) {
            return WorkloadEnum::getTypeName($this->getWorkload()) . " - (" . $this->getDataField('note') . ")";
        } else {
            return WorkloadEnum::getTypeName($this->getWorkload());
        }
    }

    public function getNote(): ?string {
//        if ($this->getDataField('note') != "null")
        return $this->getDataField('note');
//        else
//            return "";
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

    public function getReplacedSessionSum(int $semester): int {
        $totalHours = 0;
        $scheduleChanges = $this->getScheduleChanges($semester);
        foreach ($scheduleChanges as $scheduleChange) {
            //echo $scheduleChange->getId() . " - " . $scheduleChange->getClassType()->getHours() . "<br>";
            $totalHours += $scheduleChange->getClassType()->getHours();
        }
        return $totalHours;
    }

    public function getReplacedSessionSum1(): int {
        return $this->getReplacedSessionSum(1);
    }

    public function getReplacedSessionSum2(): int {
        return $this->getReplacedSessionSum(2);
    }

    public function getReplacedSessionSum3(): int {
        return $this->getReplacedSessionSum(3);
    }

    public function getScheduleChanges(int $semester): array {
        $workitems = $this->getTeacherWorkItems();
        $scheduleChangesTotal = [];
        foreach ($workitems as $workitem) {
            $taughtCourse = $workitem->getTaughtCourse();
            if ($taughtCourse) {
                if ($taughtCourse->getSemester() == $semester) {
                    $scheduleItems = $taughtCourse->getScheduleItems();
                    foreach ($scheduleItems as $scheduleItem) {
                        $scheduleChanges = $scheduleItem->getScheduleChanges();
                        foreach ($scheduleChanges as $scheduleChange) {
                            if (!in_array($scheduleChange, $scheduleChangesTotal)) {
                                $scheduleChangesTotal[] = $scheduleChange;
                            }
                        }
                    }
                }
            }
        }
        return $scheduleChangesTotal;
    }

}
