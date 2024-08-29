<?php

namespace App\Entity;

use App\Library\Entity\LibraryAccess;
use App\Library\Entity\LibraryUnit;
use App\Library\Entity\LibraryLoan;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\UserTypeEnum;
use App\Entity\Teacher;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User implements UserInterface, \Serializable {

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, unique=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=50)
     */
    private $username;

    /**
     * @var integers
     *
     * @ORM\Column(type="integer")
     */
    private $systemId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200)
     */
    private $password;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Library\Entity\LibraryUnit", mappedBy="manager")
     */
    private $libraryUnits;

    /**
     * @ORM\OneToMany(targetEntity="App\Library\Entity\LibraryLoan", mappedBy="user")
     */
    private $loans;

    /**
     * @ORM\OneToMany(targetEntity="App\Library\Entity\LibraryAccess", mappedBy="user")
     */
    private $libraryAccesses;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    public function __construct()
    {
        $this->libraryUnits = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->libraryAccesses = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setFirstname(string $firstname): void {
        $this->firstname = $firstname;
    }

    public function getFirstname(): ?string {
        return $this->firstname;
    }

    public function setLastname(string $lastname): void {
        $this->lastname = $lastname;
    }

    public function getLastname(): ?string {
        return $this->lastname;
    }

    public function getFullName(): ?string {
        return $this->firstname . " " . $this->lastname;
    }

    public function getUsername(): ?string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getSystemId(): ?string {
        return $this->systemId;
    }

    public function setSystemId(string $system_id): void {
        $this->systemId = $system_id;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(?int $type): void {
        $this->type = $type;
    }

    public function getTeacher(): Teacher {
        if ($this->getType() == UserTypeEnum::USER_TEACHER) {
            
        }
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array {
        $roles = $this->roles;
        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): void {
        $this->roles = $roles;
    }

    public function hasRole(string $role): bool {
        $hasAccess = in_array($role, $this->getRoles());
        return $hasAccess;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void {
        // if you had a plainPassword property, you'd nullify it here
        // $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return serialize([$this->id, $this->username, $this->password]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @return Collection|LibraryUnit[]
     */
    public function getLibraryUnits(): Collection
    {
        return $this->libraryUnits;
    }

    public function addLibraryUnit(LibraryUnit $libraryUnit): self
    {
        if (!$this->libraryUnits->contains($libraryUnit)) {
            $this->libraryUnits[] = $libraryUnit;
            $libraryUnit->setManager($this);
        }

        return $this;
    }

    public function removeLibraryUnit(LibraryUnit $libraryUnit): self
    {
        if ($this->libraryUnits->contains($libraryUnit)) {
            $this->libraryUnits->removeElement($libraryUnit);
            // set the owning side to null (unless already changed)
            if ($libraryUnit->getManager() === $this) {
                $libraryUnit->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LibraryLoan[]
     */
    public function getLibraryLoans(): Collection
    {
        return $this->libraryLoans;
    }

    public function addLibraryLoan(LibraryLoan $libraryLoan): self
    {
        if (!$this->libraryLoans->contains($libraryLoan)) {
            $this->libraryLoans[] = $libraryLoan;
            $libraryLoan->setUser($this);
        }

        return $this;
    }

    public function removeLibraryLoan(LibraryLoan $libraryLoan): self
    {
        if ($this->libraryLoans->contains($libraryLoan)) {
            $this->libraryLoans->removeElement($libraryLoan);
            // set the owning side to null (unless already changed)
            if ($libraryLoan->getUser() === $this) {
                $libraryLoan->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LibraryAccess[]
     */
    public function getLibraryAccesses(): Collection
    {
        return $this->libraryAccesses;
    }

    public function addLibraryAccess(LibraryAccess $libraryAccess): self
    {
        if (!$this->libraryAccesses->contains($libraryAccess)) {
            $this->libraryAccesses[] = $libraryAccess;
            $libraryAccess->setUser($this);
        }

        return $this;
    }

    public function removeLibraryAccess(LibraryAccess $libraryAccess): self
    {
        if ($this->libraryAccesses->contains($libraryAccess)) {
            $this->libraryAccesses->removeElement($libraryAccess);
            // set the owning side to null (unless already changed)
            if ($libraryAccess->getUser() === $this) {
                $libraryAccess->setUser(null);
            }
        }

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

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

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = json_encode($value);
        } else {
            $this->data += array($column => json_encode($value));
        }
        return $this;
    }

}
