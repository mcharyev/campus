<?php

namespace App\Entity;

use App\Entity\Region;
use App\Entity\Country;
use App\Entity\Nationality;
use App\Entity\AbstractStudent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExpelledStudentRepository")
 */
class ExpelledStudent extends AbstractStudent {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $systemId;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $firstnameEnglish;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $lastnameEnglish;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $patronymEnglish;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $previousLastnameEnglish;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $firstnameTurkmen;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $lastnameTurkmen;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $patronymTurkmen;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $previousLastnameTurkmen;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $gender;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $maritalStatus;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $tags;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Region")
     * @ORM\JoinColumn(name="region", referencedColumnName="id")
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumn(name="country", referencedColumnName="id")
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Nationality")
     * @ORM\JoinColumn(name="nationality", referencedColumnName="id")
     */
    private $nationality;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="students")
     * @ORM\JoinColumn(nullable=false)
     */
    private $studentGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentAbsence", mappedBy="student")
     */
    private $studentAbsences;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $birthDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $groupCode;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $matriculationDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $graduationDate;

    public function __construct() {
        $this->studentAbsences = new ArrayCollection();
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

    public function getPosition(): ?string {
        return json_decode($this->data['position']);
    }

    public function getPositions(): ?array {
        if (gettype($this->data['positions']) == 'string') {
            return json_decode($this->data['positions'], true);
        } else {
            return $this->data['positions'];
        }
    }

    public function getRelatives(): ?array {
        if (gettype($this->data['relatives']) == 'string') {
            return json_decode($this->data['relatives'], true);
        } else {
            return $this->data['relatives'];
        }
    }

    public function getExpulsionReason(): ?string {
        if (isset($this->data['expulsion_reason'])) {
            return json_decode($this->data['expulsion_reason']);
        }
        else
        {
            return "";
        }
    }

    public function getExpulsionDate(): ?string {
        if (isset($this->data['expulsion_date'])) {
            return json_decode($this->data['expulsion_date']);
        }
        else
        {
            return null;
        }
    }

    public function getExpulsionOrder(): ?string {
        if (isset($this->data['expulsion_order'])) {
            return json_decode($this->data['expulsion_order']);
        }
        else
        {
            return null;
        }
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getSystemId(): ?int {
        return $this->systemId;
    }

    public function setSystemId(int $systemId): self {
        $this->systemId = $systemId;

        return $this;
    }

    public function getFirstnameEnglish(): ?string {
        return $this->firstnameEnglish;
    }

    public function setFirstnameEnglish(?string $firstnameEnglish): self {
        $this->firstnameEnglish = $firstnameEnglish;

        return $this;
    }

    public function getLastnameEnglish(): ?string {
        return $this->lastnameEnglish;
    }

    public function setLastnameEnglish(?string $lastnameEnglish): self {
        $this->lastnameEnglish = $lastnameEnglish;

        return $this;
    }

    public function getPatronymEnglish(): ?string {
        return $this->patronymEnglish;
    }

    public function setPatronymEnglish(?string $patronymEnglish): self {
        $this->patronymEnglish = $patronymEnglish;

        return $this;
    }

    public function getPreviousLastnameEnglish(): ?string {
        return $this->previousLastnameEnglish;
    }

    public function setPreviousLastnameEnglish(?string $previousLastnameEnglish): self {
        $this->previousLastnameEnglish = $previousLastnameEnglish;

        return $this;
    }

    public function getFirstnameTurkmen(): ?string {
        return $this->firstnameTurkmen;
    }

    public function setFirstnameTurkmen(string $firstnameTurkmen): self {
        $this->firstnameTurkmen = $firstnameTurkmen;

        return $this;
    }

    public function getLastnameTurkmen(): ?string {
        return $this->lastnameTurkmen;
    }

    public function setLastnameTurkmen(string $lastnameTurkmen): self {
        $this->lastnameTurkmen = $lastnameTurkmen;

        return $this;
    }

    public function getPatronymTurkmen(): ?string {
        return $this->patronymTurkmen;
    }

    public function setPatronymTurkmen(?string $patronymTurkmen): self {
        $this->patronymTurkmen = $patronymTurkmen;

        return $this;
    }

    public function getPreviousLastnameTurkmen(): ?string {
        return $this->previousLastnameTurkmen;
    }

    public function setPreviousLastnameTurkmen(?string $previousLastnameTurkmen): self {
        $this->previousLastnameTurkmen = $previousLastnameTurkmen;

        return $this;
    }

    public function getGender(): ?int {
        return $this->gender;
    }

    public function setGender(int $gender): self {
        $this->gender = $gender;

        return $this;
    }

    public function getMaritalStatus(): ?int {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(int $maritalStatus): self {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getTags(): ?string {
        return $this->tags;
    }

    public function setTags(?string $tags): self {
        $this->tags = $tags;

        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data): self {
        $this->data = $data;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getRegion(): ?Region {
        return $this->region;
    }

    public function setRegion(?Region $region): self {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): ?Country {
        return $this->country;
    }

    public function setCountry(?Country $country): self {
        $this->country = $country;

        return $this;
    }

    public function getNationality(): ?Nationality {
        return $this->nationality;
    }

    public function setNationality(?Nationality $nationality): self {
        $this->nationality = $nationality;

        return $this;
    }

    public function getStudentGroup(): ?Group {
        return $this->studentGroup;
    }

    public function setStudentGroup(?Group $studentGroup): self {
        $this->studentGroup = $studentGroup;
        $this->groupCode = $studentGroup->getSystemId();

        return $this;
    }

    public function setAdditionalData($params = null) {
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $this->data[$key] = json_encode($value);
            }
        }
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
            $studentAbsence->setStudent($this);
        }

        return $this;
    }

    public function removeStudentAbsence(StudentAbsence $studentAbsence): self {
        if ($this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences->removeElement($studentAbsence);
            // set the owning side to null (unless already changed)
            if ($studentAbsence->getStudent() === $this) {
                $studentAbsence->setStudent(null);
            }
        }

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getGroupCode(): ?int {
        return $this->groupCode;
    }

    public function setGroupCode(int $groupCode): self {
        $this->groupCode = $groupCode;

        return $this;
    }

    public function getDepartment(): ?Department {
        return $this->getStudentGroup()->getStudyProgram()->getDepartment();
    }

    public function getFaculty(): ?Faculty {
        return $this->getDepartment()->getFaculty();
    }

    public function getMatriculationDate(): ?\DateTimeInterface
    {
        return $this->matriculationDate;
    }

    public function setMatriculationDate(?\DateTimeInterface $matriculationDate): self
    {
        $this->matriculationDate = $matriculationDate;

        return $this;
    }

    public function getGraduationDate(): ?\DateTimeInterface
    {
        return $this->graduationDate;
    }

    public function setGraduationDate(?\DateTimeInterface $graduationDate): self
    {
        $this->graduationDate = $graduationDate;

        return $this;
    }

}
