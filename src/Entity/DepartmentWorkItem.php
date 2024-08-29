<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\WorkColumnEnum;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DepartmentWorkItemRepository")
 */
class DepartmentWorkItem {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="departmentWorkItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\Column(type="smallint")
     */
    private $viewOrder;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $studentGroups;

    /**
     * @ORM\Column(type="smallint")
     */
    private $year;

    /**
     * @ORM\Column(type="smallint")
     */
    private $semester;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $studentCount;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherWorkItem", mappedBy="departmentWorkItem")
     */
    private $teacherWorkItems;

    public function __construct() {
        $this->teacherWorkItems = new ArrayCollection();
        $data = [
            WorkColumnEnum::TITLE => '',
            WorkColumnEnum::GROUPNAMES => '',
            WorkColumnEnum::STUDYYEAR => 0,
            WorkColumnEnum::STUDENTCOUNT => 0,
            WorkColumnEnum::COHORT => 1,
            WorkColumnEnum::GROUPS => 1,
            WorkColumnEnum::SUBGROUPS => 0,
            WorkColumnEnum::LECTUREPLAN => 0,
            WorkColumnEnum::LECTUREACTUAL => 0,
            WorkColumnEnum::PRACTICEPLAN => 0,
            WorkColumnEnum::PRACTICEACTUAL => 0,
            WorkColumnEnum::LABPLAN => 0,
            WorkColumnEnum::LABACTUAL => 0,
            WorkColumnEnum::CONSULTATION => 0,
            WorkColumnEnum::NONCREDIT => 0,
            WorkColumnEnum::MIDTERMEXAM => 0,
            WorkColumnEnum::FINALEXAM => 0,
            WorkColumnEnum::STATEEXAM => 0,
            WorkColumnEnum::SIWSI => 0,
            WorkColumnEnum::CUSW => 0,
            WorkColumnEnum::INTERNSHIP => 0,
            WorkColumnEnum::THESISADVISING => 0,
            WorkColumnEnum::THESISEXAM => 0,
            WorkColumnEnum::COMPETITION => 0,
            WorkColumnEnum::CLASSOBSERVATION => 0,
            WorkColumnEnum::ADMINISTRATION => 0,
            WorkColumnEnum::THESISREVIEW => 0,
            WorkColumnEnum::TOTAL => 0,
        ];
        $this->setData($data);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getDepartment(): ?Department {
        return $this->department;
    }

    public function setDepartment(?Department $department): self {
        $this->department = $department;

        return $this;
    }

    public function getViewOrder(): ?int {
        return $this->viewOrder;
    }

    public function setViewOrder(int $viewOrder): self {
        $this->viewOrder = $viewOrder;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): self {
        $this->title = $title;

        return $this;
    }

    public function getStudentGroups(): ?string {
        return $this->studentGroups;
    }

    public function setStudentGroups(?string $studentGroups): self {
        $this->studentGroups = $studentGroups;

        return $this;
    }

//    public function getStudentCount(): int {
//        $totalCount = 0;
//        foreach ($this->studentGroups as $studentGroup) {
//            $totalCount += $studentGroup->getTotalStudentCount();
//        }
//        return $totalCount;
//    }

    public function getYear(): ?int {
        return $this->year;
    }

    public function setYear(int $year): self {
        $this->year = $year;

        return $this;
    }

    public function getSemester(): ?int {
        return $this->semester;
    }

    public function setSemester(int $semester): self {
        $this->semester = $semester;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getData(): ?array {
        return $this->data;
    }

    public function setData(array $data): self {
        $this->data = $data;

        return $this;
    }

    public function getCohortCount() {
        return $this->data[WorkColumnEnum::COHORT];
    }

    public function getGroupCount() {
        return $this->data[WorkColumnEnum::GROUPS];
    }

    public function getSubgroupCount() {
        return $this->data[WorkColumnEnum::SUBGROUPS];
    }

    public function getConsultationHours() {
        return $this->data[WorkColumnEnum::CONSULTATION];
    }

    public function getNoncreditHours() {
        return $this->data[WorkColumnEnum::NONCREDIT];
    }

    public function getMidtermHours() {
        return $this->data[WorkColumnEnum::MIDTERMEXAM];
    }

    public function getFinalHours() {
        return $this->data[WorkColumnEnum::FINALEXAM];
    }

    public function getStateExamHours() {
        return $this->data[WorkColumnEnum::STATEEXAM];
    }

    public function getSiwsiHours() {
        return $this->data[WorkColumnEnum::SIWSI];
    }

    public function getCuswHours() {
        return $this->data[WorkColumnEnum::CUSW];
    }

    public function getInternshipHours() {
        return $this->data[WorkColumnEnum::INTERNSHIP];
    }

    public function getThesisAdvisingHours() {
        return $this->data[WorkColumnEnum::THESISADVISING];
    }

    public function getThesisExamHours() {
        return $this->data[WorkColumnEnum::THESISEXAM];
    }

    public function getCompetitionHours() {
        return $this->data[WorkColumnEnum::COMPETITION];
    }

    public function getClassObservationHours() {
        return $this->data[WorkColumnEnum::CLASSOBSERVATION];
    }

    public function getAdministrationHours() {
        return $this->data[WorkColumnEnum::ADMINISTRATION];
    }

    public function getThesisReviewHours() {
        return $this->data[WorkColumnEnum::THESISREVIEW];
    }

    public function setDataField($column, $value) {
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = $value;
        } else {
            $this->data += array($column => $value);
        }
        return $this;
    }

    public function getGroupNames(): string {
        if (array_key_exists(WorkColumnEnum::GROUPNAMES, $this->data)) {
            return $this->data[WorkColumnEnum::GROUPNAMES];
        } else {
            return '';
        }
    }

    public function calculateLoad(): self {
//        WorkColumnEnum::STUDYYEAR => 0,
//                WorkColumnEnum::STUDENTCOUNT => 0,
//                WorkColumnEnum::COHORT => 1,
//                WorkColumnEnum::GROUPS => 0,
//                WorkColumnEnum::SUBGROUPS => 0,
//                WorkColumnEnum::CONSULTATION => 0,
//                WorkColumnEnum::NONCREDIT => 0,
//                WorkColumnEnum::MIDTERMEXAM => 0,
//                WorkColumnEnum::FINALEXAM => 0,
//                WorkColumnEnum::STATEEXAM => 0,
//                WorkColumnEnum::SIWSI => 0,
//                WorkColumnEnum::CUSW => 0,
//                WorkColumnEnum::INTERNSHIP => 0,
//                WorkColumnEnum::THESISADVISING => 0,
//                WorkColumnEnum::THESISEXAM => 0,
//                WorkColumnEnum::COMPETITION => 0,
//                WorkColumnEnum::CLASSOBSERVATION => 0,
//                WorkColumnEnum::ADMINISTRATION => 0,
//                WorkColumnEnum::THESISREVIEW => 0,
//                WorkColumnEnum::TOTAL => 0,
    }

    public function getStudentCount(): ?int {
        return $this->studentCount;
    }

    public function setStudentCount(int $studentCount): self {
        $this->studentCount = $studentCount;

        return $this;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(int $type): self {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|TeacherWorkItem[]
     */
    public function getTeacherWorkItems(): Collection {
        return $this->teacherWorkItems;
    }

    public function addTeacherWorkItem(TeacherWorkItem $teacherWorkItem): self {
        if (!$this->teacherWorkItems->contains($teacherWorkItem)) {
            $this->teacherWorkItems[] = $teacherWorkItem;
            $teacherWorkItem->setDepartmentWorkItem($this);
        }

        return $this;
    }

    public function removeTeacherWorkItem(TeacherWorkItem $teacherWorkItem): self {
        if ($this->teacherWorkItems->contains($teacherWorkItem)) {
            $this->teacherWorkItems->removeElement($teacherWorkItem);
            // set the owning side to null (unless already changed)
            if ($teacherWorkItem->getDepartmentWorkItem() === $this) {
                $teacherWorkItem->setDepartmentWorkItem(null);
            }
        }

        return $this;
    }

    public function checkTaughtGroup(string $groupSystemId): ?bool {
        if ($this->getStudentGroups()) {
            $groupCourseIds = explode(",", $this->getStudentGroups());
            foreach ($groupCourseIds as $groupCourseId) {
                if (strpos($groupCourseId, $groupSystemId) !== false) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }

}
