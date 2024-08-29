<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SystemEventRepository")
 */
class SystemEvent {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $type;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $subjectType;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $subjectId;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $objectType;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $objectId;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    public function getId(): ?int {
        return $this->id;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(?int $type): self {
        $this->type = $type;

        return $this;
    }

    public function getSubjectType(): ?int {
        return $this->subjectType;
    }

    public function setSubjectType(?int $subjectType): self {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function getSubjectId(): ?int {
        return $this->subjectId;
    }

    public function setSubjectId(?int $subjectId): self {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function getObjectType(): ?int {
        return $this->objectType;
    }

    public function setObjectType(int $objectType): self {
        $this->objectType = $objectType;

        return $this;
    }

    public function getObjectId(): ?int {
        return $this->objectId;
    }

    public function setObjectId(?int $objectId): self {
        $this->objectId = $objectId;

        return $this;
    }

    public function getData(): ?string {
        return $this->data;
    }

    public function setData(?string $data): self {
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

}
