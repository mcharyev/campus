<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClassroomRepository")
 */
class Classroom
{
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
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $nameTurkmen;

    /**
     * @ORM\Column(type="smallint")
     */
    private $capacity;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $status;

    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="string", length=15, nullable=true, options={"default": 0})
     */
    private $letterCode;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $floor;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $data;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CampusBuilding", inversedBy="classrooms")
     */
    private $campusBuilding;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClassroomType", inversedBy="classrooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ScheduleChange", mappedBy="classroom")
     */
    private $scheduleChanges;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $scheduleName;

    public function __construct()
    {
        $this->scheduleChanges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSystemId(): ?int
    {
        return $this->systemId;
    }

    public function setSystemId(?int $systemId): self
    {
        $this->systemId = $systemId;

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

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
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

    public function getLetterCode(): ?string
    {
        return $this->letterCode;
    }

    public function setLetterCode(?string $letterCode): self
    {
        $this->letterCode = $letterCode;

        return $this;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(?int $floor): self
    {
        $this->floor = $floor;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCampusBuilding(): ?CampusBuilding
    {
        return $this->campusBuilding;
    }

    public function setCampusBuilding(?CampusBuilding $campusBuilding): self
    {
        $this->campusBuilding = $campusBuilding;

        return $this;
    }

    public function getType(): ?ClassroomType
    {
        return $this->type;
    }

    public function setType(?ClassroomType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|ScheduleChange[]
     */
    public function getScheduleChanges(): Collection
    {
        return $this->scheduleChanges;
    }

    public function addScheduleChange(ScheduleChange $scheduleChange): self
    {
        if (!$this->scheduleChanges->contains($scheduleChange)) {
            $this->scheduleChanges[] = $scheduleChange;
            $scheduleChange->setClassroom($this);
        }

        return $this;
    }

    public function removeScheduleChange(ScheduleChange $scheduleChange): self
    {
        if ($this->scheduleChanges->contains($scheduleChange)) {
            $this->scheduleChanges->removeElement($scheduleChange);
            // set the owning side to null (unless already changed)
            if ($scheduleChange->getClassroom() === $this) {
                $scheduleChange->setClassroom(null);
            }
        }

        return $this;
    }

    public function getScheduleName(): ?string
    {
        return $this->scheduleName;
    }

    public function setScheduleName(?string $scheduleName): self
    {
        $this->scheduleName = $scheduleName;

        return $this;
    }
}
