<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacultyRepository")
 */
class Faculty {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $systemId;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $nameTurkmen;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher")
     */
    private $dean;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher")
     */
    private $firstDeputyDean;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher")
     */
    private $secondDeputyDean;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher")
     */
    private $thirdDeputyDean;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher")
     */
    private $assistant;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Department", mappedBy="faculty")
     * @ORM\OrderBy({"systemId" = "ASC"})
     */
    private $departments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentAbsence", mappedBy="faculty")
     */
    private $studentAbsences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherAttendance", mappedBy="faculty")
     */
    private $teacherAttendances;

    public function __construct() {
        $this->departments = new ArrayCollection();
        $this->studentAbsences = new ArrayCollection();
        $this->teacherAttendances = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getNameEnglish(): ?string {
        return $this->nameEnglish;
    }

    public function setNameEnglish(string $nameEnglish): self {
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

    public function getSystemId(): ?int {
        return $this->systemId;
    }

    public function setSystemId(?int $systemId): self {
        $this->systemId = $systemId;

        return $this;
    }

    public function getLetterCode(): ?string {
        return $this->letterCode;
    }

    public function setLetterCode(?string $letterCode): self {
        $this->letterCode = $letterCode;

        return $this;
    }

    public function getDeanId(): ?int {
        return $this->deanId;
    }

    public function setDeanId(?int $deanId): self {
        $this->deanId = $deanId;

        return $this;
    }

    public function getFirstViceDeanId(): ?int {
        return $this->firstViceDeanId;
    }

    public function setFirstViceDeanId(?int $firstViceDeanId): self {
        $this->firstViceDeanId = $firstViceDeanId;

        return $this;
    }

    public function getSecondViceDeanId(): ?int {
        return $this->secondViceDeanId;
    }

    public function setSecondViceDeanId(?int $secondViceDeanId): self {
        $this->secondViceDeanId = $secondViceDeanId;

        return $this;
    }

    public function getThirdViceDeanId(): ?int {
        return $this->thirdViceDeanId;
    }

    public function setThirdViceDeanId(?int $thirdViceDeanId): self {
        $this->thirdViceDeanId = $thirdViceDeanId;

        return $this;
    }

    public function getAssistantId(): ?int {
        return $this->assistantId;
    }

    public function setAssistantId(?int $assistantId): self {
        $this->assistantId = $assistantId;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getDean(): ?Teacher {
        return $this->dean;
    }

    public function setDean(?Teacher $dean): self {
        $this->dean = $dean;

        return $this;
    }

    public function getFirstDeputyDean(): ?Teacher {
        return $this->firstDeputyDean;
    }

    public function setFirstDeputyDean(?Teacher $firstDeputyDean): self {
        $this->firstDeputyDean = $firstDeputyDean;

        return $this;
    }

    public function getSecondDeputyDean(): ?Teacher {
        return $this->secondDeputyDean;
    }

    public function setSecondDeputyDean(?Teacher $secondDeputyDean): self {
        $this->secondDeputyDean = $secondDeputyDean;

        return $this;
    }

    public function getThirdDeputyDean(): ?Teacher {
        return $this->thirdDeputyDean;
    }

    public function setThirdDeputyDean(?Teacher $thirdDeputyDean): self {
        $this->thirdDeputyDean = $thirdDeputyDean;

        return $this;
    }

    public function getAllDeans() {
        $deans = [];
        $deans[] = $this->getDean();
        $deans[] = $this->getFirstDeputyDean();
        $deans[] = $this->getSecondDeputyDean();
        $deans[] = $this->getThirdDeputyDean();
        return $deans;
    }

    public function getAssistant(): ?Teacher {
        return $this->assistant;
    }

    public function setAssistant(?Teacher $assistant): self {
        $this->assistant = $assistant;

        return $this;
    }

    /**
     * @return Collection|Department[]
     */
    public function getDepartments(): Collection {
        return $this->departments;
    }

    public function addDepartment(Department $department): self {
        if (!$this->departments->contains($department)) {
            $this->departments[] = $department;
            $department->setFaculty($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): self {
        if ($this->departments->contains($department)) {
            $this->departments->removeElement($department);
            // set the owning side to null (unless already changed)
            if ($department->getFaculty() === $this) {
                $department->setFaculty(null);
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
            $studentAbsence->setFaculty($this);
        }

        return $this;
    }

    public function removeStudentAbsence(StudentAbsence $studentAbsence): self {
        if ($this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences->removeElement($studentAbsence);
            // set the owning side to null (unless already changed)
            if ($studentAbsence->getFaculty() === $this) {
                $studentAbsence->setFaculty(null);
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
            $teacherAttendance->setFaculty($this);
        }

        return $this;
    }

    public function removeTeacherAttendance(TeacherAttendance $teacherAttendance): self {
        if ($this->teacherAttendances->contains($teacherAttendance)) {
            $this->teacherAttendances->removeElement($teacherAttendance);
            // set the owning side to null (unless already changed)
            if ($teacherAttendance->getFaculty() === $this) {
                $teacherAttendance->setFaculty(null);
            }
        }

        return $this;
    }

    /**
     * @return Array indexed with group years and with student count assigned as values
     */
    public function getStudentCountByYear() {
        // 0 index represents LLD and 5 index represents total student count column
        $result = [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];
        foreach ($this->getDepartments() as $department) {
            foreach ($department->getGroups() as $group) {
                if ($group->getStatus() == 1) {
                    $groupYear = $group->getStudyYear();
//                        echo $group->getLetterCode() . " : " . $groupYear . ":".$this->getNameEnglish()."<br>";
                    if ($groupYear > -1 && $groupYear < 5) {
                        $total = $group->getTotalStudentCount();
                        $result[$groupYear] += $total;
                        $result[5] += $total;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return Array indexed with group years, genders and with student count assigned as values
     */
    public function getStudentGenderCountByYear() {
        // 0 index represents LLD and 5 index represents total student count column
        // in faculty arrays [male count, female count, total count]
        $result = [
            0 => [0, 0, 0],
            1 => [0, 0, 0],
            2 => [0, 0, 0],
            3 => [0, 0, 0],
            4 => [0, 0, 0],
            5 => [0, 0, 0],
        ];

        foreach ($this->getDepartments() as $department) {
            foreach ($department->getGroups() as $group) {
                if ($group->getStatus() == 1) {
                    $groupYear = $group->getStudyYear();
//                        echo $group->getLetterCode() . " : " . $groupYear . "<br>";
                    if ($groupYear > -1 && $groupYear < 5) {
                        $male = $group->getMaleStudentCount();
                        $female = $group->getFemaleStudentCount();
                        $total = $male + $female;
                        $result[$groupYear][0] += $male;
                        $result[$groupYear][1] += $female;
                        $result[$groupYear][2] += $total;

                        $result[5][0] += $male;
                        $result[5][1] += $female;
                        $result[5][2] += $total;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return Array indexed with group years and with student count assigned as values
     */
    public function getStudentCountByRegion() {
        // 1-7 represents regions and 10 index represents total student count column
        $result = [
            6 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            7 => 0,
            10 => 0,
        ];
        foreach ($this->getDepartments() as $department) {
            foreach ($department->getGroups() as $group) {
                if ($group->getStatus() == 1) {
                    $groupYear = $group->getStudyYear();
                    if ($groupYear > -1 && $groupYear < 5) {
                        foreach ($result as $key => $value) {
                            $count = $group->getRegionStudentCount($key);
                            $result[$key] += $count;
                            $result[10] += $count;
                        }
                    }
                }
            }
        }

        return $result;
    }

}
