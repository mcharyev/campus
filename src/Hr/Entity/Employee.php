<?php

namespace App\Hr\Entity;

use App\Entity\Nationality;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Hr\Repository\EmployeeRepository")
 */
class Employee {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $systemId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $patronym;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $previousLastname;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $experience;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $employmentDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $birthplace;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Nationality")
     */
    private $nationality;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="text")
     */
    private $permanentAddress;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $phone1;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $phone2;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $phone3;

    /**
     * @ORM\Column(type="smallint")
     */
    private $gender;

    /**
     * @ORM\Column(type="smallint")
     */
    private $maritalStatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $passport;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $education;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $scientificDegree;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $zdno;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $stu;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $pension;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $departmentId;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $passportId;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $worktimeCategory;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $departmentCode;

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

    public function getLastnameFirstname(): ?string {
        return $this->lastname . " " . $this->firstname;
    }

    public function getPatronym(): ?string {
        return $this->patronym;
    }

    public function setPatronym(?string $patronym): self {
        $this->patronym = $patronym;

        return $this;
    }

    public function getPreviousLastname(): ?string {
        return $this->previousLastname;
    }

    public function setPreviousLastname(?string $previousLastname): self {
        $this->previousLastname = $previousLastname;

        return $this;
    }

    public function getPosition(): ?string {
        return $this->position;
    }

    public function setPosition(?string $position): self {
        $this->position = $position;

        return $this;
    }

    public function getExperience(): ?int {
        return $this->experience;
    }

    public function setExperience(int $experience): self {
        $this->experience = $experience;

        return $this;
    }

    public function getEmploymentDate(): ?\DateTimeInterface {
        return $this->employmentDate;
    }

    public function setEmploymentDate(?\DateTimeInterface $employmentDate): self {
        $this->date = $employmentDate;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getBirthplace(): ?string {
        return $this->birthplace;
    }

    public function setBirthplace(?string $birthplace): self {
        $this->birthplace = $birthplace;

        return $this;
    }

    public function getNationality(): ?Nationality {
        return $this->nationality;
    }

    public function setNationality(?Nationality $nationality): self {
        $this->nationality = $nationality;
        return $this;
    }

    public function getAddress(): ?string {
        return $this->address;
    }

    public function setAddress(?string $address): self {
        $this->address = $address;

        return $this;
    }

    public function getPermanentAddress(): ?string {
        return $this->permanentAddress;
    }

    public function setPermanentAddress(string $permanentAddress): self {
        $this->permanentAddress = $permanentAddress;

        return $this;
    }

    public function getPhone1(): ?string {
        return $this->phone1;
    }

    public function setPhone1(?string $phone1): self {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getPhone3(): ?string {
        return $this->phone3;
    }

    public function setPhone3(?string $phone3): self {
        $this->phone3 = $phone3;

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

    public function getPassport(): ?string {
        return $this->passport;
    }

    public function setPassport(?string $passport): self {
        $this->passport = $passport;

        return $this;
    }

    public function getEducation(): ?string {
        return $this->education;
    }

    public function setEducation(?string $education): self {
        $this->education = $education;

        return $this;
    }

    public function getScientificDegree(): ?string {
        return $this->scientificDegree;
    }

    public function setScientificDegree(string $scientificDegree): self {
        $this->scientificDegree = $scientificDegree;

        return $this;
    }

    public function getZdno(): ?string {
        return $this->zdno;
    }

    public function setZdno(?string $zdno): self {
        $this->zdno = $zdno;

        return $this;
    }

    public function getStu(): ?string {
        return $this->stu;
    }

    public function setStu(?string $stu): self {
        $this->stu = $stu;

        return $this;
    }

    public function getPension(): ?string {
        return $this->pension;
    }

    public function setPension(string $pension): self {
        $this->pension = $pension;

        return $this;
    }

    public function getDepartmentId(): ?int {
        return $this->departmentId;
    }

    public function setDepartmentId(?int $departmentId): self {
        $this->departmentId = $departmentId;

        return $this;
    }

    public function getPassportId(): ?string {
        return $this->passportId;
    }

    public function setPassportId(?string $passportId): self {
        $this->passportId = $passportId;

        return $this;
    }

    public function getData(): ?array {
        return $this->data;
    }

    public function setData(?array $data): self {
        $this->data = $data;

        return $this;
    }

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = $value;
        } else {
            $this->data += array($column => json_encode($value));
        }
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

    public function getWorktimeCategory(): ?int
    {
        return $this->worktimeCategory;
    }

    public function setWorktimeCategory(int $worktimeCategory): self
    {
        $this->worktimeCategory = $worktimeCategory;

        return $this;
    }

    public function getDepartmentCode(): ?int
    {
        return $this->departmentCode;
    }

    public function setDepartmentCode(?int $departmentCode): self
    {
        $this->departmentCode = $departmentCode;

        return $this;
    }

}
