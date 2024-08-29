<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ElectronicDocumentRepository")
 */
class ElectronicDocument {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $originType;

    /**
     * @ORM\Column(type="smallint")
     */
    private $documentType;

    /**
     * @ORM\Column(type="smallint")
     */
    private $entryType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $originDescription;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $destinationDescription;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSent;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $numberSent;

    /**
     * @ORM\Column(type="text")
     */
    private $signatureAuthor;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $numberReceived;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateReceived;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $attachedFiles;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $linkedDocuments;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $filingCategory;

    public function getId(): ?int {
        return $this->id;
    }

    public function getOriginType(): ?int {
        return $this->originType;
    }

    public function setOriginType(int $originType): self {
        $this->originType = $originType;

        return $this;
    }

    public function getDocumentType(): ?int {
        return $this->documentType;
    }

    public function setDocumentType(int $documentType): self {
        $this->documentType = $documentType;

        return $this;
    }

    public function getEntryType(): ?int {
        return $this->entryType;
    }

    public function setEntryType(int $entryType): self {
        $this->entryType = $entryType;

        return $this;
    }

    public function getOriginDescription(): ?string {
        return $this->originDescription;
    }

    public function setOriginDescription(?string $originDescription): self {
        $this->originDescription = $originDescription;

        return $this;
    }

    public function getDestinationDescription(): ?string {
        return $this->destinationDescription;
    }

    public function setDestinationDescription(?string $destinationDescription): self {
        $this->destinationDescription = $destinationDescription;

        return $this;
    }

    public function getDateSent(): ?\DateTimeInterface {
        return $this->dateSent;
    }

    public function setDateSent(?\DateTimeInterface $dateSent): self {
        $this->dateSent = $dateSent;

        return $this;
    }

    public function getNumberSent(): ?string {
        return $this->numberSent;
    }

    public function setNumberSent(?string $numberSent): self {
        $this->numberSent = $numberSent;

        return $this;
    }

    public function getSignatureAuthor(): ?string {
        return $this->signatureAuthor;
    }

    public function setSignatureAuthor(string $signatureAuthor): self {
        $this->signatureAuthor = $signatureAuthor;

        return $this;
    }

    public function getNumberReceived(): ?string {
        return $this->numberReceived;
    }

    public function setNumberReceived(?string $numberReceived): self {
        $this->numberReceived = $numberReceived;

        return $this;
    }

    public function getData(): ?array {
        return $this->data;
    }

    public function setData(?array $data): self {
        $this->data = $data;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): self {
        $this->title = $title;

        return $this;
    }

    public function getDateReceived(): ?\DateTimeInterface {
        return $this->dateReceived;
    }

    public function setDateReceived(\DateTimeInterface $dateReceived): self {
        $this->dateReceived = $dateReceived;

        return $this;
    }

    public function getDataField(?string $fieldName): ?string {
        if (array_key_exists($fieldName, $this->data)) {
            if (json_decode($this->data[$fieldName]) == null || json_decode($this->data[$fieldName]) == 'null') {
                if ($this->data[$fieldName] == 'null') {
                    return "";
                } else {
                    return $this->data[$fieldName];
                }
            } else {
                return json_decode($this->data[$fieldName]);
            }
        } else {
            return "";
        }
    }

//    public function setDataField($fieldName, $value): self {
//        $this->data[$fieldName] = $value;
//        return $this;
//    }

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = json_encode($value);
        } else {
            $this->data += array($column => json_encode($value));
        }
        return $this;
    }

    public function addFiles(array $addedFiles) {
        $files = explode(",", $this->getAttachedFiles());
        foreach ($addedFiles as $file) {
            $files[] = $file;
        }
        //$files[] = $filename;
        return $this->setAttachedFiles(implode(",", $files));
    }

    public function getFiles() {
        return explode(",", $this->getAttachedFiles());
    }

    public function getAttachedFiles(): ?string
    {
        return $this->attachedFiles;
    }

    public function setAttachedFiles(?string $attachedFiles): self
    {
        $this->attachedFiles = $attachedFiles;

        return $this;
    }

    public function getLinkedDocuments(): ?string
    {
        return $this->linkedDocuments;
    }

    public function setLinkedDocuments(?string $linkedDocuments): self
    {
        $this->linkedDocuments = $linkedDocuments;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getFilingCategory(): ?string
    {
        return $this->filingCategory;
    }

    public function setFilingCategory(?string $filingCategory): self
    {
        $this->filingCategory = $filingCategory;

        return $this;
    }

}
