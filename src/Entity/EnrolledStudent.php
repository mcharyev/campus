<?php

namespace App\Entity;

use App\Entity\Region;
use App\Entity\Country;
use App\Entity\Nationality;
use App\Entity\AbstractStudent;
use App\Entity\Teacher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EnrolledStudentRepository")
 */
class EnrolledStudent extends AbstractStudent {

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
     * @ORM\OneToMany(targetEntity="App\Entity\DisciplineAction", mappedBy="student")
     */
    private $disciplineActions;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $subgroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HostelRoom", inversedBy="enrolledStudents")
     * @ORM\JoinColumn(nullable=true)
     */
    private $hostelRoom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nationalId;

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
        $this->disciplineActions = new ArrayCollection();
    }

//    public function getDataField(?string $fieldName): ?string {
//        return json_decode($this->data[$fieldName]);
//    }

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

//    public function setDataField($fieldName, $value): self {
//        $this->data[$fieldName] = $value;
//        return $this;
//    }

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = json_encode($value);
        } else {
            $this->data += array($column => json_encode($value));
        }
        return $this;
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

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getStudentAbsencesByDate(\DateTime $beginDate, \DateTime $endDate): int {
        $result = 0;
        if ($beginDate == null or $endDate == null) {
            return $result;
        }
        foreach ($this->studentAbsences as $absence) {
            if ($absence->getDate() >= $beginDate && $absence->getDate() < $endDate) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getExcusedAbsencesCount(): int {
        $result = 0;
        foreach ($this->studentAbsences as $absence) {
            if ($absence->getExcuseStatus() == 1) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getExcusedAbsencesCountByDate(\DateTime $beginDate, \DateTime $endDate): int {
        $result = 0;
        if (!empty($beginDate) and!empty($endDate)) {
            foreach ($this->studentAbsences as $absence) {
                if ($absence->getDate() >= $beginDate && $absence->getDate() < $endDate) {
                    if ($absence->getExcuseStatus() == 1) {
                        $result++;
                    }
                }
            }
        } else {
            foreach ($this->studentAbsences as $absence) {
                if ($absence->getExcuseStatus() == 1) {
                    $result++;
                }
            }
        }
        return $result;
    }

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getRecoveredAbsencesCount(): int {
        $result = 0;
        foreach ($this->studentAbsences as $absence) {
            if ($absence->getStatus() == 2) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getRecoveredAbsencesCountByDate(\DateTime $beginDate, \DateTime $endDate): int {
        $result = 0;
        if (!empty($beginDate) and!empty($endDate)) {
            foreach ($this->studentAbsences as $absence) {
                if ($absence->getDate() >= $beginDate && $absence->getDate() < $endDate) {
                    if ($absence->getStatus() == 2) {
                        $result++;
                    }
                }
            }
        } else {
            foreach ($this->studentAbsences as $absence) {
                if ($absence->getStatus() == 2) {
                    $result++;
                }
            }
        }
        return $result;
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

    public function getMajorInTurkmen() {
        return $this->getStudentGroup()->getStudyProgram()->getNameTurkmen();
    }

    public function getMajorInEnglish() {
        return $this->getStudentGroup()->getStudyProgram()->getNameEnglish();
    }

    public function isDean(Teacher $teacher) {
        $deans = $this->getFaculty()->getAllDeans();
        if (in_array($teacher, $deans)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return Collection|DisciplineAction[]
     */
    public function getDisciplineActions(): Collection {
        return $this->disciplineActions;
    }

    public function addDisciplineAction(DisciplineAction $disciplineAction): self {
        if (!$this->disciplineActions->contains($disciplineAction)) {
            $this->disciplineActions[] = $disciplineAction;
            $disciplineAction->setStudent($this);
        }

        return $this;
    }

    public function removeDisciplineAction(DisciplineAction $disciplineAction): self {
        if ($this->disciplineActions->contains($disciplineAction)) {
            $this->disciplineActions->removeElement($disciplineAction);
            // set the owning side to null (unless already changed)
            if ($disciplineAction->getStudent() === $this) {
                $disciplineAction->setStudent(null);
            }
        }

        return $this;
    }

    public function getProgramLevel() {
        return $this->getStudentGroup()->getStudyProgram()->getProgramLevel();
    }

    public function getCurrentPosition(): string {
        $strPosition = "Halkara ynsanperwer ylymlary we ösüş uniwersitetiniň ";
        $strPosition .= $this->getFaculty()->getNameTurkmen() . "niň ";
        if ($this->getProgramLevel()->getLetterCode() == "B") {
            $strPosition .= $this->getMajorInTurkmen() . " hünäriniň ";
            $studyYear = $this->getStudentGroup()->getStudyYear();
            $strPosition .= $studyYear . "-nji ýyl talyby";
        } else {
            $strPosition .= "“" . $this->getMajorInTurkmen() . "” taýýarlyk ugrunyň ";
            //$studyYear = $this->getStudentGroup()->getStudyYear();
            $strPosition .= "magistranty";
        }
        return $strPosition;
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
        $authorizedTeachers[] = $this->getStudentGroup()->getAdvisor();

        return $authorizedTeachers;
    }

    public function isTeacherAuthorized(Teacher $teacher): bool {
        return in_array($teacher, $this->getAuthorizedTeachers());
    }

    public function getSubgroup(): ?int {
        return $this->subgroup;
    }

    public function setSubgroup(int $subgroup): self {
        $this->subgroup = $subgroup;

        return $this;
    }

    public function getSubgroupLetter(): ?string {
        $letters = [1 => 'A', 2 => 'B'];
        return $letters[$this->getSubgroup()];
    }

    public function getHostelRoom(): ?HostelRoom {
        return $this->hostelRoom;
    }

    public function setHostelRoom(?HostelRoom $hostelRoom): self {
        $this->hostelRoom = $hostelRoom;

        return $this;
    }

    public function getNationalId(): ?string {
        return $this->nationalId;
    }

    public function setNationalId(?string $nationalId): self {
        $this->nationalId = $nationalId;

        return $this;
    }

    /**
     * Set values with different functions based on import
     * @return self
     */
    public function setFieldGeneral($field, $value) {
        switch ($field) {
            case "systemId":
                return $this->setSystemId($value);
            case "nationalId":
                return $this->setNationalId($value);
            case "dob":
            case "mobile_phone":
            case "phone":
            case "address":
            case "address2":
            case "permanent_address":
            case "temporary_registration_address":
                return $this->setDataField($field, $value);
            default:
        }
        return $this;
    }

    /**
     * Get values with different functions based on import
     * @return value
     */
    public function getFieldGeneral($field) {
        switch ($field) {
            case "systemId":
                return $this->getSystemId();
            case "nationalId":
                return $this->getNationalId();
            case "lastnameTurkmen":
                return $this->getLastnameTurkmen();
            case "firstnameTurkmen":
                return $this->getFirstnameTurkmen();
            case "patronymTurkmen":
                return $this->getPatronymTurkmen();
            case "dob":
            case "mobile_phone":
            case "phone":
            case "address":
            case "address2":
            case "permanent_address":
            case "temporary_registration_address":
                return $this->getDataField($field);
            default:
        }
        return null;
    }

    public function getMatriculationDate(): ?\DateTimeInterface {
        return $this->matriculationDate;
    }

    public function setMatriculationDate(?\DateTimeInterface $matriculationDate): self {
        $this->matriculationDate = $matriculationDate;

        return $this;
    }

    public function getGraduationDate(): ?\DateTimeInterface {
        return $this->graduationDate;
    }

    public function setGraduationDate(?\DateTimeInterface $graduationDate): self {
        $this->graduationDate = $graduationDate;

        return $this;
    }

    public function getExpectedGraduationDate(): ?\DateTimeInterface {
        if ($this->graduationDate) {
            return $this->graduationDate;
        } else {
            $graduationDate = new \DateTime();
            $graduationDate->setDate($this->getStudentGroup()->getGraduationYear(), 6, 28);
            return $graduationDate;
        }
    }

}
