<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProgramCourseRepository")
 */
class ProgramCourse {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @ORM\OrderBy({"name_english" = "ASC"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $systemId;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint")
     */
    private $semester;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudyProgram", inversedBy="programCourses")
     */
    private $studyProgram;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="programCourses")
     */
    private $department;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $data;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     */
    private $language;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProgramModule")
     * @ORM\JoinColumn(nullable=false)
     */
    private $module;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProgramCourseType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     */
    private $viewOrder;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $departmentCode;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $creditType;

    function __construct($data = null) {
        if ($data == null) {
            $this->data = array('note' => '', 'counts' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
        }
    }

    public function getCredits(): ?int {
        return $this->data['counts'][0];
    }
    
    public function getLectureHours(): ?int {
        return $this->data['counts'][2];
    }
    
    public function getPracticeHours(): ?int {
        return $this->data['counts'][3];
    }
    
    public function getLabHours(): ?int {
        return $this->data['counts'][4];
    }

    public function getCounts(): ?array {
        return $this->data['counts'];
    }

    public function getCountsAsString(): ?string {
        return implode(",", $this->data['counts']);
    }

    public function getNote(): ?string {
        return json_decode($this->data['note']);
    }

    public function getId(): ?int {
        return $this->id;
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

    public function getSemester(): ?int {
        return $this->semester;
    }

    public function setSemester(int $semester): self {
        $this->semester = $semester;

        return $this;
    }

    public function getStudyProgram(): ?StudyProgram {
        return $this->studyProgram;
    }

    public function setStudyProgram(?StudyProgram $studyProgram): self {
        $this->studyProgram = $studyProgram;

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

    public function getData() {
        return $this->data;
    }

    public function setData($data): self {
        $this->data = $data;

        return $this;
    }

    public function getLanguage(): ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language): self {
        $this->language = $language;

        return $this;
    }

    public function setAdditionalData($params) {
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                if ($key == 'counts') {
                    $this->data[$key] = $value;
                } else {
                    $this->data[$key] = json_encode($value);
                }
            }
        }
    }

    public function getModule(): ?ProgramModule {
        return $this->module;
    }

    public function setModule(?ProgramModule $module): self {
        $this->module = $module;

        return $this;
    }

    public function getType(): ?ProgramCourseType {
        return $this->type;
    }

    public function setType(?ProgramCourseType $type): self {
        $this->type = $type;

        return $this;
    }

    public function getViewOrder(): ?int {
        return $this->viewOrder;
    }

    public function setViewOrder(int $viewOrder): self {
        $this->viewOrder = $viewOrder;

        return $this;
    }

    public function getDepartmentCode(): ?int {
        return $this->departmentCode;
    }

    public function setDepartmentCode(int $departmentCode): self {
        $this->departmentCode = $departmentCode;

        return $this;
    }

    public function getCreditType(): ?int
    {
        return $this->creditType;
    }

    public function setCreditType(int $creditType): self
    {
        $this->creditType = $creditType;

        return $this;
    }

}
