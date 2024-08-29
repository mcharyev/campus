<?php

namespace App\Controller\Workload;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Department;
use App\Entity\Group;
use App\Entity\ScheduleItem;
use App\Entity\TaughtCourse;
use App\Entity\Teacher;
use App\Entity\TeacherWorkItem;
use App\Entity\TeacherWorkSet;
use App\Entity\ScheduleChange;
use App\Service\SystemEventManager;
use App\Enum\WorkColumnEnum;
use App\Enum\WorkRowEnum;
use App\Enum\ClassTypeEnum;
use App\Enum\IncludeColumnEnum;
use App\Enum\WorkloadEnum;
use App\Controller\Workload\WorkColumnsArray;
use App\Service\CourseDaysManager;
use App\Service\SystemInfoManager;

class TeacherWorkReportController extends AbstractController {

    private $systemEventManager;
    private $courseDaysManager;
    private $systemInfoManager;
    private $workColumnsManager;

    public function __construct(SystemEventManager $systemEventManager,
            CourseDaysManager $courseDaysManager,
            SystemInfoManager $systemInfoManager,
            WorkColumnsArray $workColumnsManager) {
        $this->systemEventManager = $systemEventManager;
        $this->courseDaysManager = $courseDaysManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->workColumnsManager = $workColumnsManager;
    }

    private function getGroupNamesFromCodes(?string $groupCoursePairString) {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupNames = [];
        $groupCoursePairs = explode(",", $groupCoursePairString);
        foreach ($groupCoursePairs as $groupCoursePair) {
            $groupCoursePairData = explode("-", $groupCoursePair);
            $group = $groupRepository->findOneBy(['systemId' => $groupCoursePairData[0]]);
            if (!empty($group)) {
                $groupNames[] = $group->getLetterCode();
            }
        }

        return implode(", ", $groupNames);
    }

    /**
     * @Route("/faculty/teacherworkreport/{teacherId?0}/{teacherWorkSetId?0}/{year?2019}/{semester?1}", requirements={"teacherWorkSetId"="\d+","year"="\d+","semester"="\d+"}, name="faculty_teacherworkreportnew")
     */
    public function teacherWorkReport(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $teacher = null;
        $workloadNames = $this->systemInfoManager->getWorkloadNamesArray();
        $teacherId = $request->attributes->get('teacherId');
        $teacherWorkSetId = $request->attributes->get('teacherWorkSetId');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
//echo $semester;
        $beginDate = $this->systemInfoManager->getSemesterBeginDate($semester);
        $endDate = $this->systemInfoManager->getSemesterEndDate($semester);
        $workcolumn_texts = $this->workColumnsManager->getWorkColumnTexts();
        $workColumns = WorkColumnEnum::getAvailableTypes();
        $empty_sum = [0];
        $month_sums = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => []];
        $workitemsDataMonths = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null];
        $total_sums = $this->workColumnsManager->getEmptyWorkColumnsArray();
        $total_type_sums = $this->workColumnsManager->getEmptyWorkColumnsArray();
        $total_load_sums = $this->workColumnsManager->getEmptyWorkColumnsArray();
        $workitem_sums = [];
        $workitemsData = [];
        $year = $this->systemInfoManager->getSemesterBeginYear($semester);
//echo "<br><br><br>YEAR: " . $year . " SEMESTER: " . $semester . "<br>";
//if teacherWorkSetId is not set get the first teacher workset of Teacher
        if ($teacherWorkSetId == 0) {
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->find($teacherId);
            $teacherWorkSets = $teacher->getTeacherWorkSets();
            if (sizeof($teacherWorkSets) > 0) {
                $teacherWorkSetId = $teacherWorkSets[0]->getId();
            }
        }
        if (!empty($teacherWorkSetId)) {
            $teacherWorkSetRepository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
            $teacherWorkSet = $teacherWorkSetRepository->find($teacherWorkSetId);
            if ($teacherWorkSet) {
                $teacher = $teacherWorkSet->getTeacher();
//                $used_months = $semester_months[$semester - 1];
                if ($teacher->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                    $used_months = $this->systemInfoManager->getTrimesterMonths($semester);
                    $beginDate = $this->systemInfoManager->getTrimesterBeginDate($semester);
                    $endDate = $this->systemInfoManager->getTrimesterEndDate($semester);
                    $year = $this->systemInfoManager->getTrimesterBeginYear($semester);
                } else {
                    $used_months = $this->systemInfoManager->getSemesterMonths($semester);
                }
                foreach ($workColumns as $workColumn) {
                    $empty_sum[] = 0;
                }
                $workitems = $teacherWorkSet->getTeacherWorkItems();

                $i = 1;
                $currentYear = $this->systemInfoManager->getCurrentYear();
                foreach ($workitems as $workitem) {

//                    prepare work items data array for the semester
//                    item_sums and courseData will be filled later
                    if ($workitem->getYear() == $currentYear && $workitem->getSemester() == $semester) {
                        $workitemsData[] = [
                            'number' => $i,
                            'id' => $workitem->getId(),
                            'item' => $workitem,
                            'title' => $workitem->getTitle(),
                            'groups' => $this->getGroupNamesFromCodes($workitem->getStudentGroups()),
                            'item_sums' => null,
                            'courseData' => null,
                            'replacedSessionsSums' => $this->workColumnsManager->getEmptyWorkColumnsArray(),
                            'replacedSessionsTotalSums' => $this->workColumnsManager->getEmptyWorkColumnsArray(),
                        ];
                        $i++;
                    }
                }

                foreach ($used_months as $month) {
                    $month_sums[$month] = $this->workColumnsManager->getEmptyWorkColumnsArray();
                    $i = 1;
                    foreach ($workitemsData as $workitem) {
                        $courseData = $this->courseDaysManager->getWorkitemData($workitem['item'],
                                $year, $semester, $month, $empty_sum);
                        $replacedSessionsSums = $this->courseDaysManager->getReplacedSessionsSums($workitem['item'],
                                $year, $semester, $month, $empty_sum);
//$courseData[29] = array_sum($courseData);
//                        echo "Title: " . $workitem['item']->getTitle() . " Sum:" . $courseData[29]."<br>";
                        $workitem_sums[] = [
                            'month' => $month,
                            'number' => $workitem['number'],
                            'sums' => $courseData,
                            'replacedSessionsSums' => $replacedSessionsSums,
                        ];
                        for ($u = 4; $u < 29; $u++) {
                            $month_sums[$month][$u] += $courseData[$u] + $replacedSessionsSums[$u];
                            $workitemsData[$i - 1]['replacedSessionsTotalSums'][$u] += $replacedSessionsSums[$u];
                            $workitemsData[$i - 1]['replacedSessionsTotalSums'][29] += $replacedSessionsSums[$u];
                        }
                        $workitemsData[$i - 1]['courseData'] = $courseData;
                        $workitemsData[$i - 1]['replacedSessionsSums'] = $replacedSessionsSums;
//                        echo 'Month: ' . $month . "<br>";
//                        echo var_dump($replacedSessionsSums);
//                        echo var_dump($workitemsData[$i - 1]);
//$courseData[] = $courseData;
                        $i++;
                    }
                    $workitemsDataMonths[$month] = $workitemsData;
                    $month_sums[$month][29] = array_sum($month_sums[$month]);
//                    $workitemsData[$i - 1]['replacedSessionsSums'][29] = array_sum($workitemsData[$i - 1]['replacedSessionsSums']);
                }

                $month = 0;

//SUMMARY BY TYPE
                $i = 1;
//                echo var_dump($workitemsData[0]);
                foreach ($workitemsData as $workitem) {
                    $workitemsData[$i - 1]['item_sums'] = $this->workColumnsManager->getEmptyWorkColumnsArray();
//                    echo $workitem['title'] . "<br>";
//                    echo var_dump($workitemsData[$i - 1]['replacedSessionsSums']);
                    foreach ($workitem_sums as $workitem_sum) {
                        if ($workitem_sum['number'] == $workitem['number']) {
//                            echo strval($i - 1) . " - " . $workitem['title'] . " - " . $workitem['number'] . " - " . $workitem_sum['month'] . "<br>";
                            $t = 0;
                            foreach ($workitem_sum['sums'] as $sum) {
//                                echo $workitemsData[$i - 1]['replacedSessionsSums'][$t];
                                $workitemsData[$i - 1]['item_sums'][$t] += $sum;
//                                $workitemsData[$i - 1]['replacedSessionsTotalSums'][$t] += $workitemsData[$i - 1]['replacedSessionsSums'][$t];
                                $t++;
                            }
//                            echo '<br>';
                        }
                    }
                    for ($u = 9; $u < 30; $u++) {
                        $total_type_sums[$u] += intval($workitemsData[$i - 1]['item_sums'][$u]) + intval($workitemsData[$i - 1]['replacedSessionsTotalSums'][$u]);
                        $total_load_sums[$u] += intval($workitem['item']->getData()[$u]);
                    }
                    $i++;
                    $t = 0;
                }
//                $teacherWorkSet->setDataField('semestersum' . $semester, join(",", $total_type_sums));
//                $teacherWorkSetRepository->save($teacherWorkSet);
//get sessions performed for other teachers
                $substitutionsReceived = $teacherWorkSet->getScheduleChanges($semester);
                //this->getSubstitutionsReceived($teacherWorkSet, $semester);
                $scheduleChangeRepository = $this->getDoctrine()->getRepository(ScheduleChange::class);
                $substitutionsDelivered = $scheduleChangeRepository->findBy(['newTeacher' => $teacher, 'semester' => $semester]);
            }

//        print_r($workitemsData[3]['courseData']);

            return $this->render('teacher/workreport.html.twig', [
                        'workColumns' => $workColumns,
                        'workColumnTexts' => $workcolumn_texts,
                        'workloadNames' => $workloadNames,
                        'teacherWorkSet' => $teacherWorkSet,
                        'workload' => $teacherWorkSet->getWorkload(),
                        'months' => $used_months,
                        'workitemsData' => $workitemsData,
                        'workitems' => $workitemsDataMonths,
                        'monthSums' => $month_sums,
                        'totalSums' => $total_sums,
                        'totalTypeSums' => $total_type_sums,
                        'totalLoadSums' => $total_load_sums,
                        'teacher' => $teacher,
                        'year' => $currentYear,
                        'semester' => $semester,
                        'substitutionsReceived' => $substitutionsReceived,
                        'substitutionsDelivered' => $substitutionsDelivered,
                        'controller_name' => 'TeacherWorkReportController',
            ]);
        }
    }

//    private function getSubstitutionsReceived(TeacherWorkSet $teacherWorkSet, int $semester) {
//        $result = [];
//        $scheduleChangeRepository = $this->getDoctrine()->getRepository(ScheduleChange::class);
//        $substitutions = $scheduleChangeRepository->findBy(['semester' => $semester]);
//        $taughtCourses = $teacherWorkSet->
//        foreach ($substitutions as $substitution) {
//            if ($substitution->getScheduleItem()->getTeacher() == $teacher) {
//                $result[] = $substitution;
//            }
//        }
//        return $result;
//    }

    /**
     * @Route("/faculty/teacherjournalnew/{teacherId?0}/{teacherWorkSetId?0}/{year?2020}/{semester?1}/{month?}", name="faculty_teacherjournalnew")
     */
    public function teacherJournalNew(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $teacher = null;
        $viewingTeacher = null;
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $reportedMonth = $request->attributes->get('month');
        $teacherId = $request->attributes->get('teacherId');
        $teacherWorkSetId = $request->attributes->get('teacherWorkSetId');
        $teacherWorkSetViewItems = [];
        $replacedSessionsTotalSum = 0;
        $substitutionsDelivered = [];
        try {
            $months = $this->systemInfoManager->getSemesterMonths($semester);
            $teacherWorkSet = null;
            $columnNames = $this->workColumnsManager->getWorkColumnTexts();
            $totalReportedSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
            $totalPlannedSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->find($teacherId);
//        echo "Teacher work set id:" . $teacherWorkSetId . " is empty:" . empty($teacherWorkSetId);

            $teacherWorkSetRepository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
            $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
            if (empty($teacherWorkSetId)) {
                $teacherWorkSetsFound = $teacher->getTeacherWorkSets();
                if (sizeof($teacherWorkSetsFound) > 0) {
                    $teacherWorkSet = $teacherWorkSetsFound[0];
//                echo "Teacher work set found:" . sizeof($teacherWorkSetsFound);
                }
            } else {
                $teacherWorkSet = $teacherWorkSetRepository->find($teacherWorkSetId);
            }
            $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);

            if ($teacherWorkSet) {
                $teacher = $teacherWorkSet->getTeacher();
                $teacherWorkItems = $teacherWorkSet->getTeacherWorkItems();
                $groupRepository = $this->getDoctrine()->getRepository(Group::class);
                $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
                $teacherWorkItemRepository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
                $taughtCoursesOriginal = $teacher->getTaughtCourses();
//$isReplacement = ($teacherWorkSet->getWorkload() == WorkloadEnum::LOADREPLACEMENT);
                if ($teacher->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                    $months = $this->systemInfoManager->getTrimesterMonths($semester);
                }
                if ($reportedMonth) {
                    $months = [$reportedMonth];
                }
                $replacingSessionsSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
                $replacingSessions = [];

                foreach ($teacherWorkItems as $teacherWorkItem) {
                    if ($teacherWorkItem->getYear() == $year && $teacherWorkItem->getSemester() == $semester) {
                        $reportedSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
                        $replacedSessionsSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
                        $replacedSessions = [];
                        $teacherWorkSetViewItem = [
                            'teacherWorkItem' => $teacherWorkItem,
                            'courseName' => '',
                            'scheduleItemViewColumns' => [],
                            'replacedSessions' => [],
                            'replacedSessionsSums' => [],
                            'classTypeSums' => null,
                            'totalSum' => 0,
                            'reportedSums' => $reportedSums,
                        ];

                        $taughtCourse = $teacherWorkItem->getTaughtCourse();
// Needed to check if taught course deleted manually brute force
//$taughtCourse = $taughtCourseRepository->find($taughtCourseFromItem->getId());
//return new Response(var_dump($taughtCourse));
                        if ($taughtCourse) {
//                        $teacherWorkItemsCourse = $teacherWorkItemRepository->findBy(['taughtCourse' => $taughtCourse]);
//                        $teacherWorkSetViewItem['workItems'] = $teacherWorkItemsCourse;

                            $teacherWorkSetViewItem['courseName'] = $taughtCourse->getNameEnglish() . " - " . $this->courseDaysManager->getGroupNamesFromIds($taughtCourse->getStudentGroups());

//if ($isReplacement) {
//    $scheduleItemViewColumns = $this->courseDaysManager->getReplacedScheduleItemViewColumns($teacherWorkItem, $year, $semester, $months);
//} else {
                            $scheduleItemViewColumns = $this->courseDaysManager->getScheduleItemViewColumns($teacherWorkItem, $year, $semester, $months, true);
//}
//echo "SIVC count:" . sizeof($scheduleItemViewColumns) . " " . $taughtCourse->getNameEnglish()." Teacher work item id:".$teacherWorkItem->getId()."<br>";
                            $teacherWorkSetViewItem['scheduleItemViewColumns'] = $scheduleItemViewColumns;
                            $classTypeSums = [
                                ClassTypeEnum::LECTURE => 0,
                                ClassTypeEnum::PRACTICE => 0,
                                ClassTypeEnum::SEMINAR => 0,
                                ClassTypeEnum::LAB => 0,
                                ClassTypeEnum::SIWSI => 0,
                                ClassTypeEnum::LANGUAGE => 0,
                            ];
                            foreach ($scheduleItemViewColumns as $scheduleItemViewColumn) {
//echo $scheduleItemViewColumn['scheduleItem']->getId() . " - " . $scheduleItemViewColumn['scheduleItem']->getClassType()->getLetterCode()."<br>";
                                $classTypeSums[$scheduleItemViewColumn['scheduleItem']->getClassType()->getSystemId()] += $scheduleItemViewColumn['totalSum'];

                                $classTypeSumsReplaced = [
                                    ClassTypeEnum::LECTURE => 0,
                                    ClassTypeEnum::PRACTICE => 0,
                                    ClassTypeEnum::SEMINAR => 0,
                                    ClassTypeEnum::LAB => 0,
                                    ClassTypeEnum::SIWSI => 0,
                                    ClassTypeEnum::LANGUAGE => 0,
                                ];
                                foreach ($scheduleItemViewColumn['monthlyWorkItems'] as $monthlyWorkItem) {
                                    foreach ($monthlyWorkItem['replacedSessions'] as $replacedSession) {
                                        $classTypeSumsReplaced[$replacedSession['courseDay']['scheduleItem']->getClassType()->getSystemId()] += $replacedSession['hours'];
//echo $teacherWorkItem->getId() . " " . $replacedSession['teacher']->getFullname() . " " . $replacedSession['courseDay']['date']->format("Y-m-d") . "<br>";
                                        $replacedSessions[] = $replacedSession;
                                        $replacedSessionsSums[29] += $replacedSession['hours'];
                                        $replacedSessionsTotalSum += $replacedSession['hours'];
                                    }
                                }
                                $replacedSessionsSums[WorkColumnEnum::LECTUREACTUAL] += $classTypeSumsReplaced[ClassTypeEnum::LECTURE];
                                $replacedSessionsSums[WorkColumnEnum::PRACTICEACTUAL] += $classTypeSumsReplaced[ClassTypeEnum::PRACTICE] + $classTypeSumsReplaced[ClassTypeEnum::SEMINAR] + $classTypeSumsReplaced[ClassTypeEnum::LANGUAGE];
                                $replacedSessionsSums[WorkColumnEnum::LABACTUAL] += $classTypeSumsReplaced[ClassTypeEnum::LAB];
                                $replacedSessionsSums[WorkColumnEnum::SIWSI] += $classTypeSumsReplaced[ClassTypeEnum::SIWSI];
                            }
                            $teacherWorkSetViewItem['classTypeSums'] = $classTypeSums;
                            $teacherWorkSetViewItem['totalSum'] += array_sum($classTypeSums);
                            $reportedSums[WorkColumnEnum::LECTUREACTUAL] += $classTypeSums[ClassTypeEnum::LECTURE];
                            $reportedSums[WorkColumnEnum::PRACTICEACTUAL] += $classTypeSums[ClassTypeEnum::PRACTICE] + $classTypeSums[ClassTypeEnum::SEMINAR] + $classTypeSums[ClassTypeEnum::LANGUAGE];
                            $reportedSums[WorkColumnEnum::LABACTUAL] += $classTypeSums[ClassTypeEnum::LAB];
                            $reportedSums[WorkColumnEnum::SIWSI] += $classTypeSums[ClassTypeEnum::SIWSI];
                        }
                        $reportedWorks = $teacherWorkItem->getReportedWorks();
                        $reportedWorkSum = 0;
                        foreach ($reportedWorks as $reportedWork) {
                            $reportedWorkSum += $reportedWork->getAmount();
                            $reportedSums[$reportedWork->getType()] += $reportedWork->getAmount();
                        }

                        for ($u = 9; $u < 29; $u++) {
                            $reportedSums[29] += intval($reportedSums[$u]);
                            $totalReportedSums[$u] += intval($reportedSums[$u]) + $replacedSessionsSums[$u];
                            $totalPlannedSums[$u] += intval($teacherWorkItem->getData()[$u]);
                        }
                        $totalReportedSums[29] += intval($reportedSums[29]) + $replacedSessionsSums[29];
                        $totalPlannedSums[29] += intval($teacherWorkItem->getData()[29]);

                        $teacherWorkSetViewItem['totalSum'] += $reportedWorkSum + $replacedSessionsSums[29];
                        $teacherWorkSetViewItem['reportedSums'] = $reportedSums;
                        $teacherWorkSetViewItem['replacedSessions'] = $replacedSessions;
                        $teacherWorkSetViewItem['replacedSessionsSums'] = $replacedSessionsSums;
                        $teacherWorkSetViewItem['replacingSessions'] = $replacingSessions;
//echo "TWR Size of replaced sessions: " . sizeof($replacedSessions) . " ID= " . $teacherWorkItem->getId() . " --\n";
                        $teacherWorkSetViewItems[] = $teacherWorkSetViewItem;
                    }
                }
//get sessions performed for other teachers
                $scheduleChangeRepository = $this->getDoctrine()->getRepository(ScheduleChange::class);
                $substitutionsDelivered = $scheduleChangeRepository->findBy(['newTeacher' => $teacher, 'semester' => $semester]);
            }

            return $this->render('teacher/teacherjournalnew.html.twig', [
                        'viewingTeacher' => $viewingTeacher,
                        'teacher' => $teacher,
                        'teacherWorkSet' => $teacherWorkSet,
                        'columnNames' => $columnNames,
                        'year' => $year,
                        'semester' => $semester,
                        'months' => $months,
                        'controller_name' => 'AttendanceController',
                        'teacherWorkSetViewItems' => $teacherWorkSetViewItems,
                        'totalPlannedSums' => $totalPlannedSums,
                        'totalReportedSums' => $totalReportedSums,
                        'reportedMonth' => $reportedMonth,
                        'replacingSessions' => $substitutionsDelivered,
                        'replacedSessionTotalSum' => $replacedSessionsTotalSum
            ]);
        } catch (Exception $ex) {
            return new Response($ex->getMessage());
        }
    }

}
