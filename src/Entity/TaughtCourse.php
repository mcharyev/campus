<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ClassTypeEnum;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaughtCourseRepository")
 */
class TaughtCourse {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProgramCourse")
     * @ORM\OrderBy({"name_english" = "ASC"})
     */
    private $programCourse;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="taughtCourses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teacher;

    /**
     * @ORM\Column(type="smallint")
     */
    private $year;

    /**
     * @ORM\Column(type="smallint")
     */
    private $semester;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="taughtCourses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ScheduleItem", mappedBy="taughtCourse")
     * @ORM\OrderBy({"session" = "ASC"})
     */
    private $scheduleItems;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $studentGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentAbsence", mappedBy="course")
     */
    private $studentAbsences;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $departmentCode;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeacherAttendance", mappedBy="course")
     */
    private $teacherAttendances;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $courseCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nameEnglish;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $gradingType;

    public function __construct() {
        $this->scheduleItems = new ArrayCollection();
        $this->studentAbsences = new ArrayCollection();
        $this->teacherAttendances = new ArrayCollection();
        $this->scheduleChanges = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getProgramCourse(): ?ProgramCourse {
        return $this->programCourse;
    }

    public function setProgramCourse(?ProgramCourse $programCourse): self {
        $this->programCourse = $programCourse;

        return $this;
    }

    public function getTeacher(): ?Teacher {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self {
        $this->teacher = $teacher;

        return $this;
    }

    public function getTeacherName(): ?string {
        return $this->teacher->getFullname();
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

    public function getDepartment(): ?Department {
        return $this->department;
    }

    public function setDepartment(?Department $department): self {
        $this->department = $department;
        $this->setDepartmentCode(intval($department->getSystemId()));

        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data): self {
        $this->data = $data;

        return $this;
    }

//    public function getDataField(?string $fieldName): ?string {
//        if (array_key_exists($fieldName, $this->data)) {
//            return $this->data[$fieldName];
//        } else {
//            return '';
//        }
//    }

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

    public function isSeminarCombined() {
        return ($this->getDataField('seminar_combined') == 1);
    }

    public function getLectureTopics() {
        $topics = $this->getDataField("lecture_topics");
        if (strlen($topics) > 0) {
            return explode("\r\n", $topics);
        } else {
            return [];
        }
    }

    public function getPracticeTopics() {
        $topics = $this->getDataField("practice_topics");
        if (strlen($topics) > 0) {
            return explode("\r\n", $topics);
        } else {
            return [];
        }
    }

    public function getLabTopics() {
        $topics = $this->getDataField("lab_topics");
        if (strlen($topics) > 0) {
            return explode("\r\n", $topics);
        } else {
            return [];
        }
    }

    public function getStartDate(): ?\DateTimeInterface {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection|ScheduleItem[]
     */
    public function getScheduleItems(): Collection {
        return $this->scheduleItems;
    }

    public function addScheduleItem(ScheduleItem $scheduleItem): self {
        if (!$this->scheduleItems->contains($scheduleItem)) {
            $this->scheduleItems[] = $scheduleItem;
            $scheduleItem->setTaughtCourse($this);
        }

        return $this;
    }

    public function removeScheduleItem(ScheduleItem $scheduleItem): self {
        if ($this->scheduleItems->contains($scheduleItem)) {
            $this->scheduleItems->removeElement($scheduleItem);
            // set the owning side to null (unless already changed)
            if ($scheduleItem->getTaughtCourse() === $this) {
                $scheduleItem->setTaughtCourse(null);
            }
        }

        return $this;
    }

    public function getStudentGroups(): ?string {
        return $this->studentGroups;
    }

    public function setStudentGroups(string $studentGroups): self {
        $this->studentGroups = $studentGroups;

        return $this;
    }

    public function checkTaughtGroup(string $groupSystemId): ?bool {
        $groupSystemIds = explode(",", $this->getStudentGroups());
        return in_array($groupSystemId, $groupSystemIds);
    }

    /**
     * @return Collection|StudentAbsence[]
     */
    public function getStudentAbsences(): Collection {
        return $this->studentAbsences;
    }

    public function addStudentAbsence(StudentAbsence $studentAbsence): self {
        if (!$this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences[] = $studentAbsence;
            $studentAbsence->setCourse($this);
        }

        return $this;
    }

    public function removeStudentAbsence(StudentAbsence $studentAbsence): self {
        if ($this->studentAbsences->contains($studentAbsence)) {
            $this->studentAbsences->removeElement($studentAbsence);
            // set the owning side to null (unless already changed)
            if ($studentAbsence->getCourse() === $this) {
                $studentAbsence->setCourse(null);
            }
        }

        return $this;
    }

    public function getNoteCourseName(): ?string {
        return $this->getDataField('course_name');
    }

    public function getCourseCurriculumYear($year = 2022): ?int {
        if (strlen($this->studentGroups) > 2) {
            //hardcoded
            if ($this->studentGroups == "22511" || $this->studentGroups == "22521") {
                return 1;
            } else {
                $intEntranceYear = intval("20" . substr($this->studentGroups, 0, 2));
                return ($year - $intEntranceYear);
            }
        } else {
            return 0;
        }
    }

    public function getCourseCurriculumSemester(): ?int {
        return ($this->getCourseCurriculumYear() - 1) * 2 + $this->semester;
    }

    public function getDepartmentLetterCode(): ?string {
        //hardcoded - change ids of masters groups
        if ($this->getStudentGroups() == "22511") {
            return "em";
        } elseif ($this->getStudentGroups() == "22521") {
            return "mba";
        } else {
            return $this->getDepartment()->getLetterCode();
        }
    }

    public function setCourseTitle($title) {
        $this->setDataField('course_name', $title);
    }

    public function getFullName(): ?string {
        return $this->getTeacherName() . " - " . $this->getNoteCourseName() . " - " . $this->getStudentGroups() . " - " . $this->getId();
    }

    public function getDepartmentCode(): ?int {
        return $this->departmentCode;
    }

    public function setDepartmentCode(int $departmentCode): self {
        $this->departmentCode = $departmentCode;

        return $this;
    }

    /**
     * @return Collection|TeacherAttendance[]
     */
    public function getTeacherAttendances(): Collection {
        return $this->teacherAttendances;
    }

    public function addTeacherAttendance(TeacherAttendance $teacherAttendance): self {
        if (!$this->teacherAttendances->contains($teacherAttendance)) {
            $this->teacherAttendances[] = $teacherAttendance;
            $teacherAttendance->setCourse($this);
        }

        return $this;
    }

    public function removeTeacherAttendance(TeacherAttendance $teacherAttendance): self {
        if ($this->teacherAttendances->contains($teacherAttendance)) {
            $this->teacherAttendances->removeElement($teacherAttendance);
            // set the owning side to null (unless already changed)
            if ($teacherAttendance->getCourse() === $this) {
                $teacherAttendance->setCourse(null);
            }
        }

        return $this;
    }

    public function getCourseCode(): ?string {
        return $this->courseCode;
    }

    public function setCourseCode(?string $courseCode): self {
        $this->courseCode = $courseCode;

        return $this;
    }

    public function getNameEnglish(): ?string {
        return $this->nameEnglish;
    }

    public function setNameEnglish(?string $nameEnglish): self {
        $this->nameEnglish = $nameEnglish;

        return $this;
    }

    public function getMainTeacher(): ?Teacher {
        $scheduleItems = $this->getScheduleItems();
        foreach ($scheduleItems as $scheduleItem) {
            if ($scheduleItem->getClassType()->getSystemId() == ClassTypeEnum::LECTURE) {
                return $scheduleItem->getTeacher();
            }
            if ($scheduleItem->getClassType()->getSystemId() == ClassTypeEnum::LANGUAGE) {
                return $scheduleItem->getTeacher();
            }
        }
        if ($this->getDepartmentLetterCode() == 'LANG') {
            return $this->getTeacher();
        }
        return null;
    }

    public function getGradingType(): ?int
    {
        return $this->gradingType;
    }

    public function setGradingType(int $gradingType): self
    {
        $this->gradingType = $gradingType;

        return $this;
    }

}
