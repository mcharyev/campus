<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\TaughtCourse;
use App\Entity\Teacher;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScheduleItemRepository")
 */
class ScheduleItem {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TaughtCourse", inversedBy="scheduleItems")
     * @ORM\OrderBy({"day" = "ASC"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $taughtCourse;

    /**
     * @ORM\Column(type="smallint")
     */
    private $day;

    /**
     * @ORM\Column(type="smallint")
     */
    private $session;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schedule")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schedule;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $rooms;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $studentGroups;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $data;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="scheduleItems")
     * @ORM\OrderBy({"lastname" = "ASC"})
     */
    private $teacher;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ScheduleChange", mappedBy="scheduleItem")
     */
    private $scheduleChanges;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $period;

    public function __construct() {
        $this->scheduleChanges = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getTaughtCourse(): ?TaughtCourse {
        return $this->taughtCourse;
    }

    public function setTaughtCourse(?TaughtCourse $taughtCourse): self {
        $this->taughtCourse = $taughtCourse;

        return $this;
    }

    public function getDay(): ?int {
        return $this->day;
    }

    public function setDay(int $day): self {
        $this->day = $day;

        return $this;
    }

    public function getSession(): ?int {
        return $this->session;
    }

    public function setSession(int $session): self {
        $this->session = $session;

        return $this;
    }

    public function getSchedule(): ?Schedule {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): self {
        $this->schedule = $schedule;

        return $this;
    }

    public function getRooms(): ?string {
        return $this->rooms;
    }

    public function setRooms(?string $rooms): self {
        $this->rooms = $rooms;

        return $this;
    }

    public function getStudentGroups(): ?string {
        return $this->studentGroups;
    }

    public function setStudentGroups(string $studentGroups): self {
        $this->studentGroups = $studentGroups;

        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data): self {
        $this->data = $data;

        return $this;
    }

    public function getDataField(?string $fieldName): ?string {
        if (array_key_exists($fieldName, $this->data)) {
            if (json_decode($this->data[$fieldName]) == null || json_decode($this->data[$fieldName]) == 'null') {
                if ($this->data[$fieldName] == 'null') {
                    return "";
                } else {
                    return json_decode($this->data[$fieldName]);
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

    public function getTeacherName(): ?string {
        return $this->data['teacher'];
    }

    public function getNoteCourseName(): ?string {
        if (array_key_exists('course', $this->data)) {
            return $this->data['course'];
        } else {
            return '';
        }
    }

    public function setNoteCourseName(?string $courseName): self {
        $this->data['course'] = $courseName;
        return $this;
    }

    public function getCourseTeacherName(): ?string {
        return $this->taughtCourse->getTeacher()->getFullname();
    }

    public function getClassType(): ?ClassType {
        return $this->classType;
    }

    public function setClassType(?ClassType $classType): self {
        $this->classType = $classType;

        return $this;
    }

    public function getTeacher(): ?Teacher {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * @return Collection|ScheduleChange[]
     */
    public function getScheduleChanges(): Collection {
        return $this->scheduleChanges;
    }

    public function addScheduleChange(ScheduleChange $scheduleChange): self {
        if (!$this->scheduleChanges->contains($scheduleChange)) {
            $this->scheduleChanges[] = $scheduleChange;
            $scheduleChange->setScheduleItem($this);
        }

        return $this;
    }

    public function removeScheduleChange(ScheduleChange $scheduleChange): self {
        if ($this->scheduleChanges->contains($scheduleChange)) {
            $this->scheduleChanges->removeElement($scheduleChange);
            // set the owning side to null (unless already changed)
            if ($scheduleChange->getScheduleItem() === $this) {
                $scheduleChange->setScheduleItem(null);
            }
        }

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

    public function getPeriod(): ?int {
        return $this->period;
    }

    public function setPeriod(int $period): self {
        $this->period = $period;

        return $this;
    }

    public function isSeminarCombined() {
        return ($this->getDataField('seminar_combined') == 1);
    }
    
    public function getDepartmentCode(): ?int {
        return $this->getTaughtCourse()->getDepartment()->getSystemId();
    }
    
    public function getRoomsName(): ?string {
        return $this->getDataField('room');
    }
    
    public function getGroupsName(): ?string {
        return $this->getDataField('group_name');
    }

}
