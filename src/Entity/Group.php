<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
 */
class Group {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true, options={"default": ""})
     */
    private $letterCode;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $systemId = 0;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $graduationYear = 0;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 1})
     */
    private $status = 1;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EnrolledStudent", mappedBy="studentGroup")
     * @ORM\OrderBy({"subgroup" = "ASC", "systemId" = "ASC"})
     */
    private $students;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AlumnusStudent", mappedBy="studentGroup")
     */
    private $alumniStudents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudyProgram", inversedBy="groups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $studyProgram;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="advisedGroups")
     * @ORM\JoinColumn(nullable=true)
     */
    private $advisor;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EnrolledStudent")
     * @ORM\JoinColumn(nullable=true)
     */
    private $groupLeader;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $departmentCode;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentAbsence", mappedBy="studentGroup")
     */
    private $studentAbsences;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="groups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $scheduleName;

    public function __construct() {
        $this->students = new ArrayCollection();
        $this->studentAbsences = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getLetterCode(): ?string {
        return $this->letterCode;
    }

    public function setLetterCode(?string $letterCode): self {
        $this->letterCode = $letterCode;

        return $this;
    }

    /**
     * @return int Returns the system id of the group's program
     */
    public function getProgramId(): ?int {
        return $this->programId;
    }

    /**
     * Sets the program id of the group
     * @return \Entity\Group Returns the modified group object
     * @param int $programId integer representing the system id of the program
     */
    public function setProgramId(?int $programId): self {
        $this->programId = $programId;

        return $this;
    }

    public function getSystemId(): ?int {
        return $this->systemId;
    }

    public function setSystemId(?int $systemId): self {
        $this->systemId = $systemId;

        return $this;
    }

    public function getGraduationYear(): ?int {
        return $this->graduationYear;
    }

    public function setGraduationYear(?int $graduationYear): self {
        $this->graduationYear = $graduationYear;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(?int $status): self {
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
     * @return Collection|EnrolledStudent[]
     */
    public function getStudents(): Collection {
        return $this->students;
    }

    /**
     * @return Collection|EnrolledStudent[]
     */
    public function getAlumniStudents(): Collection {
        return $this->alumniStudents;
    }

    public function addStudent(EnrolledStudent $student): self {
        if (!$this->students->contains($student)) {
            $this->students[] = $student;
            $student->setStudentGroup($this);
        }

        return $this;
    }

    public function removeStudent(EnrolledStudent $student): self {
        if ($this->students->contains($student)) {
            $this->students->removeElement($student);
            // set the owning side to null (unless already changed)
            if ($student->getStudentGroup() === $this) {
                $student->setStudentGroup(null);
            }
        }

        return $this;
    }

    public function getMaleStudentCount(): ?int {
        $result = 0;
        foreach ($this->students as $student) {
            if ($student->getGender() == 1) {
                $result++;
            }
        }
        return $result;
    }

    public function getFemaleStudentCount(): ?int {
        $result = 0;
        foreach ($this->students as $student) {
            if ($student->getGender() == 0) {
                $result++;
            }
        }
        return $result;
    }

    public function getTotalStudentCount(): ?int {
        return $this->students->count();
    }

    public function getStudyProgram(): ?StudyProgram {
        return $this->studyProgram;
    }

    public function setStudyProgram(?StudyProgram $studyProgram): self {
        $this->studyProgram = $studyProgram;

        return $this;
    }

    public function getDepartmentHead(): ?Teacher {
        return $this->getDepartment()->getDepartmentHead();
    }

    public function getFaculty(): ?Faculty {
        return $this->getDepartment()->getFaculty();
    }

    public function getDean(): ?Teacher {
        return $this->getDepartment()->getFaculty()->getDean();
    }

    public function getAdvisor(): ?Teacher {
        return $this->advisor;
    }

    public function setAdvisor(?Teacher $advisor): self {
        $this->advisor = $advisor;

        return $this;
    }

    public function getGroupLeader(): ?EnrolledStudent {
        return $this->groupLeader;
    }

    public function setGroupLeader(?EnrolledStudent $groupLeader): self {
        $this->groupLeader = $groupLeader;

        return $this;
    }

    public function getDepartmentCode(): ?int {
        return $this->departmentCode;
    }

    public function setDepartmentCode(?int $departmentCode): self {
        $this->departmentCode = $departmentCode;

        return $this;
    }

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getStudentAbsences(): Collection {
        return $this->studentAbsences;
    }

    /**
     * @return int|StudentAbsencesCount
     */
    public function getAbsencesCount(?int $month): int {
        $result = 0;
        $absences = $this->studentAbsences;
        foreach ($absences as $absence) {
            if ($month == $absence->getDate()->format("n") && $absence->getClassType()->getSystemId() != 6) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @return int|HealthAbsencesCount
     */
    public function getHealthAbsencesCount(?int $month): int {
        $result = 0;
        $absences = $this->studentAbsences;
        foreach ($absences as $absence) {
            if ($month == $absence->getDate()->format("n")) {
                if ($absence->getExcuseStatus() == 1 && strlen($absence->getExcuseNote()) == 0) {
                    $result++;
                }
            }
        }
        return $result;
    }

    /**
     * @return int|OtherAbsencesCount
     */
    public function getOtherAbsencesCount(?int $month): int {
        $result = 0;
        $absences = $this->studentAbsences;
        foreach ($absences as $absence) {
            if ($month == $absence->getDate()->format("n")) {
                if ($absence->getExcuseStatus() == 1 && strpos($absence->getExcuseNote(), "other") !== false) {
                    $result++;
                }
            }
        }
        return $result;
    }

    /**
     * @return int|UnexcusedAbsencesCount
     */
    public function getUnexcusedAbsencesCount(?int $month): int {
        $result = 0;
        $absences = $this->studentAbsences;
        foreach ($absences as $absence) {
            if ($month == $absence->getDate()->format("n")) {
                if ($absence->getExcuseStatus() == 0) {
                    $result++;
                }
            }
        }
        return $result;
    }

    public function addStudentAbsence(StudentAbsence $studentAbsence): self {
        if (!$this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences[] = $studentAbsence;
            $studentAbsence->setStudentGroup($this);
        }

        return $this;
    }

    public function removeStudentAbsence(StudentAbsence $studentAbsence): self {
        if ($this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences->removeElement($studentAbsence);
            // set the owning side to null (unless already changed)
            if ($studentAbsence->getStudentGroup() === $this) {
                $studentAbsence->setStudentGroup(null);
            }
        }

        return $this;
    }

    /*
     * Gets the cumulative semester of group given year and semester
     */

    public function getGroupSemester($beginningYearOfAcademicYear, $semester) {
        if ($this->getStudyProgram()->getProgramLevel()->getSystemId() == 6) {
            $currentYear = 5 - ($this->getGraduationYear() - $beginningYearOfAcademicYear);
        } else {
            $currentYear = 2 - ($this->getGraduationYear() - $beginningYearOfAcademicYear);
        }
        return ($currentYear - 1) * 2 + $semester;
    }

    public function getStudyYear(): ?int {
        $currentYear = date("Y");
        $currentMonth = date("n");
        if ($this->getStudyProgram()->getProgramLevel()->getSystemId() == 6) {
            if ($currentMonth > 7) {
                $studyYear = 5 - ($this->getGraduationYear() - $currentYear);
            } else {
                $studyYear = 4 - ($this->getGraduationYear() - $currentYear);
            }
        } else {
            if ($currentMonth > 7) {
                $studyYear = 2 - ($this->getGraduationYear() - $currentYear);
            } else {
                $studyYear = 1 - ($this->getGraduationYear() - $currentYear);
            }
        }
        return $studyYear;
    }

    public function getRegionStudentCount(int $regionId): ?int {
        $result = 0;
        foreach ($this->students as $student) {
            if ($student->getRegion()->getSystemId() == $regionId) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * Get list of authorized persons to access student information
     * @return Collection|DisciplineAction[]
     */
    public function getAuthorizedTeachers() {
        $authorizedTeachers = [];

        $authorizedTeachers[] = $this->getFaculty()->getDean();
        $authorizedTeachers[] = $this->getFaculty()->getFirstDeputyDean();
        $authorizedTeachers[] = $this->getFaculty()->getSecondDeputyDean();
        $authorizedTeachers[] = $this->getFaculty()->getThirdDeputyDean();
        $authorizedTeachers[] = $this->getDepartment()->getDepartmentHead();
        $authorizedTeachers[] = $this->getAdvisor();

        return $authorizedTeachers;
    }

    public function isTeacherAuthorized(Teacher $teacher): bool {
        return in_array($teacher, $this->getAuthorizedTeachers());
    }

    public function getDepartment(): ?Department {
        return $this->department;
    }

    public function setDepartment(?Department $department): self {
        $this->department = $department;

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
