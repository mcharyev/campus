<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeacherRepository")
 */
class Teacher {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $patronym;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $type;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $degree;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $systemId;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group", mappedBy="advisor")
     * @ORM\OrderBy({"lastname" = "ASC"})
     */
    private $advisedGroups;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="teachers")
     * @ORM\OrderBy({"lastname" = "ASC"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaughtCourse", mappedBy="teacher")
     */
    private $taughtCourses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentAbsence", mappedBy="author")
     */
    private $studentAbsences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ScheduleItem", mappedBy="teacher")
     */
    private $scheduleItems;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherAttendance", mappedBy="teacher")
     */
    private $teacherAttendances;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherWorkItem", mappedBy="teacher")
     * @ORM\OrderBy({"viewOrder" = "ASC"})
     */
    private $teacherWorkItems;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReportedWork", mappedBy="teacher")
     * @ORM\OrderBy({"lastname" = "ASC"})
     */
    private $reportedWorks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherWorkSet", mappedBy="teacher")
     */
    private $teacherWorkSets;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\HostelRoom", mappedBy="instructor")
     */
    private $hostelRooms;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $scheduleName;

    public function __construct() {
        $this->advisedGroups = new ArrayCollection();
        $this->taughtCourses = new ArrayCollection();
        $this->studentAbsences = new ArrayCollection();
        $this->scheduleItems = new ArrayCollection();
        $this->teacherAttendances = new ArrayCollection();
        $this->teacherWorkItems = new ArrayCollection();
        $this->reportedWorks = new ArrayCollection();
        $this->teacherWorkSets = new ArrayCollection();
        $this->hostelRooms = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getLevel(): ?int {
        return $this->level;
    }

    public function setLevel(?int $level): self {
        $this->level = $level;

        return $this;
    }

    public function getFirstname(): ?string {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPatronym(): ?string {
        return $this->patronym;
    }

    public function setPatronym(?string $patronym): self {
        $this->patronym = $patronym;

        return $this;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(?int $type): self {
        $this->type = $type;

        return $this;
    }

    public function getFullname(): ?string {
        return $this->lastname . " " . $this->firstname;
    }

    public function getThreeNames(): ?string {
        return $this->lastname . " " . $this->firstname . " " . $this->patronym;
    }

    public function getShortFullname(): ?string {
        return mb_substr($this->firstname, 0, 1) . "." . $this->lastname;
    }

    public function getInitials(): ?string {
        return mb_substr($this->firstname, 0, 1) . "." . mb_substr($this->lastname, 0, 1);
    }

    public function getDegree(): ?int {
        return $this->degree;
    }

    public function setDegree(?int $degree): self {
        $this->degree = $degree;

        return $this;
    }

    public function getSystemId(): ?int {
        return $this->systemId;
    }

    public function setSystemId(?int $systemId): self {
        $this->systemId = $systemId;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getAdvisedGroups(): Collection {
        return $this->advisedGroups;
    }

    public function addAdvisedGroup(Group $advisedGroup): self {
        if (!$this->advisedGroups->contains($advisedGroup)) {
            $this->advisedGroups[] = $advisedGroup;
            $advisedGroup->setAdvisor($this);
        }

        return $this;
    }

    public function removeAdvisedGroup(Group $advisedGroup): self {
        if ($this->advisedGroups->contains($advisedGroup)) {
            $this->advisedGroups->removeElement($advisedGroup);
            // set the owning side to null (unless already changed)
            if ($advisedGroup->getAdvisor() === $this) {
                $advisedGroup->setAdvisor(null);
            }
        }

        return $this;
    }

    public function getDepartment(): ?Department {
        return $this->department;
    }

    public function setDepartment(?Department $department): self {
        $this->department = $department;

        return $this;
    }

    public function getFaculty(): ?Faculty {
        return $this->getDepartment()->getFaculty();
    }

    public function isDean(Teacher $teacher) {
        $deans = $this->getFaculty()->getAllDeans();
        if (in_array($teacher, $deans)) {
            return true;
        } else {
            return false;
        }
    }

    public function isDepartmentHead(Teacher $teacher) {
        return ($teacher == $this->getDepartment()->getDepartmentHead());
    }

    /**
     * @return Collection|TaughtCourse[]
     */
    public function getTaughtCourses(): Collection {
        return $this->taughtCourses;
    }

    public function addTaughtCourse(TaughtCourse $taughtCourse): self {
        if (!$this->taughtCourses->contains($taughtCourse)) {
            $this->taughtCourses[] = $taughtCourse;
            $taughtCourse->setTeacher($this);
        }

        return $this;
    }

    public function removeTaughtCourse(TaughtCourse $taughtCourse): self {
        if ($this->taughtCourses->contains($taughtCourse)) {
            $this->taughtCourses->removeElement($taughtCourse);
            // set the owning side to null (unless already changed)
            if ($taughtCourse->getTeacher() === $this) {
                $taughtCourse->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getStudentAbsences(): Collection {
        return $this->studentAbsences;
    }

    public function addStudentAbsence(StudentAbsence $studentAbsence): self {
        if (!$this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences[] = $studentAbsence;
            $studentAbsence->setAuthor($this);
        }

        return $this;
    }

    public function removeStudentAbsence(StudentAbsence $studentAbsence): self {
        if ($this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences->removeElement($studentAbsence);
            // set the owning side to null (unless already changed)
            if ($studentAbsence->getAuthor() === $this) {
                $studentAbsence->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ScheduleItem[]
     */
    public function getScheduleItems(): Collection {
        return $this->scheduleItems;
    }

    public function addScheduleItem(ScheduleItem $scheduleItem): self {
        if (!$this->scheduleItems->contains($scheduleItem)) {
            $this->scheduleItems[] = $scheduleItem;
            $scheduleItem->setTeacher($this);
        }

        return $this;
    }

    public function removeScheduleItem(ScheduleItem $scheduleItem): self {
        if ($this->scheduleItems->contains($scheduleItem)) {
            $this->scheduleItems->removeElement($scheduleItem);
            // set the owning side to null (unless already changed)
            if ($scheduleItem->getTeacher() === $this) {
                $scheduleItem->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TeacherAttendance[]
     */
    public function getTeacherAttendances(): Collection {
        return $this->teacherAttendances;
    }

    public function addTeacherAttendance(TeacherAttendance $teacherAttendance): self {
        if (!$this->teacherAttendances->contains($teacherAttendance)) {
            $this->teacherAttendances[] = $teacherAttendance;
            $teacherAttendance->setTeacher($this);
        }

        return $this;
    }

    public function removeTeacherAttendance(TeacherAttendance $teacherAttendance): self {
        if ($this->teacherAttendances->contains($teacherAttendance)) {
            $this->teacherAttendances->removeElement($teacherAttendance);
            // set the owning side to null (unless already changed)
            if ($teacherAttendance->getTeacher() === $this) {
                $teacherAttendance->setTeacher(null);
            }
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
            $teacherWorkItem->setTeacher($this);
        }

        return $this;
    }

    public function removeTeacherWorkItem(TeacherWorkItem $teacherWorkItem): self {
        if ($this->teacherWorkItems->contains($teacherWorkItem)) {
            $this->teacherWorkItems->removeElement($teacherWorkItem);
            // set the owning side to null (unless already changed)
            if ($teacherWorkItem->getTeacher() === $this) {
                $teacherWorkItem->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ReportedWork[]
     */
    public function getReportedWorks(): Collection {
        return $this->reportedWorks;
    }

    public function addReportedWork(ReportedWork $reportedWork): self {
        if (!$this->reportedWorks->contains($reportedWork)) {
            $this->reportedWorks[] = $reportedWork;
            $reportedWork->setTeacher($this);
        }

        return $this;
    }

    public function removeReportedWork(ReportedWork $reportedWork): self {
        if ($this->reportedWorks->contains($reportedWork)) {
            $this->reportedWorks->removeElement($reportedWork);
            // set the owning side to null (unless already changed)
            if ($reportedWork->getTeacher() === $this) {
                $reportedWork->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TeacherWorkSet[]
     */
    public function getTeacherWorkSets(): Collection
    {
        return $this->teacherWorkSets;
    }

    public function addTeacherWorkSet(TeacherWorkSet $teacherWorkSet): self
    {
        if (!$this->teacherWorkSets->contains($teacherWorkSet)) {
            $this->teacherWorkSets[] = $teacherWorkSet;
            $teacherWorkSet->setTeacher($this);
        }

        return $this;
    }

    public function removeTeacherWorkSet(TeacherWorkSet $teacherWorkSet): self
    {
        if ($this->teacherWorkSets->contains($teacherWorkSet)) {
            $this->teacherWorkSets->removeElement($teacherWorkSet);
            // set the owning side to null (unless already changed)
            if ($teacherWorkSet->getTeacher() === $this) {
                $teacherWorkSet->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|HostelRoom[]
     */
    public function getHostelRooms(): Collection
    {
        return $this->hostelRooms;
    }

    public function addHostelRoom(HostelRoom $hostelRoom): self
    {
        if (!$this->hostelRooms->contains($hostelRoom)) {
            $this->hostelRooms[] = $hostelRoom;
            $hostelRoom->setInstructor($this);
        }

        return $this;
    }

    public function removeHostelRoom(HostelRoom $hostelRoom): self
    {
        if ($this->hostelRooms->contains($hostelRoom)) {
            $this->hostelRooms->removeElement($hostelRoom);
            // set the owning side to null (unless already changed)
            if ($hostelRoom->getInstructor() === $this) {
                $hostelRoom->setInstructor(null);
            }
        }

        return $this;
    }

    public function getScheduleName(): ?string
    {
        return $this->scheduleName;
    }

    public function setScheduleName(?string $scheduleName): self
    {
        $this->scheduleName = $scheduleName;

        return $this;
    }

}
