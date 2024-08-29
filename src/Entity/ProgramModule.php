<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProgramModuleRepository")
 */
class ProgramModule
{
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
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nameTurkmen;

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

    public function setLetterCode(string $letterCode): self
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

    public function setNameTurkmen(string $nameTurkmen): self
    {
        $this->nameTurkmen = $nameTurkmen;

        return $this;
    }
}
