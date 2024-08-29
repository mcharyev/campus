<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HostelRoomRepository")
 */
class HostelRoom
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Hostel", inversedBy="hostelRooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $hostel;

    /**
     * @ORM\Column(type="smallint")
     */
    private $floor;

    /**
     * @ORM\Column(type="integer")
     */
    private $roomNumber;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $roomName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="hostelRooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instructor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EnrolledStudent", mappedBy="hostelRoom")
     */
    private $enrolledStudents;

    public function __construct()
    {
        $this->enrolledStudents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHostel(): ?Hostel
    {
        return $this->hostel;
    }

    public function setHostel(?Hostel $hostel): self
    {
        $this->hostel = $hostel;

        return $this;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(int $floor): self
    {
        $this->floor = $floor;

        return $this;
    }

    public function getRoomNumber(): ?int
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(int $roomNumber): self
    {
        $this->roomNumber = $roomNumber;

        return $this;
    }

    public function getRoomName(): ?string
    {
        return $this->roomName;
    }

    public function setRoomName(string $roomName): self
    {
        $this->roomName = $roomName;

        return $this;
    }
    
    public function getFullName(): string {
        return $this->getHostel()->getNameEnglish()." - ".$this->getRoomName();
    }

    public function getInstructor(): ?Teacher
    {
        return $this->instructor;
    }

    public function setInstructor(?Teacher $instructor): self
    {
        $this->instructor = $instructor;

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

    /**
     * @return Collection|EnrolledStudent[]
     */
    public function getEnrolledStudents(): Collection
    {
        return $this->enrolledStudents;
    }

    public function addEnrolledStudent(EnrolledStudent $enrolledStudent): self
    {
        if (!$this->enrolledStudents->contains($enrolledStudent)) {
            $this->enrolledStudents[] = $enrolledStudent;
            $enrolledStudent->setHostelRoom($this);
        }

        return $this;
    }

    public function removeEnrolledStudent(EnrolledStudent $enrolledStudent): self
    {
        if ($this->enrolledStudents->contains($enrolledStudent)) {
            $this->enrolledStudents->removeElement($enrolledStudent);
            // set the owning side to null (unless already changed)
            if ($enrolledStudent->getHostelRoom() === $this) {
                $enrolledStudent->setHostelRoom(null);
            }
        }

        return $this;
    }
}
