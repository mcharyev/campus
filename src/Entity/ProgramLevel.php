<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProgramLevelRepository")
 */
class ProgramLevel
{
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
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="string", length=35, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=35, nullable=true)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudyProgram", mappedBy="programLevel")
     */
    private $studyPrograms;

    public function __construct()
    {
        $this->studyPrograms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSystemId(): ?int
    {
        return $this->systemId;
    }

    public function setSystemId(int $systemId): self
    {
        $this->systemId = $systemId;

        return $this;
    }

    public function getLetterCode(): ?string
    {
        return $this->letterCode;
    }

    public function setLetterCode(?string $letterCode): self
    {
        $this->letterCode = $letterCode;

        return $this;
    }

    public function getNameEnglish(): ?string
    {
        return $this->nameEnglish;
    }

    public function setNameEnglish(?string $nameEnglish): self
    {
        $this->nameEnglish = $nameEnglish;

        return $this;
    }

    public function getNameTurkmen(): ?string
    {
        return $this->nameTurkmen;
    }

    public function setNameTurkmen(?string $nameTurkmen): self
    {
        $this->nameTurkmen = $nameTurkmen;

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

    /**
     * @return Collection|StudyProgram[]
     */
    public function getStudyPrograms(): Collection
    {
        return $this->studyPrograms;
    }

    public function addStudyProgram(StudyProgram $studyProgram): self
    {
        if (!$this->studyPrograms->contains($studyProgram)) {
            $this->studyPrograms[] = $studyProgram;
            $studyProgram->setProgramLevel($this);
        }

        return $this;
    }

    public function removeStudyProgram(StudyProgram $studyProgram): self
    {
        if ($this->studyPrograms->contains($studyProgram)) {
            $this->studyPrograms->removeElement($studyProgram);
            // set the owning side to null (unless already changed)
            if ($studyProgram->getProgramLevel() === $this) {
                $studyProgram->setProgramLevel(null);
            }
        }

        return $this;
    }
}
