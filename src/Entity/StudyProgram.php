<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudyProgramRepository")
 */
class StudyProgram {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $approvalYear;

    /**
     * @ORM\Column(type="smallint", nullable=true)
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
     * @ORM\OneToMany(targetEntity="App\Entity\Group", mappedBy="studyProgram")
     * @ORM\OrderBy({"systemId" = "DESC"})
     */
    private $groups;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProgramLevel", inversedBy="studyPrograms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $programLevel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="studyPrograms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProgramCourse", mappedBy="studyProgram")
     * @ORM\OrderBy({"viewOrder" = "ASC"})
     */
    private $programCourses;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $departmentCode;

    public function __construct() {
        $this->groups = new ArrayCollection();
        $this->programCourses = new ArrayCollection();
        if ($this->data == null) {
            $this->data = [
                'field' => '',
                'subfield' => '',
                'major' => '',
                'degree' => '',
                'qualification' => '',
                'deputy' => '',
                'minister' => '',
                'rector' => '',
                'date' => ''
            ];
        }
    }

    public function getField(): ?string {
        return json_decode($this->data['field']);
    }

    public function getSubfield(): ?string {
        return json_decode($this->data['subfield']);
    }

    public function getMajor(): ?string {
        return json_decode($this->data['major']);
    }

    public function getQualification(): ?string {
        return json_decode($this->data['qualification']);
    }

    public function getDegree(): ?string {
        return json_decode($this->data['degree']);
    }

    public function getDate(): ?string {
        return json_decode($this->data['date']);
    }

    public function getDeputy(): ?string {
        return json_decode($this->data['deputy']);
    }

    public function getMinister(): ?string {
        return json_decode($this->data['minister']);
    }

    public function getRector(): ?string {
        return json_decode($this->data['rector']);
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

    public function getLetterCode(): ?string {
        return $this->letterCode;
    }

    public function setLetterCode(?string $letterCode): self {
        $this->letterCode = $letterCode;

        return $this;
    }

    public function getApprovalYear(): ?int {
        return $this->approvalYear;
    }

    public function setApprovalYear(?int $approvalYear): self {
        $this->approvalYear = $approvalYear;

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

    public function getSystemId(): ?int {
        return $this->systemId;
    }

    public function setSystemId(?int $systemId): self {
        $this->systemId = $systemId;

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroups(): Collection {
        return $this->groups;
    }

    public function addGroup(Group $group): self {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->setStudyProgram($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): self {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
            // set the owning side to null (unless already changed)
            if ($group->getStudyProgram() === $this) {
                $group->setStudyProgram(null);
            }
        }

        return $this;
    }

    public function getProgramLevel(): ?ProgramLevel {
        return $this->programLevel;
    }

    public function setProgramLevel(?ProgramLevel $programLevel): self {
        $this->programLevel = $programLevel;

        return $this;
    }

    public function getDepartment(): ?Department {
        return $this->department;
    }

    public function setDepartment(?Department $department): self {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection|ProgramCourse[]
     */
    public function getProgramCourses(): Collection {
        return $this->programCourses;
    }

    public function getModuleSums($moduleNumber): ?array {
        $moduleSums = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($this->programCourses as $programCourse) {
            if ($programCourse->getModule()->getSystemId() == $moduleNumber) {
                $counts = $programCourse->getCounts();
                $i = 0;
                foreach ($counts as $count) {
                    if ($i < 31) {
                        $moduleSums[$i] += $count;
                    }
                    $i++;
                }
            }
        }
        return $moduleSums;
    }

    public function addProgramCourse(ProgramCourse $programCourse): self {
        if (!$this->programCourses->contains($programCourse)) {
            $this->programCourses[] = $programCourse;
            $programCourse->setStudyProgram($this);
        }

        return $this;
    }

    public function removeProgramCourse(ProgramCourse $programCourse): self {
        if ($this->programCourses->contains($programCourse)) {
            $this->programCourses->removeElement($programCourse);
            // set the owning side to null (unless already changed)
            if ($programCourse->getStudyProgram() === $this) {
                $programCourse->setStudyProgram(null);
            }
        }

        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data): self {
        $this->data = $data;

        return $this;
    }

    public function setAdditionalData($params = null) {
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $this->data[$key] = json_encode($value);
            }
        }
    }

    public function getDepartmentCode(): ?int {
        return $this->departmentCode;
    }

    public function setDepartmentCode(int $departmentCode): self {
        $this->departmentCode = $departmentCode;

        return $this;
    }

}
