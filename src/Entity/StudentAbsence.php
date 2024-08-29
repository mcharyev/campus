<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentAbsenceRepository")
 */
class StudentAbsence {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EnrolledStudent", inversedBy="studentAbsences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TaughtCourse", inversedBy="studentAbsences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $course;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $session;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="studentAbsences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classType;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $authorApprovalDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deanApprovalDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="studentAbsences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Faculty", inversedBy="studentAbsences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $faculty;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="studentAbsences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $studentGroup;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $excuseNote;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $recoverNote;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $excuseStatus;

    public function getId(): ?int {
        return $this->id;
    }

    public function getStudent(): ?EnrolledStudent {
        return $this->student;
    }

    public function setStudent(?EnrolledStudent $student): self {
        $this->student = $student;

        return $this;
    }

    public function getCourse(): ?TaughtCourse {
        return $this->course;
    }

    public function setCourse(?TaughtCourse $course): self {
        $this->course = $course;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self {
        $this->date = $date;

        return $this;
    }

    public function getSession(): ?int {
        return $this->session;
    }

    public function setSession(?int $session): self {
        $this->session = $session;

        return $this;
    }

    public function getAuthor(): ?Teacher {
        return $this->author;
    }

    public function setAuthor(?Teacher $author): self {
        $this->author = $author;

        return $this;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function setNote(?string $note): self {
        $this->note = $note;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }
    
    public function getDateCreated(): ?\DateTimeInterface {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getClassType(): ?ClassType {
        return $this->classType;
    }

    public function setClassType(?ClassType $classType): self {
        $this->classType = $classType;

        return $this;
    }

    public function getAuthorApprovalDate(): ?\DateTimeInterface {
        return $this->authorApprovalDate;
    }

    public function setAuthorApprovalDate(?\DateTimeInterface $authorApprovalDate): self {
        $this->authorApprovalDate = $authorApprovalDate;

        return $this;
    }

    public function getDeanApprovalDate(): ?\DateTimeInterface {
        return $this->deanApprovalDate;
    }

    public function setDeanApprovalDate(?\DateTimeInterface $deanApprovalDate): self {
        $this->deanApprovalDate = $deanApprovalDate;

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
        return $this->faculty;
    }

    public function setFaculty(?Faculty $faculty): self {
        $this->faculty = $faculty;

        return $this;
    }

    public function getStudentGroup(): ?Group {
        return $this->studentGroup;
    }

    public function setStudentGroup(?Group $studentGroup): self {
        $this->studentGroup = $studentGroup;

        return $this;
    }

    public function getExcuseNote(): ?string {
        return $this->excuseNote;
    }

    public function setExcuseNote(?string $excuseNote): self {
        $this->excuseNote = $excuseNote;

        return $this;
    }

    public function getRecoverNote(): ?string {
        return $this->recoverNote;
    }

    public function setRecoverNote(?string $recoverNote): self {
        $this->recoverNote = $recoverNote;

        return $this;
    }

    public function getExcuseStatus(): ?int {
        return $this->excuseStatus;
    }

    public function setExcuseStatus(int $excuseStatus): self {
        $this->excuseStatus = $excuseStatus;

        return $this;
    }

}
