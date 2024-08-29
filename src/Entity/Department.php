<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DepartmentRepository")
 */
class Department {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="string", length=10, options={"default": ""})
     */
    private $letterCode = "";

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $type = 0;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 1})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $systemId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudyProgram", mappedBy="department")
     * @ORM\OrderBy({"systemId" = "ASC", "approvalYear" = "DESC"})
     */
    private $studyPrograms;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Faculty", inversedBy="departments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $faculty;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher")
     * @ORM\OrderBy({"lastname" = "ASC"})
     */
    private $departmentHead;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Teacher", mappedBy="department")
     */
    private $teachers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProgramCourse", mappedBy="department")
     */
    private $programCourses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaughtCourse", mappedBy="department")
     */
    private $taughtCourses;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $facultyCode;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentAbsence", mappedBy="department")
     */
    private $studentAbsences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherAttendance", mappedBy="department")
     */
    private $teacherAttendances;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DepartmentWorkItem", mappedBy="department")
     * @ORM\OrderBy({"viewOrder" = "ASC"})
     */
    private $departmentWorkItems;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherWorkItem", mappedBy="department")
     */
    private $teacherWorkItems;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherWorkSet", mappedBy="department")
     * @ORM\OrderBy({"viewOrder" = "ASC"})
     */
    private $teacherWorkSets;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group", mappedBy="department")
     */
    private $groups;

    public function __construct() {
        $this->studyPrograms = new ArrayCollection();
        $this->teachers = new ArrayCollection();
        $this->programCourses = new ArrayCollection();
        $this->taughtCourses = new ArrayCollection();
        $this->studentAbsences = new ArrayCollection();
        $this->teacherAttendances = new ArrayCollection();
        $this->departmentWorkSets = new ArrayCollection();
        $this->departmentWorkItems = new ArrayCollection();
        $this->teacherWorkItems = new ArrayCollection();
        $this->teacherWorkSets = new ArrayCollection();
        $this->groups = new ArrayCollection();
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

    public function setNameTurkmen(string $nameTurkmen): self {
        $this->nameTurkmen = $nameTurkmen;

        return $this;
    }

    public function getLetterCode(): ?string {
        return $this->letterCode;
    }

    public function setLetterCode(string $letterCode): self {
        $this->letterCode = $letterCode;

        return $this;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(int $type): self {
        $this->type = $type;

        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data): self {
        $this->data = $data;

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

    public function getSystemId(): ?int {
        return $this->systemId;
    }

    public function setSystemId(?int $systemId): self {
        $this->systemId = $systemId;

        return $this;
    }

    /**
     * @return Collection|StudyProgram[]
     */
    public function getStudyPrograms(): Collection {
        return $this->studyPrograms;
    }

    public function addStudyProgram(StudyProgram $studyProgram): self {
        if (!$this->studyPrograms->contains($studyProgram)) {
            $this->studyPrograms[] = $studyProgram;
            $studyProgram->setDepartment($this);
        }

        return $this;
    }

    public function removeStudyProgram(StudyProgram $studyProgram): self {
        if ($this->studyPrograms->contains($studyProgram)) {
            $this->studyPrograms->removeElement($studyProgram);
            // set the owning side to null (unless already changed)
            if ($studyProgram->getDepartment() === $this) {
                $studyProgram->setDepartment(null);
            }
        }

        return $this;
    }

    public function getFaculty(): ?Faculty {
        return $this->faculty;
    }

    public function setFaculty(?Faculty $faculty): self {
        $this->faculty = $faculty;

        return $this;
    }

    public function getDepartmentHead(): ?Teacher {
        return $this->departmentHead;
    }

    public function setDepartmentHead(?Teacher $departmentHead): self {
        $this->departmentHead = $departmentHead;

        return $this;
    }

    /**
     * @return Collection|Teacher[]
     */
    public function getTeachers(): Collection {
        return $this->teachers;
    }

    public function addTeacher(Teacher $teacher): self {
        if (!$this->teachers->contains($teacher)) {
            $this->teachers[] = $teacher;
            $teacher->setDepartment($this);
        }

        return $this;
    }

    public function removeTeacher(Teacher $teacher): self {
        if ($this->teachers->contains($teacher)) {
            $this->teachers->removeElement($teacher);
            // set the owning side to null (unless already changed)
            if ($teacher->getDepartment() === $this) {
                $teacher->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProgramCourse[]
     */
    public function getProgramCourses(): Collection {
        return $this->programCourses;
    }

    public function addProgramCourse(ProgramCourse $programCourse): self {
        if (!$this->programCourses->contains($programCourse)) {
            $this->programCourses[] = $programCourse;
            $programCourse->setDepartment($this);
        }

        return $this;
    }

    public function removeProgramCourse(ProgramCourse $programCourse): self {
        if ($this->programCourses->contains($programCourse)) {
            $this->programCourses->removeElement($programCourse);
            // set the owning side to null (unless already changed)
            if ($programCourse->getDepartment() === $this) {
                $programCourse->setDepartment(null);
            }
        }

        return $this;
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
            $taughtCourse->setDepartment($this);
        }

        return $this;
    }

    public function removeTaughtCourse(TaughtCourse $taughtCourse): self {
        if ($this->taughtCourses->contains($taughtCourse)) {
            $this->taughtCourses->removeElement($taughtCourse);
            // set the owning side to null (unless already changed)
            if ($taughtCourse->getDepartment() === $this) {
                $taughtCourse->setDepartment(null);
            }
        }

        return $this;
    }

    public function getFacultyCode(): ?int {
        return $this->facultyCode;
    }

    public function setFacultyCode(int $facultyCode): self {
        $this->facultyCode = $facultyCode;

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
            $studentAbsence->setDepartment($this);
        }

        return $this;
    }

    public function removeStudentAbsence(StudentAbsence $studentAbsence): self {
        if ($this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences->removeElement($studentAbsence);
            // set the owning side to null (unless already changed)
            if ($studentAbsence->getDepartment() === $this) {
                $studentAbsence->setDepartment(null);
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
            $teacherAttendance->setDepartment($this);
        }

        return $this;
    }

    public function removeTeacherAttendance(TeacherAttendance $teacherAttendance): self {
        if ($this->teacherAttendances->contains($teacherAttendance)) {
            $this->teacherAttendances->removeElement($teacherAttendance);
            // set the owning side to null (unless already changed)
            if ($teacherAttendance->getDepartment() === $this) {
                $teacherAttendance->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DepartmentWorkItem[]
     */
    public function getDepartmentWorkItems(): Collection {
        return $this->departmentWorkItems;
    }

    public function addDepartmentWorkItem(DepartmentWorkItem $departmentWorkItem): self {
        if (!$this->departmentWorkItems->contains($departmentWorkItem)) {
            $this->departmentWorkItems[] = $departmentWorkItem;
            $departmentWorkItem->setDepartment($this);
        }

        return $this;
    }

    public function removeDepartmentWorkItem(DepartmentWorkItem $departmentWorkItem): self {
        if ($this->departmentWorkItems->contains($departmentWorkItem)) {
            $this->departmentWorkItems->removeElement($departmentWorkItem);
            // set the owning side to null (unless already changed)
            if ($departmentWorkItem->getDepartment() === $this) {
                $departmentWorkItem->setDepartment(null);
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
            $teacherWorkItem->setDepartment($this);
        }

        return $this;
    }

    public function removeTeacherWorkItem(TeacherWorkItem $teacherWorkItem): self {
        if ($this->teacherWorkItems->contains($teacherWorkItem)) {
            $this->teacherWorkItems->removeElement($teacherWorkItem);
            // set the owning side to null (unless already changed)
            if ($teacherWorkItem->getDepartment() === $this) {
                $teacherWorkItem->setDepartment(null);
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
            $teacherWorkSet->setDepartment($this);
        }

        return $this;
    }

    public function removeTeacherWorkSet(TeacherWorkSet $teacherWorkSet): self
    {
        if ($this->teacherWorkSets->contains($teacherWorkSet)) {
            $this->teacherWorkSets->removeElement($teacherWorkSet);
            // set the owning side to null (unless already changed)
            if ($teacherWorkSet->getDepartment() === $this) {
                $teacherWorkSet->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->setDepartment($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
            // set the owning side to null (unless already changed)
            if ($group->getDepartment() === $this) {
                $group->setDepartment(null);
            }
        }

        return $this;
    }

}
