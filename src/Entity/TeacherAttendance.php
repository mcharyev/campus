<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeacherAttendanceRepository")
 */
class TeacherAttendance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TaughtCourse", inversedBy="teacherAttendances")
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="teacherAttendances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teacher;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="smallint")
     */
    private $session;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="teacherAttendances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Faculty", inversedBy="teacherAttendances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $faculty;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?TaughtCourse
    {
        return $this->course;
    }

    public function setCourse(?TaughtCourse $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSession(): ?int
    {
        return $this->session;
    }

    public function setSession(int $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getClassType(): ?ClassType
    {
        return $this->classType;
    }

    public function setClassType(?ClassType $classType): self
    {
        $this->classType = $classType;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getFaculty(): ?Faculty
    {
        return $this->faculty;
    }

    public function setFaculty(?Faculty $faculty): self
    {
        $this->faculty = $faculty;

        return $this;
    }
}
