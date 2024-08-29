<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 */
class Country {

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
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $letterCode;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint", nullable=false, options={"default": 1})
     */
    private $status;

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

    public function setNameTurkmen(?string $nameTurkmen): self {
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

}
