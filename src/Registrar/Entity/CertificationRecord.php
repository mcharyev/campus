<?php

namespace App\Registrar\Entity;

use App\Entity\Region;
use App\Entity\Country;
use App\Entity\Department;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Registrar\Repository\CertificationRecordRepository")
 */
class CertificationRecord {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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
     * @ORM\Column(type="integer")
     */
    private $registrationNumber;

    /**
     * @ORM\Column(type="smallint")
     */
    private $applicationYear;

    /**
     * @ORM\Column(type="smallint")
     */
    private $applicationMonth;

    /**
     * @ORM\Column(type="smallint")
     */
    private $certificationYear;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $certificationMonth;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $protocolNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Region")
     * @ORM\JoinColumn(nullable=false)
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $propertyType;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $tags;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    public function getId(): ?int {
        return $this->id;
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

    public function getPatronym(): ?string {
        return $this->patronym;
    }

    public function setPatronym(?string $patronym): self {
        $this->patronym = $patronym;

        return $this;
    }

    public function getRegistrationNumber(): ?int {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(int $registrationNumber): self {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getApplicationYear(): ?int {
        return $this->applicationYear;
    }

    public function setApplicationYear(int $applicationYear): self {
        $this->applicationYear = $applicationYear;

        return $this;
    }

    public function getApplicationMonth(): ?int {
        return $this->applicationMonth;
    }

    public function setApplicationMonth(int $applicationMonth): self {
        $this->applicationMonth = $applicationMonth;

        return $this;
    }

    public function getCertificationYear(): ?int {
        return $this->certificationYear;
    }

    public function setCertificationYear(int $certificationYear): self {
        $this->certificationYear = $certificationYear;

        return $this;
    }

    public function getCertificationMonth(): ?int {
        return $this->certificationMonth;
    }

    public function setCertificationMonth(?int $certificationMonth): self {
        $this->certificationMonth = $certificationMonth;

        return $this;
    }

    public function getProtocolNumber(): ?string {
        return $this->protocolNumber;
    }

    public function setProtocolNumber(?string $protocolNumber): self {
        $this->protocolNumber = $protocolNumber;

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

    public function getPropertyType(): ?int {
        return $this->propertyType;
    }

    public function setPropertyType(int $propertyType): self {
        $this->propertyType = $propertyType;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getTags(): ?string {
        return $this->tags;
    }

    public function setTags(?string $tags): self {
        $this->tags = $tags;

        return $this;
    }

    public function getData(): ?array {
        return $this->data;
    }

    public function setData(?array $data): self {
        $this->data = $data;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = $value;
        } else {
            $this->data += array($column => $value);
        }
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

}
