<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\WorkColumnEnum;
use App\Enum\ClassTypeEnum;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeacherWorkItemRepository")
 */
class TeacherWorkItem {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DepartmentWorkItem", inversedBy="teacherWorkItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $departmentWorkItem;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
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
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $year;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $semester;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
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
     * @ORM\ManyToOne(targetEntity="App\Entity\TaughtCourse")
     * @ORM\JoinColumn(nullable=true)
     */
    private $taughtCourse;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="teacherWorkItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teacher;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $workload;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="teacherWorkItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReportedWork", mappedBy="workitem")
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $reportedWorks;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeacherWorkSet", inversedBy="teacherWorkItems")
     * @ORM\JoinColumn(nullable=true)
     * @ORM\OrderBy({"department" = "ASC"})
     */
    private $teacherWorkSet;

    public function __construct() {
        $this->reportedWorks = new ArrayCollection();
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

    public function getDepartmentWorkItem(): ?DepartmentWorkItem {
        return $this->departmentWorkItem;
    }

    public function setDepartmentWorkItem(?DepartmentWorkItem $departmentWorkItem): self {
        $this->departmentWorkItem = $departmentWorkItem;

        return $this;
    }

    public function getViewOrder(): ?int {
        return $this->viewOrder;
    }

    public function setViewOrder(?int $viewOrder): self {
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

    public function getTaughtCourse(): ?TaughtCourse {
        return $this->taughtCourse;
    }

    public function setTaughtCourse(?TaughtCourse $taughtCourse): self {
        $this->taughtCourse = $taughtCourse;

        return $this;
    }

    public function getScheduleItems() {
        $scheduleItems = [];
        $courseScheduleItems = $this->getTaughtCourse()->getScheduleItems();
        //echo "Teacher work item get schedule items: id - ".$this->getTaughtCourse()->getId(). " - count:". sizeof($courseScheduleItems)."<br>";
        $addLecture = false;
        $addPractice = false;
        $addLab = false;
        $addSiwsi = false;
        if ($this->getLectureHours() > 0) {
            $addLecture = true;
        }
        if ($this->getPracticeHours() > 0) {
            $addPractice = true;
        }
        if ($this->getLabHours() > 0) {
            $addLab = true;
        }
        if ($this->getSiwsiHours() > 0) {
            $addSiwsi = true;
        }
        foreach ($courseScheduleItems as $courseScheduleItem) {
            $groupIds = explode(",", $courseScheduleItem->getStudentGroups());
            if ($this->groupIdExists($groupIds)) {
                $classType = $courseScheduleItem->getClassType()->getSystemId();
                switch ($classType) {
                    Case ClassTypeEnum::LECTURE:
                        if ($addLecture) {
                            $scheduleItems[] = $courseScheduleItem;
                        }
                        break;
                    Case ClassTypeEnum::PRACTICE:
                    Case ClassTypeEnum::SEMINAR:
                    Case ClassTypeEnum::LANGUAGE:
                        if ($addPractice) {
                            $scheduleItems[] = $courseScheduleItem;
                        }
                        break;
                    Case ClassTypeEnum::LAB:
                        if ($addLab) {
                            $scheduleItems[] = $courseScheduleItem;
                        }
                        break;
                    Case ClassTypeEnum::SIWSI:
                        if ($addSiwsi) {
                            $scheduleItems[] = $courseScheduleItem;
                        }
                        break;
                }
            }
        }
        return $scheduleItems;
    }

    private function groupIdExists($groupIds) {
        $result = false;
        $itemGroupIds = $this->getGroupIds();
        foreach ($groupIds as $groupId) {
            //echo "checking:" . $groupId . " in " . implode(",", $itemGroupIds) . "<br>";
            if (in_array($groupId, $itemGroupIds)) {
                $result = true;
            }
        }
        return $result;
    }

    private function getGroupIds() {
        $groupIds = [];
        $groupCoursePairs = explode(",", $this->getStudentGroups());
        foreach ($groupCoursePairs as $groupCoursePair) {
            $group = explode("-", $groupCoursePair);
            if (!empty($group[0])) {
                $groupIds[] = $group[0];
            }
        }

        return $groupIds;
    }

    public function getTeacher(): ?Teacher {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self {
        $this->teacher = $teacher;

        return $this;
    }

    public function getCohortCount() {
        return $this->data[WorkColumnEnum::COHORT];
    }

    public function getGroupCount() {
        return intval($this->data[WorkColumnEnum::GROUPS]);
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

    public function getLectureHours() {
        return $this->data[WorkColumnEnum::LECTUREACTUAL];
    }

    public function getPracticeHours() {
        return $this->data[WorkColumnEnum::PRACTICEACTUAL];
    }

    public function getLabHours() {
        return $this->data[WorkColumnEnum::LABACTUAL];
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

    public function getTotal() {
        return $this->data[29];
    }

    public function setDataField($column, $value) {
        if ($column > 8) {
            $setValue = intval($value);
        } else {
            $setValue = $value;
        }
        if (array_key_exists($column, $this->data)) {
            $this->data[$column] = $setValue;
        } else {
            $this->data += array($column => $setValue);
        }
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

    public function setGroupLetterCodes(string $groupLetterCodes): self {
        $this->data[WorkColumnEnum::GROUPNAMES] = $groupLetterCodes;
        return $this;
    }

    public function groupLetterCodes(): ?string {
        return $this->data[WorkColumnEnum::GROUPNAMES];
    }

    public function getWorkload(): ?int {
        return $this->workload;
    }

    public function setWorkload(int $workload): self {
        $this->workload = $workload;

        return $this;
    }

    public function getDepartment(): ?Department {
        return $this->department;
    }

    public function setDepartment(?Department $department): self {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection|ReportedWork[]
     */
    public function getReportedWorks(): Collection {
        return $this->reportedWorks;
    }

    public function addReportedWork(ReportedWork $reportedWork): self {
        if (!$this->reportedWorks->contains($reportedWork)) {
            $this->reportedWorks[] = $reportedWork;
            $reportedWork->setWorkitem($this);
        }

        return $this;
    }

    public function removeReportedWork(ReportedWork $reportedWork): self {
        if ($this->reportedWorks->contains($reportedWork)) {
            $this->reportedWorks->removeElement($reportedWork);
            // set the owning side to null (unless already changed)
            if ($reportedWork->getWorkitem() === $this) {
                $reportedWork->setWorkitem(null);
            }
        }

        return $this;
    }

    public function isLastSemester(): bool {
        //echo "CHECK LAST SEMESTER:" . $this->getStudentGroups() . ":" . substr($this->getStudentGroups(), 0, 2) . "<br>";
        if (substr($this->getStudentGroups(), 2, 1) == "5") {
            return ($this->semester == 2 && substr($this->getStudentGroups(), 2, 1) == "5");
        } else
            return ($this->semester == 2 && substr($this->getStudentGroups(), 0, 2) == "15");
    }

    public function getTeacherWorkSet(): ?TeacherWorkSet {
        return $this->teacherWorkSet;
    }

    public function setTeacherWorkSet(?TeacherWorkSet $teacherWorkSet): self {
        $this->teacherWorkSet = $teacherWorkSet;

        return $this;
    }

    public function getIncludeColumn() {
        return intval($this->getDataField('includeColumn'));
    }

}
