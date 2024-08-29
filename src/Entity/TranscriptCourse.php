<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TranscriptCourseRepository")
 */
class TranscriptCourse {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $creditType;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $courseType;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $midterm;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $final;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $makeup;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $siwsi;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $courseGrade;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $teacher;

    /**
     * @ORM\Column(type="integer")
     */
    private $studentId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $studentName;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="smallint")
     */
    private $semester;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastUpdater;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $status;

    /**
     * @ORM\Column(type="smallint")
     */
    private $credits;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $courseId;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $groupCode;

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

    public function getCreditType(): ?int {
        return $this->creditType;
    }

    public function setCreditType(int $creditType): self {
        $this->creditType = $creditType;

        return $this;
    }

    public function getCourseType(): ?int {
        return $this->courseType;
    }

    public function setCourseType(int $courseType): self {
        $this->courseType = $courseType;

        return $this;
    }

    public function getMidterm(): ?int {
        return $this->midterm;
    }

    public function setMidterm(int $midterm): self {
        $this->midterm = $midterm;

        return $this;
    }

    public function getFinal(): ?int {
        return $this->final;
    }

    public function setFinal(int $final): self {
        $this->final = $final;

        return $this;
    }

    public function getMakeup(): ?int {
        return $this->makeup;
    }

    public function setMakeup(int $makeup): self {
        $this->makeup = $makeup;

        return $this;
    }

    public function getSiwsi(): ?int {
        return $this->siwsi;
    }

    public function setSiwsi(int $siwsi): self {
        $this->siwsi = $siwsi;

        return $this;
    }

    public function getCourseGrade(): ?int {
        return $this->courseGrade;
    }

    public function setCourseGrade(int $courseGrade): self {
        $this->courseGrade = $courseGrade;

        return $this;
    }

    public function getTeacher(): ?string {
        return $this->teacher;
    }

    public function setTeacher(string $teacher): self {
        $this->teacher = $teacher;

        return $this;
    }

    public function getStudentId(): ?int {
        return $this->studentId;
    }

    public function setStudentId(int $studentId): self {
        $this->studentId = $studentId;

        return $this;
    }

    public function getStudentName(): ?string {
        return $this->studentName;
    }

    public function setStudentName(string $studentName): self {
        $this->studentName = $studentName;

        return $this;
    }

    public function getYear(): ?int {
        return $this->year;
    }

    public function setYear(int $year): self {
        $this->year = $year;

        return $this;
    }

    public function getSemester(): ?int {
        return $this->semester;
    }

    public function setSemester(int $semester): self {
        $this->semester = $semester;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getLastUpdater(): ?string {
        return $this->lastUpdater;
    }

    public function setLastUpdater(string $lastUpdater): self {
        $this->lastUpdater = $lastUpdater;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getCredits(): ?int {
        return $this->credits;
    }

    public function setCredits(int $credits): self {
        $this->credits = $credits;

        return $this;
    }

    public function getFourPointGrade(): ?float {
        $result = 0.00;
        if ($this->courseGrade < 50) {
            $result = 0.00;
        } elseif ($this->courseGrade >= 50 && $this->courseGrade <= 54) {
            $result = 1.00;
        } elseif ($this->courseGrade >= 55 && $this->courseGrade <= 59) {
            $result = 1.33;
        } elseif ($this->courseGrade >= 60 && $this->courseGrade <= 64) {
            $result = 1.67;
        } elseif ($this->courseGrade >= 65 && $this->courseGrade <= 69) {
            $result = 2.00;
        } elseif ($this->courseGrade >= 70 && $this->courseGrade <= 74) {
            $result = 2.33;
        } elseif ($this->courseGrade >= 75 && $this->courseGrade <= 79) {
            $result = 2.67;
        } elseif ($this->courseGrade >= 80 && $this->courseGrade <= 84) {
            $result = 3.00;
        } elseif ($this->courseGrade >= 85 && $this->courseGrade <= 89) {
            $result = 3.33;
        } elseif ($this->courseGrade >= 90 && $this->courseGrade <= 94) {
            $result = 3.67;
        } elseif ($this->courseGrade >= 95 && $this->courseGrade <= 100) {
            $result = 4.00;
        }
        return number_format($result, 2);
    }

    public function getECTSGrade(): ?string {
        $result = "F";
        if ($this->getCredits() == 0) {
            if ($this->courseGrade < 50) {
                $result = "U";
            } else {
                $result = "S";
            }
            return $result;
        }
        if ($this->courseGrade < 50) {
            $result = "F";
        } elseif ($this->courseGrade >= 50 && $this->courseGrade <= 54) {
            $result = "E";
        } elseif ($this->courseGrade >= 55 && $this->courseGrade <= 69) {
            $result = "D";
        } elseif ($this->courseGrade >= 70 && $this->courseGrade <= 79) {
            $result = "C";
        } elseif ($this->courseGrade >= 80 && $this->courseGrade <= 89) {
            $result = "B";
        } elseif ($this->courseGrade >= 90 && $this->courseGrade <= 100) {
            $result = "A";
        }
        return $result;
    }

    public function getFivePointGrade(): ?int {
        $result = 2;
        if ($this->getCredits() == 0) {
            if ($this->courseGrade < 50) {
                $result = 2;
            } else {
                $result = 5;
            }
            return $result;
        }
        if ($this->courseGrade < 50) {
            $result = 2;
        } elseif ($this->courseGrade >= 50 && $this->courseGrade <= 69) {
            $result = 3;
        } elseif ($this->courseGrade >= 70 && $this->courseGrade <= 84) {
            $result = 4;
        } elseif ($this->courseGrade >= 85 && $this->courseGrade <= 100) {
            $result = 5;
        }
        return $result;
    }

    public function getCourseId(): ?int {
        return $this->courseId;
    }

    public function setCourseId(int $courseId): self {
        $this->courseId = $courseId;

        return $this;
    }

    public function getGroupCode(): ?int {
        return $this->groupCode;
    }

    public function setGroupCode(int $groupCode): self {
        $this->groupCode = $groupCode;

        return $this;
    }

    public function calculateCourseGrade(): ?int {
        $midtermCoefficient = 30;
        $makeupCoefficient = 0;
        $finalCoefficient = 45;
        $siwsiCoefficient = 25;
        if ($this->getMakeup() > 0) {
            $finalCoefficient = 0;
            $makeupCoefficient = 45;
        } else {
            $finalCoefficient = 45;
            $makeupCoefficient = 0;
        }
        if (($this->getSiwsi() < 50 || $this->getFinal() < 50) && $this->getMakeup() == 0) {
            $finalCoefficient = 0;
            $makeupCoefficient = 0;
            $siwsiCoefficient = 0;
        }
        $courseGrade = round(($this->getMidterm() * $midtermCoefficient + $this->getFinal() * $finalCoefficient + $this->getMakeup() * $makeupCoefficient + $this->getSiwsi() * $siwsiCoefficient) / 100);
        return $courseGrade;
    }

}
