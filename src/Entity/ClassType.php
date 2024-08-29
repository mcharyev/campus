<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\ClassTypeEnum;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClassTypeRepository")
 */
class ClassType {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $systemId;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

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

    public function getLetterCode(): ?string {
        return $this->letterCode;
    }

    public function setLetterCode(string $letterCode): self {
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

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(?int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getHours(): int {
        switch ($this->getSystemId()) {
            Case ClassTypeEnum::LECTURE:
            Case ClassTypeEnum::PRACTICE:
            Case ClassTypeEnum::SEMINAR:
            Case ClassTypeEnum::LAB:
                return 2;
            Case ClassTypeEnum::SIWSI:
            Case ClassTypeEnum::LANGUAGE:
                return 1;
        }
        return 0;
    }

}
