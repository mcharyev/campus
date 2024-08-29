<?php

namespace App\Controller\Workload;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Department;
use App\Entity\Group;
use App\Entity\ScheduleItem;
use App\Entity\TaughtCourse;
use App\Entity\Teacher;
use App\Entity\TeacherWorkItem;
use App\Entity\ScheduleChange;
use App\Service\SystemEventManager;
use App\Enum\WorkColumnEnum;
use App\Enum\WorkRowEnum;
use App\Enum\ClassTypeEnum;
use App\Enum\IncludeColumnEnum;
use App\Service\CourseDaysManager;
use App\Service\SystemInfoManager;
use App\Controller\Workload\WorkColumnsArray;

class DepartmentWorkReportController extends AbstractController {

    private $systemEventManager;
    private $courseDaysManager;
    private $systemInfoManager;
    private $workColumnsManager;

    public function __construct(SystemEventManager $systemEventManager,
            CourseDaysManager $courseDaysManager, SystemInfoManager $systemInfoManager,
            WorkColumnsArray $workColumnsManager) {
        $this->systemEventManager = $systemEventManager;
        $this->courseDaysManager = $courseDaysManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->workColumnsManager = $workColumnsManager;
    }

    private function getWorkitemDataForMonth(TeacherWorkItem $teacherWorkItem, int $year, int $month, $empty_sum) {

        $semesterBeginDate = new \DateTime($this->systemInfoManager->getSemesterBeginDate($this->systemInfoManager->getCurrentSemester()));
        $semesterEndDate = new \DateTime($this->systemInfoManager->getSemesterEndDate($this->systemInfoManager->getCurrentSemester()));
        $semesterFinalEndDate = new \DateTime($this->systemInfoManager->getSemesterFinalEndDate($this->systemInfoManager->getCurrentSemester()));
        $semesterFourthYearEndDate = new \DateTime($this->systemInfoManager->getSemesterFourthYearEndDate($this->systemInfoManager->getCurrentSemester()));
        if ($teacherWorkItem->getDepartment()->getSystemId() == 66) {
            $semesterBeginDate = new \DateTime($this->systemInfoManager->getTrimesterBeginDate($this->systemInfoManager->getCurrentTrimester()));
            $semesterEndDate = new \DateTime($this->systemInfoManager->getTrimesterEndDate($this->systemInfoManager->getCurrentTrimester()));
            $semesterFinalEndDate = new \DateTime($this->systemInfoManager->getTrimesterFinalEndDate($this->systemInfoManager->getCurrentTrimester()));
        }
        $date1 = new \DateTime();
        $date1->setDate($year, $month, 1);
        if ($date1 < $semesterBeginDate) {
            $date1 = $semesterBeginDate;
        }
        $beginDate = $date1->format("Y-m-d");
        $date2 = clone $date1;
        $date2->modify('first day of next month');
        if ($date2 > $semesterEndDate) {
            $date2 = $semesterEndDate;
        }
        if ($teacherWorkItem->isLastSemester()) {
//            echo "LAST SEMESTER: YES<br>";
            if ($date2 > $semesterFourthYearEndDate) {
                $date2 = $semesterFourthYearEndDate;
            }
        }
        $includeColumn = intval($teacherWorkItem->getDataField('includeColumn'));
        $taughtCourse = $teacherWorkItem->getTaughtCourse();
        $endDate = $date2->format("Y-m-d");
//        echo $beginDate . ":" . $endDate . "<br>";
        $courseData = $empty_sum;
        $lectureCounted = false;
        $divider = 1;
        if ($taughtCourse) {
            $groupIds = explode(",", $teacherWorkItem->getStudentGroups());
//            $groupIds = explode(",", $taughtCourse->getStudentGroups());
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $g = 0;
//            echo "<br><br>";
            foreach ($groupIds as $groupId) {
                $group = $groupRepository->findOneBy(['systemId' => $groupId]);
                if (!empty($group)) {
//                    if($includeColumn == IncludeColumnEnum::PRACTICE_ONLY && $groupId==)
//                    echo $group->getLetterCode() . "<br>";
                    $courseDays = $this->courseDaysManager->getGroupCourseDays($group, $taughtCourse, 'coursemonth', $beginDate, $endDate);
                    foreach ($courseDays as $courseDay) {
                        if ($courseDay['holiday'] == null && $courseDay['changed'] == false) {
//                            if ($teacherWorkItem->getId() == 1256) {
//                                echo "Type: " . ClassTypeEnum::getTypeName($courseDay['classTypeId']) . " ";
//                                echo $courseDay['date']->format('d-m-Y') . "->" . $courseDay['changed'] . "->" . "null<br>";
//                            }
                            $count = true;
                            if ($courseDay['changedDay']) { //if it is a changed schedule day
                                if ($courseDay['changedDay']->getNewTeacher() != $teacherWorkItem->getTeacher()) {
                                    $count = false;
                                }
                            }

                            if ($count) {
                                if ($courseDay['classTypeId'] == ClassTypeEnum::LECTURE) {
                                    $courseData[WorkColumnEnum::LECTUREACTUAL] += 2;
                                } elseif ($courseDay['classTypeId'] == ClassTypeEnum::PRACTICE || $courseDay['classTypeId'] == ClassTypeEnum::SEMINAR) {
                                    //echo "includeColumn:" . $includeColumn . "<br>";
                                    $courseData[WorkColumnEnum::PRACTICEACTUAL] += 2;
                                } elseif ($courseDay['classTypeId'] == ClassTypeEnum::LAB) {
                                    $courseData[WorkColumnEnum::LABACTUAL] += 2;
                                } elseif ($courseDay['classTypeId'] == ClassTypeEnum::SIWSI) {
                                    $courseData[WorkColumnEnum::SIWSI] += 1;
                                } elseif ($courseDay['classTypeId'] == ClassTypeEnum::LANGUAGE) {
                                    $courseData[WorkColumnEnum::PRACTICEACTUAL] += 1;
                                }
                            }
                        }
                    }

                    $scheduleChangeRepository = $this->getDoctrine()->getRepository(ScheduleChange::class);
                    $scheduleChanges = $scheduleChangeRepository->findAll();
                    foreach ($scheduleChanges as $scheduleChange) {
                        if ($scheduleChange->getScheduleItem()->getTaughtCourse() == $taughtCourse) {
                            $count = false;
                            if ($scheduleChange->getNewTeacher() != $taughtCourse->getTeacher()) {
                                if ($scheduleChange->getNewDate() >= $date1 && $scheduleChange->getNewDate() <= $date2) {
                                    $count = true;
                                }
                                if ($count) {
                                    $classTypeId = $scheduleChange->getClassType()->getSystemId();
                                    if ($classTypeId == ClassTypeEnum::LECTURE) {
                                        $courseData[WorkColumnEnum::LECTUREACTUAL] += 2;
                                    } elseif ($classTypeId == ClassTypeEnum::PRACTICE || $courseDay['classTypeId'] == ClassTypeEnum::SEMINAR) {
                                        //echo "includeColumn:" . $includeColumn . "<br>";
                                        $courseData[WorkColumnEnum::PRACTICEACTUAL] += 2;
                                    } elseif ($classTypeId == ClassTypeEnum::LAB) {
                                        $courseData[WorkColumnEnum::LABACTUAL] += 2;
                                    } elseif ($classTypeId == ClassTypeEnum::SIWSI) {
                                        $courseData[WorkColumnEnum::SIWSI] += 1;
                                    } elseif ($classTypeId == ClassTypeEnum::LANGUAGE) {
                                        $courseData[WorkColumnEnum::PRACTICEACTUAL] += 1;
                                    }
                                }
                            }
                        }
                    }
                }
                $g++;
            }

            $courseData[WorkColumnEnum::LECTUREACTUAL] = $courseData[WorkColumnEnum::LECTUREACTUAL] / $g;
            $courseData[WorkColumnEnum::SIWSI] = $courseData[WorkColumnEnum::SIWSI] / $g;
            if ($taughtCourse->isSeminarCombined()) {
                $courseData[WorkColumnEnum::PRACTICEACTUAL] = $courseData[WorkColumnEnum::PRACTICEACTUAL] / $g;
                $courseData[WorkColumnEnum::LABACTUAL] = $courseData[WorkColumnEnum::LABACTUAL] / $g;
            }
            //$divider = $this->getDivider($taughtCourse);
        }

        if ($includeColumn == IncludeColumnEnum::LECTURE_ONLY) {
            $courseData[WorkColumnEnum::PRACTICEACTUAL] = 0;
            $courseData[WorkColumnEnum::LABACTUAL] = 0;
        }
        if ($includeColumn == IncludeColumnEnum::PRACTICE_ONLY) {
            $courseData[WorkColumnEnum::LECTUREACTUAL] = 0;
            $courseData[WorkColumnEnum::SIWSI] = 0;
            $courseData[WorkColumnEnum::LABACTUAL] = 0;
            //$courseData[WorkColumnEnum::PRACTICEACTUAL] = $courseData[WorkColumnEnum::PRACTICEACTUAL] / $divider;
        }
        if ($includeColumn == IncludeColumnEnum::LAB_ONLY) {
            $courseData[WorkColumnEnum::LECTUREACTUAL] = 0;
            $courseData[WorkColumnEnum::SIWSI] = 0;
            $courseData[WorkColumnEnum::PRACTICEACTUAL] = 0;
        }
        if ($includeColumn == IncludeColumnEnum::EXAMS_ONLY) {
            $courseData[WorkColumnEnum::LECTUREACTUAL] = 0;
            $courseData[WorkColumnEnum::SIWSI] = 0;
            $courseData[WorkColumnEnum::PRACTICEACTUAL] = 0;
            $courseData[WorkColumnEnum::LABACTUAL] = 0;
        }

        if ($month > 5) {
            $date2 = $semesterFinalEndDate;
        }
        $reportedWorks = $teacherWorkItem->getReportedWorks();
        foreach ($reportedWorks as $reportedWork) {
            if ($reportedWork->getDate() >= $date1 && $reportedWork->getDate() < $date2) {
                $courseData[$reportedWork->getType()] += $reportedWork->getAmount();
            }
        }

        return $courseData;
    }

    private function getTeacherWorks($teacherWorkItems, $year, $semester, &$teachers) {
        $teacherWorks = [];
        foreach ($teacherWorkItems as $teacherWorkItem) {
            if ($teacherWorkItem->getYear() == $year && $teacherWorkItem->getSemester() == $semester) {
//                echo $teacherWorkItem->getTeacher()->getId()."<br>";
                if (!in_array($teacherWorkItem->getTeacher()->getId() . "_" . $teacherWorkItem->getWorkload(), $teachers)) {
                    $teachers[] = $teacherWorkItem->getTeacher()->getId() . "_" . $teacherWorkItem->getWorkload();
//                    echo $teacherWorkItem->getTeacher()->getId() . "_" . $teacherWorkItem->getWorkload()."<br>";
                    $workitems = $teacherWorkItem->getTeacher()->getTeacherWorkItems();
                    $i = 1;
                    $workitemsData = [];
                    foreach ($workitems as $workitem) {
                        if ($workitem->getWorkload() == $teacherWorkItem->getWorkload() && $workitem->getYear() == $year && $workitem->getSemester() == $semester) {
                            $workitemsData[] = [
                                'number' => $i,
                                'id' => $workitem->getId(),
                                'item' => $workitem,
                            ];
                            $i++;
                        }
                    }

                    $teacherWorks[] = [
                        'number' => $i,
                        'teacherName' => $teacherWorkItem->getTeacher()->getFullname(),
                        'teacher' => $teacherWorkItem->getTeacher(),
                        'workload' => $teacherWorkItem->getWorkload(),
                        'workitemsData' => $workitemsData,
                        'worksums' => null,
                        'totalsums' => $this->workColumnsManager->getEmptyWorkColumnsArray()
                    ];
                    $i++;
                }
            }
        }

        return $teacherWorks;
    }

    /**
     * @Route("/faculty/departmentworkreport/{departmentId}/{year?2020}/{semester?1}/{viewType?0}", name="faculty_departmentworkreport")
     */
    public function departmentWorkReport(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $departmentId = $request->attributes->get('departmentId');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $viewType = $request->attributes->get('viewType');
        $workloadNames = $this->systemInfoManager->getWorkloadNamesArray();
        $workColumns = WorkColumnEnum::getAvailableTypes();
        $empty_sum = [0];
        $month_sums = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => []];
        $semesterYear = $this->systemInfoManager->getSemesterBeginYear($semester);
        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
        $teacherWorkItemRepository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $department = $departmentRepository->find($departmentId);

        if ($department->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
            $used_months = $this->systemInfoManager->getTrimesterMonths($semester);
            $semesterYear = $this->systemInfoManager->getTrimesterBeginYear($semester);
        } else {
            $used_months = $this->systemInfoManager->getSemesterMonths($semester);
        }
        foreach ($workColumns as $workColumn) {
            $empty_sum[] = 0;
        }
        $departmentMonthlySums = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null];

        $teacherWorks = [];
        $teacherWorksMonths = [
            1 => null, 2 => null,
            3 => null, 4 => null,
            5 => null, 6 => null,
            7 => null, 8 => null,
            9 => null, 10 => null,
            11 => null, 12 => null
        ];
        $totalSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
        $totalLoadSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
        if (!empty($departmentId)) {

            $teachers = [];
            $teacherWorkItems = $department->getTeacherWorkItems();
            $teacherWorkSets = $department->getTeacherWorkSets();
//echo "count:".sizeof($teacherWorkItems);
            $i = 1;
//            $commencementYear = $this->systemInfoManager->getCurrentCommencementYear();
            $teacherWorks = $this->getTeacherWorks2($teacherWorkSets, $year, $semester, $teachers);

// Sort and print the resulting array
//uasort($teacherWorks, array('App\Controller\Workload\TeacherWorkReportController', 'compareNumeric'));
//uasort($teacherWorks, array('App\Controller\Workload\TeacherWorkReportController', 'compareString'));

            foreach ($used_months as $month) {
                $z = 0;
                $departmentMonthlySums[$month] = $this->workColumnsManager->getEmptyWorkColumnsArray();
//                $test_limiter = 1;
                foreach ($teacherWorks as $teacherWork) {
//                    if($test_limiter > 2) break;
//                    $test_limiter++;
                    $monthSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
                    $i = 1;
                    foreach ($teacherWork['workitemsData'] as $workitem) {
                        $courseData = $this->courseDaysManager->getWorkitemData($workitem['item'],
                                $semesterYear, $semester, $month, $empty_sum);

                        $courseData[29] = array_sum($courseData);
                        for ($u = 4; $u < 29; $u++) {
                            $monthSums[$u] += round($courseData[$u], 0);
                            $departmentMonthlySums[$month][$u] += round($courseData[$u], 0);
                            $teacherWorks[$z]['totalsums'][$u] += $courseData[$u];
                            $totalSums[$u] += $courseData[$u]; // + $workitem['replacedSessionsSums'][$u];
                        }
                        $i++;
                    }
                    $monthSums[29] = array_sum($monthSums);
                    $teacherWorks[$z]['worksums'] = $monthSums;
//                    if ($teacherWork['teacher']->getId()==38) {
//                        echo "<br><br><br><br>";
//                        print_r($monthSums);
//                    }

                    $z++;
                }
                $departmentMonthlySums[$month][29] = array_sum($departmentMonthlySums[$month]);
                $teacherWorksMonths[$month] = $teacherWorks;
            }
            $z = 0;
            foreach ($teacherWorks as $teacherWork) {
                $teacherWorks[$z]['totalsums'][29] = array_sum($teacherWorks[$z]['totalsums']);
                for ($u = 4; $u < 29; $u++) {
                    if (!in_array($u, [9, 11, 13])) {
                        $totalLoadSums[$u] += $teacherWorks[$z]['loadSums'][$u];
                    }
                }
                for ($u = 4; $u < 29; $u++) {
                    $totalSums[$u] += $teacherWorks[$z]['replacedSums'][$u];
                }
                $z++;
            }

            $departmentSubstitutions = $this->workColumnsManager->getEmptyWorkColumnsArray();
            $departmentSubstitutionsList = [];
            $taughtCourses = $department->getTaughtCourses();
            foreach ($taughtCourses as $taughtCourse) {
                if ($taughtCourse->getSemester() == $semester) {
                    $teacherWorkItem = $teacherWorkItemRepository->findOneBy(['taughtCourse' => $taughtCourse]);
                    $scheduleItems = $taughtCourse->getScheduleItems();
                    foreach ($scheduleItems as $scheduleItem) {
                        $scheduleChanges = $scheduleItem->getScheduleChanges();
                        foreach ($scheduleChanges as $scheduleChange) {
                            $departmentSubstitutionsList[] = [
                                'teacherWorkItem' => $teacherWorkItem,
                                'scheduleChange' => $scheduleChange,
                            ];
                            $classType = $scheduleChange->getClassType()->getSystemId();
                            $hours = $scheduleChange->getClassType()->getHours();
                            switch ($classType) {
                                case ClassTypeEnum::LECTURE:
                                    $departmentSubstitutions[WorkColumnEnum::LECTUREACTUAL] += $hours;
//                                    $totalSums[WorkColumnEnum::LECTUREACTUAL] += $hours;
                                    break;
                                case ClassTypeEnum::PRACTICE:
                                case ClassTypeEnum::SEMINAR:
                                case ClassTypeEnum::LANGUAGE:
                                    $departmentSubstitutions[WorkColumnEnum::PRACTICEACTUAL] += $hours;
//                                    $totalSums[WorkColumnEnum::PRACTICEACTUAL] += $hours;
                                    break;
                                case ClassTypeEnum::LAB:
                                    $departmentSubstitutions[WorkColumnEnum::LABACTUAL] += $hours;
//                                    $totalSums[WorkColumnEnum::LABACTUAL] += $hours;
                                    break;
                                default:
                                    $totalSums[WorkColumnEnum::SIWSI] += 0;
//                                    $departmentSubstitutions[WorkColumnEnum::SIWSI] += 0;
                            }
                        }
                    }
                }
            }
            $departmentSubstitutions[29] = array_sum($departmentSubstitutions);

            $totalSums[29] = array_sum($totalSums);
            $totalLoadSums[29] = array_sum($totalLoadSums);
        }

        return $this->render('department_work_item/departmentworkreport.html.twig', [
                    'workColumns' => $workColumns,
                    'workloadNames' => $this->systemInfoManager->getWorkloadNamesArray(),
                    'months' => $used_months,
                    'department' => $department,
                    'teacherWorks' => $teacherWorks,
                    'teacherWorksMonths' => $teacherWorksMonths,
                    'departmentMonthlySums' => $departmentMonthlySums,
                    'departmentSubstitutions' => $departmentSubstitutions,
                    'departmentSubstitutionsList' => $departmentSubstitutionsList,
                    'totalSums' => $totalSums,
                    'totalLoadSums' => $totalLoadSums,
                    'year' => $year,
                    'semester' => $semester,
                    'viewType' => $viewType,
                    'controller_name' => 'TeacherWorkReportController',
        ]);
    }

    private function getTeacherWorks2($teacherWorkSets, $year, $semester, &$teachers) {
        $teacherWorks = [];
        $loadSums = [];
        foreach ($teacherWorkSets as $teacherWorkSet) {
            if ($teacherWorkSet->getYear() == $year) {
                $loadSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
                $replacedSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
//                echo $teacherWorkItem->getTeacher()->getId()."<br>";
//                    echo $teacherWorkItem->getTeacher()->getId() . "_" . $teacherWorkItem->getWorkload()."<br>";
                $workitems = $teacherWorkSet->getTeacherWorkItems();
                $i = 1;
                $workitemsData = [];
                $loadSum = 0;
                foreach ($workitems as $workitem) {
                    if ($workitem->getYear() == $year && $workitem->getSemester() == $semester) {
                        $replacedSessionsSums = $this->courseDaysManager->getReplacedSessionsSumsforSemester($workitem,
                                $year, $semester);
                        $workitemsData[] = [
                            'number' => $i,
                            'id' => $workitem->getId(),
                            'item' => $workitem,
                            'sum' => $workitem->getTotal(),
                            'replacedSessionsSums' => $replacedSessionsSums,
                        ];
                        for ($u = 9; $u < 30; $u++) {
                            $loadSums[$u] += intval($workitem->getData()[$u]);
                            $replacedSums[$u] += $replacedSessionsSums[$u];
//                            echo "adding: " . $workitem->getData()[$u];
//                            echo " sum: [".$u."]" . $loadSums[$u];
//                            echo "<br>";
                        }

                        //$loadSum += $workitem->getTotal();
                        $i++;
                    }
                }


                $teacherWorks[] = [
                    'number' => $i,
                    'teacherName' => $teacherWorkSet->getTeacher()->getFullname(),
                    'teacher' => $teacherWorkSet->getTeacher(),
                    'workload' => $teacherWorkSet->getWorkload(),
                    'note' => $teacherWorkSet->getNote(),
                    'workitemsData' => $workitemsData,
                    'worksums' => null,
                    'replacedSums' => $replacedSums,
                    'loadSums' => $loadSums,
                    'totalsums' => $this->workColumnsManager->getEmptyWorkColumnsArray(),
                    'teacherWorkSet' => $teacherWorkSet,
                ];
                $i++;
            }
        }

        return $teacherWorks;
    }

}
