<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\Group;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Entity\Freeday;
use App\Entity\TeacherWorkItem;
use App\Entity\ScheduleChange;
use App\Enum\ScheduleChangeTypeEnum;
use App\Enum\FreedayTypeEnum;
use App\Enum\WorkloadEnum;
use App\Enum\ClassTypeEnum;
use App\Enum\WorkColumnEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\SystemInfoManager;
use App\Controller\Workload\WorkColumnsArray;
use App\Repository\SettingRepository;

/**
 * Description of CourseDaysManager
 *
 * @author nazar
 */
class CourseDaysManager extends AbstractController {

//put your code here
    private $systemInfoManager;
    private $workColumnsManager;
    private $settingsRepository;

    function __construct(SystemInfoManager $systemInfoManager,
            WorkColumnsArray $workColumnsManager, SettingRepository $settingRepository) {
        $this->systemInfoManager = $systemInfoManager;
        $this->workColumnsManager = $workColumnsManager;
        $this->settingsRepository = $settingRepository;
    }

    public function getGroupCourseDays(Group $group, ?TaughtCourse $taughtCourse, string $action, string $beginDate, string $endDate): ?array {
        $siwsiMode = false;
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        if ($action == 'course') {
            $courseScheduleItems = $scheduleItemRepository->findCourseScheduleItemsAll(['course_id' => $taughtCourse->getId()]);
//$beginDate = new \DateTime($beginDate);
//$endDate = new \DateTime($endDate);
//$courseScheduleItems = $taughtCourse->getScheduleItems();
//$beginDate = $taughtCourse->getStartDate()->format("Y-m-d");
//$endDate = $taughtCourse->getEndDate()->format("Y-m-d");
//$endDate = $taughtCourse->getEndDate()->format("Y-m-d");
        } elseif ($action == 'coursemonth') {
//$courseScheduleItems = $taughtCourse->getScheduleItems();
            $courseScheduleItems = $scheduleItemRepository->findCourseScheduleItemsAll(['course_id' => $taughtCourse->getId()]);
            $siwsiMode = true;
//$beginDate = $taughtCourse->getStartDate()->format("Y-m-d");
//$endDate = $taughtCourse->getEndDate()->format("Y-m-d");
//            echo "Begin date:". $beginDate . " : End date:" . $endDate . "<br>";
//$endDate = $taughtCourse->getEndDate()->format("Y-m-d");
        } elseif ($action == 'courseteacherjournal') {
//$courseScheduleItems = $taughtCourse->getScheduleItems();
            $courseScheduleItems = $scheduleItemRepository->findCourseScheduleItemsAll(['course_id' => $taughtCourse->getId()]);
            $beginDate = $taughtCourse->getStartDate()->format("Y-m-d");
            $endDate = $taughtCourse->getEndDate()->format("Y-m-d");
            $siwsiMode = true;
        } elseif ($action == 'groupfacultyreport') {
            $courseScheduleItems = $scheduleItemRepository->findGroupScheduleItemsAllNoSiw(['group_code' => $group->getSystemId()]);
        } else {
            $courseScheduleItems = $scheduleItemRepository->findGroupScheduleItemsAll($group->getSystemId());
        }

        $courseDays = array();
        $group_id = $group->getSystemId();
//        if ($group->getStudyYear() < 2)
//            $siwsiMode = false;
//$today = new \DateTime("2020-02-03"); //
        $today = new \DateTime(date("Y-m-d"));
        $semesterDays = $this->getDaysFromInterval($beginDate, $endDate);
        $freedayRepository = $this->getDoctrine()->getRepository(Freeday::class);
        $boolLLD = false;
        if ($group->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
            $freedays = $freedayRepository->findBy(['type' => [3, 4]]);
            $boolLLD = true;
        } else {
            $freedays = $freedayRepository->findBy(['type' => [1, 2, 5]]);
        }
//        if ($taughtCourse) {
//            $scheduleChanges = $taughtCourse->getScheduleChanges();
//        }

        if ($taughtCourse) {
            $date1 = $taughtCourse->getStartDate();
            $date2 = $taughtCourse->getEndDate();
        } else {
            $date1 = $this->systemInfoManager->getSemesterBeginDate($this->systemInfoManager->getCurrentSemester());
            $date2 = $this->systemInfoManager->getSemesterEndDate($this->systemInfoManager->getCurrentSemester());
        }

        $locked = 0;
        $lockAfterDays = $this->settingsRepository->findOneBy(['name' => 'lock_attendance_after_days'])->getValue();
//        $period = 2;
        $periodCount = 1;
        $periodCounts = [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
            1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
            1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
            1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
            1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1];
        foreach ($semesterDays as $semesterDay) {
            $itemIndex = 0;
            foreach ($courseScheduleItems as $courseScheduleItem) {
                $date1 = $courseScheduleItem->getStartDate();
                $date2 = $courseScheduleItem->getEndDate();
//$scheduleItemDays = $this->getDaysFromInterval($courseScheduleItem->getStartDate()->format("Y-m-d"), $courseScheduleItem->getEndDate()->format("Y-m-d"));
//                foreach ($scheduleItemDays as $scheduleItemDay) {
                $weekdayNumber = $semesterDay->format("N");
                if ($weekdayNumber == $courseScheduleItem->getDay() && ($semesterDay >= $date1 && $semesterDay < $date2)) {
//                    if ($courseScheduleItem->getId() == 545) {
//                        echo "mode:" . $action .
//                        " adding:" . $semesterDay->format("d-m-Y") .
//                        " period:" . $courseScheduleItem->getPeriod() .
//                        " periodCount:" . $periodCounts[$itemIndex] .
//                        " itemIndex:" . $itemIndex .
//                        " modulus:" . ($periodCounts[$itemIndex] % $courseScheduleItem->getPeriod()
//                        );
//                        echo "<br>";
//                    }

                    $periodCounts[$itemIndex]++;

                    if ($periodCounts[$itemIndex] % $courseScheduleItem->getPeriod() == 0) {
                        $scheduleChanges = $courseScheduleItem->getScheduleChanges();
                        $courseGroupIds = explode(",", $courseScheduleItem->getStudentGroups());
                        if (in_array($group_id, $courseGroupIds)) {
                            $classNumber = $courseScheduleItem->getSession();
//                        if ($weekdayNumber == $courseScheduleItem->getDay()) {
                            if ($this->isGranted("ROLE_DEAN")) {
                                $locked = 0;
                            } else {
                                $interval = $semesterDay->diff($today);
                                $dayDifference = abs($interval->format("%a"));
                                if ($dayDifference > $lockAfterDays) {
                                    $locked = 1;
                                } else {
                                    $locked = 0;
                                }
                            }

                            $changeType = ScheduleChangeTypeEnum::NONE;
                            $changed = false;
                            $changedDay = null;
                            foreach ($scheduleChanges as $scheduleChange) {
                                if ($scheduleChange->getDate() == $semesterDay) {
                                    $changedDay = $scheduleChange;
                                    $changed = true;
                                    $changeType = ScheduleChangeTypeEnum::REPLACEMENT;
                                    //$locked = 0;
                                    break;
                                }
                            }

                            $holiday = null;
                            $freeday = null;
                            if ($changeType == ScheduleChangeTypeEnum::NONE) {
                                foreach ($freedays as $freeday) {
                                    //echo "Freeday: " . $freeday->getDate()->format("Y-m-d") . " Semester day:" . $semesterDay->format("Y-m-d")."<br>";
                                    //echo "Freeday type: " . $freeday->getType()."<br>";
                                    //echo "Course schedule Item Id: " . $courseScheduleItem->getId()."<br>";
                                    //echo "Department code: " . $courseScheduleItem->getDepartmentCode()."<br>";
                                    //echo "\n";
                                    if ($boolLLD) {
//                                        echo "LLD detected<br>";
                                        if ($freeday->getType() == FreedayTypeEnum::NOREPLACEMENT_LLD && $freeday->getDate() == $semesterDay) {
                                            $holiday = $freeday;
                                            $changed = true;
                                            $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                            break;
                                        }
                                        
                                        if ($freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT &&
                                                $freeday->getDate() == $semesterDay && $courseScheduleItem->getSession() == $freeday->getSession()) {
//                                                echo "added";
                                            $holiday = $freeday;
                                            $changed = true;
                                            $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                            //$locked = false;
                                            break;
                                        }
//                                        else {
//                                            echo "remove freeday";
//                                            $changed = false;
//                                            $holiday = null;
//                                        }
                                    } else {
                                     
                                        if ($freeday->getType() == FreedayTypeEnum::NOREPLACEMENT && $freeday->getDate() == $semesterDay) {
                                            $holiday = $freeday;
                                            $changed = true;
                                            $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                            break;
                                        }

                                        if ($freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT_BM &&
                                                $freeday->getDate() == $semesterDay && $courseScheduleItem->getSession() == $freeday->getSession()) {
                                            echo "added TESTING " . $freeday->getDate()->format("d-M-y") . " " . $freeday->getSession();
                                            $holiday = $freeday;
                                            $changed = true;
                                            $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                            //$locked = false;
                                            break;
                                        }

                                        if ($freeday->getType() == FreedayTypeEnum::REPLACEMENT && $freeday->getDate() == $semesterDay) {
                                            $holiday = $freeday;
                                            $changed = true;
                                            $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                            //$locked = false;
                                            break;
                                        }
//                                        $changedDay = null;
//                                        foreach ($scheduleChanges as $scheduleChange) {
//                                            if ($scheduleChange->getDate() == $freeday->getDate()) {
//                                                $changedDay = $scheduleChange;
//                                                break;
//                                            }
//                                        }
                                    }
                                }
                            }



                            $courseDays[] = [
                                'changed' => $changed,
                                'scheduleItem' => $courseScheduleItem,
                                'coverday' => null,
                                'coverdayTitle' => '',
                                'holiday' => $holiday,
                                'freeday' => $holiday,
                                'changedDay' => null,
                                'locked' => $locked,
                                'classNumber' => $classNumber,
                                'date' => $semesterDay,
                                'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                'session' => $courseScheduleItem->getSession(),
                                'course' => $courseScheduleItem->getTaughtCourse()
                            ];

                            $classNumber++;
                            if ($changeType == ScheduleChangeTypeEnum::REPLACEMENT) {
                                $courseDays[] = [
                                    'changed' => false,
                                    'scheduleItem' => $courseScheduleItem,
                                    'coverday' => $changedDay->getDate(),
                                    'coverdayTitle' => $changedDay->getDate()->format('d.m.Y') . " (" . $changedDay->getDataField("note") . ")",
                                    'holiday' => null,
                                    'freeday' => $holiday,
                                    'changedDay' => $changedDay,
                                    'locked' => $locked,
                                    'classNumber' => $classNumber,
                                    'date' => $changedDay->getNewDate(),
                                    'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                    'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                    'session' => $courseScheduleItem->getSession(),
                                    'course' => $courseScheduleItem->getTaughtCourse(),
                                ];
                                $classNumber++;
                            }

                            //echo "Department code:" . $courseScheduleItem->getDepartmentCode();

                            if ($changeType == ScheduleChangeTypeEnum::FREEDAY) {
                                if (!$boolLLD && $freeday->getType() == FreedayTypeEnum::REPLACEMENT) {
                                    $interval = $holiday->getNewDate()->diff($today);
                                    $dayDifference = abs($interval->format("%a"));
                                    if ($dayDifference > $lockAfterDays) {
                                        $locked = 1;
                                    } else {
                                        $locked = 0;
                                    }
//                                    $changedDay = null;
//                                    foreach ($scheduleChanges as $scheduleChange) {
//                                        if ($scheduleChange->getDate() == $freeday->getDate()) {
//                                            $changedDay = $scheduleChange;
//                                            break;
//                                        }
//                                    }

                                    $courseDays[] = [
                                        'changed' => false,
                                        'scheduleItem' => $courseScheduleItem,
                                        'coverday' => $freeday->getDate(),
                                        'coverdayTitle' => $freeday->getTitle(),
                                        'holiday' => null,
                                        'freeday' => $freeday,
                                        'changedDay' => $changedDay,
                                        'locked' => $locked,
                                        'classNumber' => $classNumber,
                                        'date' => $holiday->getNewDate(),
                                        'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                        'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                        'session' => $courseScheduleItem->getSession() + 4, //add 4 pairs so that it doesn't add absence for the same session on that day for the same course
                                        'course' => $courseScheduleItem->getTaughtCourse()
                                    ];
                                    $classNumber++;
                                }
//                                echo "Dep code:" . $courseScheduleItem->getDepartmentCode() . " Freeday:" . $freeday->getDate()->format("Y-m-d") . " Session:" . $freeday->getType();
//                                echo "\n";
                                $interval = $holiday->getNewDate()->diff($today);
                                $dayDifference = abs($interval->format("%a"));
                                if ($dayDifference > $lockAfterDays) {
                                    $locked = 1;
                                } else {
                                    $locked = 0;
                                }

                                if ($boolLLD && $freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT) {
                                    //echo "added<br>";


                                    $courseDays[] = [
                                        'changed' => false,
                                        'scheduleItem' => $courseScheduleItem,
                                        'coverday' => $freeday->getDate(),
                                        'coverdayTitle' => $freeday->getTitle(),
                                        'holiday' => null,
                                        'freeday' => $freeday,
                                        'changedDay' => null,
                                        'locked' => $locked,
                                        'classNumber' => $classNumber,
                                        'date' => $holiday->getNewDate(),
                                        'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                        'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                        'session' => $courseScheduleItem->getSession() + 4, //add 4 pairs so that it doesn't add absence for the same session on that day for the same course
                                        'course' => $courseScheduleItem->getTaughtCourse()
                                    ];
                                    $classNumber++;
                                }

                                if (!$boolLLD && $freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT_BM) {
                                    //echo "added<br>";
                                    $courseDays[] = [
                                        'changed' => false,
                                        'scheduleItem' => $courseScheduleItem,
                                        'coverday' => $freeday->getDate(),
                                        'coverdayTitle' => $freeday->getTitle(),
                                        'holiday' => null,
                                        'freeday' => $freeday,
                                        'changedDay' => null,
                                        'locked' => $locked,
                                        'classNumber' => $classNumber,
                                        'date' => $holiday->getNewDate(),
                                        'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                        'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                        'session' => $freeday->getNewSession(),
                                        'course' => $courseScheduleItem->getTaughtCourse()
                                    ];
                                    $classNumber++;
                                }
                            }
//                        }
                        }
                    }
//                    $periodCount++;
                }
//                }
                $itemIndex++;
            }
        }

        return $courseDays;
    }

//    public function getScheduleItemDaysForMonth(ScheduleItem $courseScheduleItem, $year, $month) {
//        $beginDate = new \DateTime();
//        $beginDate->setDate($year, $month, 1);
//        $endDate = clone $beginDate;
//        $endDate->modify('last day of this month');
//        return $this->getScheduleItemDaysForInterval($teacherWorkItem, $courseScheduleItem, $beginDate, $endDate);
//    }

    /* this function gets an array of course sessions between begindate and enddate for schedule items to be displayed in teacher's journal */
    public function getScheduleItemDaysForInterval($teacherWorkItem, ScheduleItem $courseScheduleItem, \DateTime $beginDate, \DateTime $endDate): ?array {
        $siwsiMode = false;
        $courseDays = [];
        $semesterDays = $this->getDaysFromDateIntervals($beginDate, $endDate);
        $freedayRepository = $this->getDoctrine()->getRepository(Freeday::class);
//        $freedays = $freedayRepository->findAll();
//        echo "ID: " . $teacherWorkItem->getDepartment()->getSystemId() . "<br>";
        $boolLLD = false;
        if ($teacherWorkItem->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
            $freedays = $freedayRepository->findBy(['type' => [3, 4]]);
            $boolLLD = true;
        } else {
            $freedays = $freedayRepository->findBy(['type' => [1, 2, 5]]);
        }
        $periodCounts = [1, 1, 1, 1, 1, 1, 1, 1];
        $locked = false;
        $itemNumber = 1;
        foreach ($semesterDays as $semesterDay) {
            //echo "DAY: " . $semesterDay->format('Y-m-d') . "<br>";
            $itemIndex = 0;
            $date1 = $courseScheduleItem->getStartDate();
            $date2 = $courseScheduleItem->getEndDate();
            $weekdayNumber = $semesterDay->format("N");
            if ($weekdayNumber == $courseScheduleItem->getDay() && ($semesterDay >= $date1 && $semesterDay < $date2)) {
                $periodCounts[$itemIndex]++;
                if ($periodCounts[$itemIndex] % $courseScheduleItem->getPeriod() == 0) {
                    $scheduleChanges = $courseScheduleItem->getScheduleChanges();
                    $classNumber = $courseScheduleItem->getSession();
                    $changeType = ScheduleChangeTypeEnum::NONE;
                    $changed = false;
                    $changedDay = null;
                    $holiday = null;
                    foreach ($scheduleChanges as $scheduleChange) {
                        //echo $scheduleChange->getId() . " " . $scheduleChange->getScheduleItem()->getId();
                        //&& $courseScheduleItem->getSession() == $scheduleChange->getSession()
                        if ($scheduleChange->getDate() == $semesterDay) {
                            //echo " counted<br>";
                            $changedDay = $scheduleChange;
                            $changed = true;
                            $changeType = ScheduleChangeTypeEnum::REPLACEMENT;
                            break;
                        }
                        //echo "<br>";
                    }

                    $holiday = null;
                    $freeday = null;
                    if ($changeType == ScheduleChangeTypeEnum::NONE) {
                        foreach ($freedays as $freeday) {
                            if ($boolLLD) {
                                if ($freeday->getType() == FreedayTypeEnum::NOREPLACEMENT_LLD && $freeday->getDate() == $semesterDay) {
                                    $holiday = $freeday;
                                    $changed = true;
                                    $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                    break;
                                }
                                if ($freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT && $freeday->getDate() == $semesterDay && $courseScheduleItem->getSession() == $freeday->getSession()) {
//                                                echo "added";
                                    $holiday = $freeday;
                                    $changed = true;
                                    $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                    break;
                                }
                            } else {
                                if ($freeday->getType() == FreedayTypeEnum::NOREPLACEMENT && $freeday->getDate() == $semesterDay) {
                                    $holiday = $freeday;
                                    $changed = true;
                                    $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                    break;
                                }
                                if ($freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT_BM && $freeday->getDate() == $semesterDay && $courseScheduleItem->getSession() == $freeday->getSession()) {
//                                    echo "added ".$freeday->getDate()->format('d.m.Y')." session:".$freeday->getSession();
                                    $holiday = $freeday;
                                    $changed = true;
                                    $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                    //$locked = false;
                                    break;
                                }
                                if ($freeday->getType() == FreedayTypeEnum::REPLACEMENT && $freeday->getDate() == $semesterDay) {
                                    $holiday = $freeday;
                                    $changed = true;
                                    $changeType = ScheduleChangeTypeEnum::FREEDAY;
                                    break;
                                }
                            }
                        }
                    }



                    $courseDays[] = [
                        'changed' => $changed,
                        'scheduleItem' => $courseScheduleItem,
                        'coverday' => null,
                        'coverdayTitle' => '',
                        'holiday' => $holiday,
                        'freeday' => $freeday,
                        'changedDay' => null,
                        'locked' => $locked,
                        'itemNumber' => $itemNumber,
                        'classNumber' => $classNumber,
                        'date' => $semesterDay,
                        'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                        'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                        'session' => $courseScheduleItem->getSession(),
                        'course' => $courseScheduleItem->getTaughtCourse(),
                        'title' => 'Topic of class',
                    ];


                    if ($changeType == ScheduleChangeTypeEnum::REPLACEMENT) {
                        $changed = false;
                        $courseDays[] = [
                            'changed' => $changed,
                            'scheduleItem' => $courseScheduleItem,
                            'coverday' => $changedDay->getDate(),
                            'coverdayTitle' => $changedDay->getDate()->format('d.m.Y') . " Session:" . $changedDay->getSession() . " (" . $changedDay->getDataField("note") . ")",
                            'holiday' => null,
                            'freeday' => $freeday,
                            'changedDay' => $changedDay,
                            'locked' => $locked,
                            'itemNumber' => $itemNumber,
                            'classNumber' => $classNumber,
                            'date' => $changedDay->getNewDate(),
                            'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                            'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                            'session' => $courseScheduleItem->getSession(),
                            'course' => $courseScheduleItem->getTaughtCourse(),
                            'title' => 'Topic of class',
                        ];
                    }

                    if ($changeType == ScheduleChangeTypeEnum::FREEDAY) {
                        $changed = false;
                        $changedDay = null;
                        //$holiday = null;

                        if ($freeday->getType() == FreedayTypeEnum::REPLACEMENT ||
                                $freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT ||
                                $freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT_BM) {
                            foreach ($scheduleChanges as $scheduleChange) {
                                //echo $scheduleChange->getId() . " " . $scheduleChange->getScheduleItem()->getId();
                                //&& $courseScheduleItem->getSession() == $scheduleChange->getSession()
                                if ($scheduleChange->getDate() == $holiday->getNewDate() && $scheduleChange->getSession() == $holiday->getNewSession()) {
                                    //echo " counted<br>";
                                    $changedDay = $scheduleChange;
                                    $changed = true;
                                    $changeType = ScheduleChangeTypeEnum::REPLACEMENT;
                                    break;
                                }
                                //echo "<br>";
                            }
                        }

                        if ($freeday->getType() == FreedayTypeEnum::REPLACEMENT) {
                            $courseDays[] = [
                                'changed' => $changed,
                                'scheduleItem' => $courseScheduleItem,
                                'coverday' => $freeday->getDate(),
                                'coverdayTitle' => $freeday->getTitle(),
                                'holiday' => null,
                                'freeday' => $freeday,
                                'changedDay' => $changedDay,
                                'locked' => $locked,
                                'itemNumber' => $itemNumber,
                                'classNumber' => $classNumber,
                                'date' => $holiday->getNewDate(),
                                'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                'session' => $courseScheduleItem->getSession() + 4, //add 4 pairs so that it doesn't add absence for the same session on that day for the same course
                                'course' => $courseScheduleItem->getTaughtCourse(),
                                'title' => 'Topic of class',
                            ];
                        }
//                        echo "Freeday session:" . $freeday->getSession() . " Course session:" . $courseScheduleItem->getSession() . "<br>";
                        if (($freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT || $freeday->getType() == FreedayTypeEnum::SESSION_REPLACEMENT_BM) && $freeday->getSession() == $courseScheduleItem->getSession()) {
//                            if ($boolLLD) {
                            $courseDays[] = [
                                'changed' => $changed,
                                'scheduleItem' => $courseScheduleItem,
                                'coverday' => $freeday->getDate(),
                                'coverdayTitle' => $freeday->getTitle() . " Session:" . $holiday->getSession(),
                                'holiday' => null,
                                'freeday' => $freeday,
                                'changedDay' => $changedDay,
                                'locked' => $locked,
                                'itemNumber' => $itemNumber,
                                'classNumber' => $classNumber,
                                'date' => $holiday->getNewDate(),
                                'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                'session' => $freeday->getNewSession(), //add 4 pairs so that it doesn't add absence for the same session on that day for the same course
                                'course' => $courseScheduleItem->getTaughtCourse(),
                                'title' => 'Topic of class',
                            ];
//                            }
                        }

                        if ($changeType == ScheduleChangeTypeEnum::REPLACEMENT) {
                            $changed = false;
                            $courseDays[] = [
                                'changed' => $changed,
                                'scheduleItem' => $courseScheduleItem,
                                'coverday' => $changedDay->getDate(),
                                'coverdayTitle' => $changedDay->getDate()->format('d.m.Y') . " Session:" . $changedDay->getSession() . " (" . $changedDay->getDataField("note") . ")",
                                'holiday' => null,
                                'freeday' => $freeday,
                                'changedDay' => $changedDay,
                                'locked' => $locked,
                                'itemNumber' => $itemNumber,
                                'classNumber' => $classNumber,
                                'date' => $changedDay->getNewDate(),
                                'classType' => $courseScheduleItem->getClassType()->getLetterCode(),
                                'classTypeId' => $courseScheduleItem->getClassType()->getId(),
                                'session' => $changedDay->getNewSession(),
                                'course' => $courseScheduleItem->getTaughtCourse(),
                                'title' => 'Topic of class',
                            ];
                        }
                    }
                    if (!$changed) {
                        $itemNumber++;
                    }
//                        }
                }
            }
//                }
            $itemIndex++;
        }

        return $courseDays;
    }

    public function getScheduleItemViewColumns(TeacherWorkItem $teacherWorkItem, $year, $semester, $months, $fromJournal = false) {
        $scheduleItemViewColumns = [];
        $scheduleItems = $teacherWorkItem->getScheduleItems();
        if ($fromJournal) {
            if ($teacherWorkItem->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                $allMonths = $this->systemInfoManager->getTrimesterMonths($semester);
            } else {
                $allMonths = $this->systemInfoManager->getSemesterMonths($semester);
            }
        } else {
            $allMonths = $months;
        }

        if ($teacherWorkItem->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
//            echo "This is LLD<br>";
            $beginDate = $this->systemInfoManager->getTrimesterBeginDate($semester);
            $endDate = $this->systemInfoManager->getTrimesterEndDate($semester);
        } else {
            $beginDate = $this->systemInfoManager->getSemesterBeginDate($semester);
            $endDate = $this->systemInfoManager->getSemesterEndDate($semester);
        }
        $topicIndex = [
            ClassTypeEnum::LECTURE => 0,
            ClassTypeEnum::SEMINAR => 0,
            ClassTypeEnum::PRACTICE => 0,
            ClassTypeEnum::LAB => 0,
            ClassTypeEnum::CLASSHOUR => 0,
            ClassTypeEnum::LANGUAGE => 0,
        ];

        $topics = [];
        $totalCourseDays = [
            ClassTypeEnum::LECTURE => [],
            ClassTypeEnum::SEMINAR => [],
            ClassTypeEnum::PRACTICE => [],
            ClassTypeEnum::LAB => [],
            ClassTypeEnum::CLASSHOUR => [],
            ClassTypeEnum::LANGUAGE => [],
        ];
        foreach ($scheduleItems as $scheduleItem) {
//            echo $scheduleItem->getSchedule()->getId() . " Session:" . $scheduleItem->getSession() . "<br>";
            $replacedSessions = [];
            $totalSum = 0;
            $monthCourseDays = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => []];
            $monthReplacedSessions = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => []];
            $monthSums = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
            $monthlyWorkItems = [];
            //echo $beginDate->format('d-m-Y') . "-" . $endDate->format('d-m-Y') . "<br>";
            $courseDays = $this->getScheduleItemDaysForInterval($teacherWorkItem, $scheduleItem, $beginDate, $endDate);

//calculate work sum
            $workSum = 0;
            foreach ($courseDays as $courseDay) {
                $replacedSession = null;
                if ($courseDay['holiday'] == null && $courseDay['changed'] == false) {
                    //echo $courseDay['date']->format('Y-m-d') . " :: " . $courseDay['course']->getNameEnglish();
                    $count = true;
                    if ($courseDay['changedDay']) { //if it is a changed schedule day
//                        if ($courseDay['changedDay']->getNewTeacher() != $courseDay['scheduleItem']->getTeacher()) {
                        //echo $courseDay['date']->format('Y-m-d') . " :: " . $courseDay['course']->getNameEnglish() . " " . $courseDay['changedDay']->getNewTeacher()->getFullname() . "\n";
                        $count = false;
                        $replacedSession = [
                            'teacher' => $courseDay['changedDay']->getNewTeacher(),
                            'courseDay' => $courseDay,
                            'hours' => $this->getHoursForClassType($courseDay),
                            'scheduleChange' => $courseDay['changedDay'],
                        ];
                        $replacedSessions[] = $replacedSession;
//                        }
                    }

                    if ($count) {
//                        echo " adding: " . $this->getHoursForClassType($courseDay) . "<br>";
                        $topicIndex[$courseDay['classTypeId']]++;
                        $courseDay['topicIndex'] = $topicIndex[$courseDay['classTypeId']];
                        $topic = [
                            'date' => $courseDay['date'],
                            'session' => $courseDay['session'],
                            'topic' => '',
                            'classTypeId' => $courseDay['classTypeId'],
                            'studentGroups' => $scheduleItem->getStudentGroups(),
                        ];

                        $topics[] = $topic;
                        if ($courseDay['date']) {
//                            echo "Class type:".$courseDay['classTypeId']." getHours:".$this->getHoursForClassType($courseDay['classTypeId'])."<br>";
                            //adds sum to month number N
                            $monthSums[$courseDay['date']->format('n')] += $this->getHoursForClassType($courseDay);
                        }
                        if ($courseDay['classTypeId'] == ClassTypeEnum::PRACTICE || $courseDay['classTypeId'] == ClassTypeEnum::SEMINAR || $courseDay['classTypeId'] == ClassTypeEnum::LANGUAGE) {
                            $totalCourseDays[ClassTypeEnum::PRACTICE][] = $courseDay['date']->format('Y-m-d');
                        } elseif ($courseDay['classTypeId'] == ClassTypeEnum::LECTURE) {
                            $totalCourseDays[ClassTypeEnum::LECTURE][] = $courseDay['date']->format('Y-m-d');
                        } elseif ($courseDay['classTypeId'] == ClassTypeEnum::LAB) {
                            $totalCourseDays[ClassTypeEnum::LAB][] = $courseDay['date']->format('Y-m-d');
                        }
                    }
                }
                if ($courseDay['date']) {
                    $monthCourseDays[$courseDay['date']->format('n')][] = $courseDay;
                    if ($replacedSession) {
                        $monthReplacedSessions[$courseDay['date']->format('n')][] = $replacedSession;
                    }
//                $monthSums[$courseDay['date']->format('n')] = $workSum;
                }
            }

            foreach ($allMonths as $month) {
                $date = \DateTime::createFromFormat('!m', $month);
//                $sortedMonthCourseDays = $this->array_orderby($monthCourseDays[$month], 'session', SORT_ASC);
//                usort($sortedMonthCourseDays, function($a, $b) {return $a['session'] > $b['session'];}); 
//                foreach ($monthCourseDays[$month] as $cDay) {
//                    if ($cDay['classTypeId'] == ClassTypeEnum::LECTURE) {
//                        echo " Session:" . $cDay['session'] . " day:" . $cDay['date']->format("Y-m-d") . " Course:" . $cDay['scheduleItem']->getTaughtCourse()->getNameEnglish() . "<br>";
//                    }
//                }

                $monthlyWorkItem = [
                    'monthNumber' => $month,
                    'monthName' => $date->format('F'),
                    'courseDays' => $monthCourseDays[$month],
                    'monthSum' => $monthSums[$month],
                    'replacedSessions' => $monthReplacedSessions[$month],
                ];
//                echo "Month :" . $month . " Sum:" . $monthSums[$month]."<br>";

                $monthlyWorkItems[] = $monthlyWorkItem;
                $totalSum += $monthSums[$month];
            }

            $scheduleItemViewColumn = [
                'teacherWorkItem' => $teacherWorkItem,
                'scheduleItem' => $scheduleItem,
                'classTypeId' => $scheduleItem->getClassType()->getSystemId(),
                'classType' => $scheduleItem->getClassType()->getNameEnglish(),
                'groupNames' => $this->getGroupNamesFromIds($scheduleItem->getStudentGroups()),
                'monthlyWorkItems' => $monthlyWorkItems,
                'totalSum' => $totalSum,
                'replacedSessions' => $replacedSessions,
            ];
            //echo "CM: Size of replaced sessions: " . sizeof($replacedSessions) . " - ID= " . $teacherWorkItem->getId() . " --\n";
            $scheduleItemViewColumns[] = $scheduleItemViewColumn;
        }
        //echo "SIVC count3:" . sizeof($scheduleItemViewColumns)." ";

        $sortedTopics = $this->array_orderby($topics, 'date', SORT_ASC);
        $taughtCourse = $teacherWorkItem->getTaughtCourse();
        if ($taughtCourse) {
            $lectureTopics = $taughtCourse->getLectureTopics();
            $practiceTopics = $taughtCourse->getPracticeTopics();
            $labTopics = $taughtCourse->getLabTopics();
        }

// combine similar class types for the same class to one view column.
        $previousClassTypeId = -1;
        $previousClassDay = -1;
        $previousGroupNames = '';
        $combinedScheduleItemViewColumns = [];
        $i = -1;
        $scheduleItemViewColumns = $this->array_orderby($scheduleItemViewColumns, 'classTypeId', SORT_ASC, 'groupNames', SORT_ASC);
        foreach ($scheduleItemViewColumns as $scheduleItemViewColumn) {
// || $scheduleItemViewColumn['scheduleItem']->getDay() != $previousClassDay
//echo "Group names:".$scheduleItemViewColumn['groupNames']."<br>";
            if ($scheduleItemViewColumn['classTypeId'] != $previousClassTypeId || $scheduleItemViewColumn['groupNames'] != $previousGroupNames) {
                $combinedScheduleItemViewColumns[] = $scheduleItemViewColumn;
                $i++;
            } else {
                $z = 0;
                foreach ($combinedScheduleItemViewColumns[$i]['monthlyWorkItems'] as $monthlyWorkItem) {
                    //$combinedScheduleItemViewColumns[$i]['replacedSessions'] = array_merge($combinedScheduleItemViewColumns[$i]['replacedSessions'], $scheduleItemViewColumn['replacedSessions']);
                    $combinedScheduleItemViewColumns[$i]['monthlyWorkItems'][$z]['replacedSessions'] = array_merge($combinedScheduleItemViewColumns[$i]['monthlyWorkItems'][$z]['replacedSessions'], $scheduleItemViewColumn['monthlyWorkItems'][$z]['replacedSessions']);
                    $combinedScheduleItemViewColumns[$i]['monthlyWorkItems'][$z]['courseDays'] = array_merge($combinedScheduleItemViewColumns[$i]['monthlyWorkItems'][$z]['courseDays'], $scheduleItemViewColumn['monthlyWorkItems'][$z]['courseDays']);
                    $combinedScheduleItemViewColumns[$i]['monthlyWorkItems'][$z]['monthSum'] += $scheduleItemViewColumn['monthlyWorkItems'][$z]['monthSum'];
                    $combinedScheduleItemViewColumns[$i]['totalSum'] += $scheduleItemViewColumn['monthlyWorkItems'][$z]['monthSum'];
                    $z++;
                }
            }
            $previousClassTypeId = $scheduleItemViewColumn['classTypeId'];
            $previousClassDay = $scheduleItemViewColumn['scheduleItem']->getDay();
            $previousGroupNames = $scheduleItemViewColumn['groupNames'];
        }

        //echo "SIVC count2:" . sizeof($combinedScheduleItemViewColumns)." ".$taughtCourse->getNameEnglish();
        $d = 0;

        foreach ($combinedScheduleItemViewColumns as $combinedScheduleItemViewColumn) {
            //echo "CM2: Size " . sizeof($combinedScheduleItemViewColumn['replacedSessions']) . " - ID= " . $combinedScheduleItemViewColumn['teacherWorkItem']->getId() . " --\n";
            $t = 0;
            $lectureIndex = 0;
            $practiceIndex = 0;
            $labIndex = 0;
            foreach ($combinedScheduleItemViewColumns[$d]['monthlyWorkItems'] as $item) {
                $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'] = $this->array_orderby($combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'], 'date', SORT_ASC, 'session', SORT_ASC);
                $c = 0;
                foreach ($combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'] as $courseDay) {
                    //echo $courseDay['date']->format('d-m-Y') . " -- ";
//                    echo $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['date']->format('Y-m-d')."<br>";
//                    foreach ($sortedTopics as $topic) {
////                        && $topic['session'] == $courseDay['session'] 
//                        if ($topic['date'] == $courseDay['date'] && $topic['classTypeId'] == $courseDay['classTypeId']) {
////                            echo "found topic".$topic['topic']."<br>";
//                            $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['title'] = $topic['topic'];
//                            break;
//                        }
//                    }
                    if ($courseDay['changed'] == false) {
                        //$dateNeedle = $courseDay['date']->format('Y-m-d');
                        if ($courseDay['classTypeId'] == ClassTypeEnum::PRACTICE || $courseDay['classTypeId'] == ClassTypeEnum::SEMINAR || $courseDay['classTypeId'] == ClassTypeEnum::LANGUAGE) {
                            //$practiceIndex = array_search($dateNeedle, $totalCourseDays[ClassTypeEnum::PRACTICE]);
                            //echo "adding:" . $practiceIndex . " -- " . $practiceTopics[$practiceIndex];
                            if (isset($practiceTopics[$practiceIndex])) {
                                $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['title'] = $practiceTopics[$practiceIndex];
                            } else {
                                $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['title'] = 'Lesson topic ' . $practiceIndex;
                            }
                            $practiceIndex++;
                        } elseif ($courseDay['classTypeId'] == ClassTypeEnum::LECTURE) {
                            //$lectureIndex = array_search($dateNeedle, $totalCourseDays[ClassTypeEnum::LECTURE]);
                            // echo "adding:" . $lectureIndex . " -- " . $lectureTopics[$lectureIndex] . " <br>";
                            if (isset($lectureTopics[$lectureIndex])) {
                                $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['title'] = $lectureTopics[$lectureIndex];
                            } else {
                                $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['title'] = 'Lesson topic ' . $lectureIndex;
                            }
                            $lectureIndex++;
                        } elseif ($courseDay['classTypeId'] == ClassTypeEnum::LAB) {
                            //$labIndex = array_search($dateNeedle, $totalCourseDays[ClassTypeEnum::LAB]);
                            if (isset($labTopics[$labIndex])) {
                                $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['title'] = $labTopics[$labIndex];
                            } else {
                                $combinedScheduleItemViewColumns[$d]['monthlyWorkItems'][$t]['courseDays'][$c]['title'] = 'Lesson topic ' . $labIndex;
                            }
                            $labIndex++;
//echo $topic['date']->format('Y-m-d') . "<br>";
                        }
                    }
//                    if ($practiceIndex > 16) {
//                        $practiceIndex = 0;
//                    }
                    $c++;
                    //echo "<br>";
                }
                $t++;
            }
            $d++;
        }
        //echo " ------- <br>";
        //echo "SIVC count1:" . sizeof($combinedScheduleItemViewColumns)." ".$taughtCourse->getNameEnglish();
        return $combinedScheduleItemViewColumns;
    }

    public function getReplacedScheduleItemViewColumns(TeacherWorkItem $teacherWorkItem, $year, $semester, $months) {
        $scheduleItemViewColumns = [];
        $scheduleChangeRepository = $this->getDoctrine()->getRepository(ScheduleChange::class);
        $scheduleChanges = $scheduleChangeRepository->findBy(["newTeacher" => $teacherWorkItem->getTeacher()]);
        $scheduleItems = [];
        $scheduleItemIds = [];
        foreach ($scheduleChanges as $scheduleChange) {
            if ($scheduleChange->getScheduleItem()->getTaughtCourse() == $teacherWorkItem->getTaughtCourse()) {
                if (!in_array($scheduleChange->getScheduleItem()->getId(), $scheduleItemIds)) {
                    $scheduleItems[] = $scheduleChange->getScheduleItem();
                    $scheduleItemIds[] = $scheduleChange->getScheduleItem()->getId();
                }
            }
        }
        $beginDate = $this->systemInfoManager->getSemesterBeginDate($semester);
        $endDate = $this->systemInfoManager->getSemesterEndDate($semester);
        foreach ($scheduleItems as $scheduleItem) {
            $totalSum = 0;
            $monthCourseDays = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => []];
            $monthSums = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
            $monthlyWorkItems = [];
            $courseDays = $this->getScheduleItemDaysForInterval($teacherWorkItem, $scheduleItem, $beginDate, $endDate);

//calculate work sum
            $workSum = 0;
            foreach ($courseDays as $courseDay) {
                if ($courseDay['holiday'] == null && $courseDay['changed'] == false) {
                    $count = false;
                    if ($courseDay['changedDay']) { //if it is a changed schedule day
                        if ($courseDay['changedDay']->getNewTeacher() == $teacherWorkItem->getTeacher()) {
                            $count = true;
                        }
                    }

                    if ($count) {
                        $monthSums[$courseDay['date']->format('n')] += $this->getHoursForClassType($courseDay);
                    }
                }

                $monthCourseDays[$courseDay['date']->format('n')][] = $courseDay;
//                $monthSums[$courseDay['date']->format('n')] = $workSum;
            }

            foreach ($months as $month) {
                $date = \DateTime::createFromFormat('!m', $month);
                $monthlyWorkItem = [
                    'monthNumber' => $month,
                    'monthName' => $date->format('F'),
                    'courseDays' => $monthCourseDays[$month],
                    'monthSum' => $monthSums[$month],
                ];

                $monthlyWorkItems[] = $monthlyWorkItem;
                $totalSum += $monthSums[$month];
            }

            $scheduleItemViewColumn = [
                'scheduleItem' => $scheduleItem,
                'classType' => $scheduleItem->getClassType()->getNameEnglish(),
                'groupNames' => $this->getGroupNamesFromIds($scheduleItem->getStudentGroups()),
                'monthlyWorkItems' => $monthlyWorkItems,
                'totalSum' => $totalSum,
            ];
            $scheduleItemViewColumns[] = $scheduleItemViewColumn;
        }
        return $scheduleItemViewColumns;
    }

    public function getDaysFromInterval(string $beginDate, string $endDate) {
        $semesterBeginDate = new \DateTime($beginDate);
        $semesterEndDate = new \DateTime($endDate);
        $interval = \DateInterval::createFromDateString('1 day');
        return new \DatePeriod($semesterBeginDate, $interval, $semesterEndDate);
    }

    public function getDaysFromDateIntervals(\DateTime $beginDate, \DateTime $endDate) {
        $interval = \DateInterval::createFromDateString('1 day');
        return new \DatePeriod($beginDate, $interval, $endDate);
    }

    public function getWorkItemData(TeacherWorkItem $teacherWorkItem, $year, $semester, $month, $empty_sum) {
        $semesterBeginDate = $this->systemInfoManager->getSemesterBeginDate($semester);
        $semesterEndDate = $this->systemInfoManager->getSemesterEndDate($semester);
        $semesterFinalEndDate = $this->systemInfoManager->getSemesterFinalEndDate($semester);
        $semesterFourthYearEndDate = $this->systemInfoManager->getSemesterFourthYearEndDate($semester);
        $lldSecondTrimester = false;
        if ($teacherWorkItem->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
            $semesterBeginDate = $this->systemInfoManager->getTrimesterBeginDate($semester);
            $semesterEndDate = $this->systemInfoManager->getTrimesterEndDate($semester);
            $semesterFinalEndDate = $this->systemInfoManager->getTrimesterFinalEndDate($semester);
            $lldSecondTrimester = ($semester == 2);
        }
        //echo "Begin date:" . $semesterBeginDate->format("Y-m-d") . " End date:" . $semesterEndDate->format("Y-m-d") . "<br>";
        $date1 = new \DateTime();
        //if month is January then increase year + 1 because it is next year but first semester.
        $date1->setDate($year, $month, 1);
        if ($lldSecondTrimester) {
            if ($month >= 1 && $month <= 3) {
                $date1->setDate($year + 1, $month, 1);
            }
        } else {
            if ($month == 1) {
                $date1->setDate($year + 1, $month, 1);
            }
        }

        if ($date1 < $semesterBeginDate) {
            $date1 = $semesterBeginDate;
        }
        $date1->setTime(0, 0, 0, 0);

        $date2 = clone $date1;
//        $date2->modify('first day of next month');
        $date2->modify('last day of this month');
        if ($date2 > $semesterFinalEndDate) {
            $date2 = $semesterFinalEndDate;
        }
        $date2->setTime(0, 0, 0, 0);

//        if ($teacherWorkItem->isLastSemester()) {
////            echo "LAST SEMESTER: YES<br>";
//            if ($date2 > $semesterFourthYearEndDate) {
//                $date2 = $semesterFourthYearEndDate;
//            }
//        }
//        echo "Month: " . $month . " date1:" . $date1->format("Y-m-d") . " date2:" . $date2->format("Y-m-d") . "<br>";

        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $months = [];
        $months[] = $month;
        $reportedSums = $this->workColumnsManager->getEmptyWorkColumnsArray();
        $taughtCourse = $teacherWorkItem->getTaughtCourse();
        $isReplacement = ($teacherWorkItem->getTeacherWorkSet()->getWorkload() == WorkloadEnum::LOADREPLACEMENT);
        if ($taughtCourse) {
            if ($isReplacement) {
                $scheduleItemViewColumns = $this->getReplacedScheduleItemViewColumns($teacherWorkItem, $year, $semester, $months);
            } else {
                $scheduleItemViewColumns = $this->getScheduleItemViewColumns($teacherWorkItem, $year, $semester, $months);
            }
            $classTypeSums = [
                ClassTypeEnum::LECTURE => 0,
                ClassTypeEnum::PRACTICE => 0,
                ClassTypeEnum::SEMINAR => 0,
                ClassTypeEnum::LAB => 0,
                ClassTypeEnum::SIWSI => 0,
                ClassTypeEnum::LANGUAGE => 0,
            ];
            foreach ($scheduleItemViewColumns as $scheduleItemViewColumn) {
                $classTypeSums[$scheduleItemViewColumn['scheduleItem']->getClassType()->getSystemId()] += $scheduleItemViewColumn['totalSum'];
            }
            $reportedSums[WorkColumnEnum::LECTUREACTUAL] += $classTypeSums[ClassTypeEnum::LECTURE];
            $reportedSums[WorkColumnEnum::PRACTICEACTUAL] += $classTypeSums[ClassTypeEnum::PRACTICE] + $classTypeSums[ClassTypeEnum::SEMINAR] + + $classTypeSums[ClassTypeEnum::LANGUAGE];
            $reportedSums[WorkColumnEnum::LABACTUAL] += $classTypeSums[ClassTypeEnum::LAB];
            $reportedSums[WorkColumnEnum::SIWSI] += $classTypeSums[ClassTypeEnum::SIWSI];
        }
//calculate reported work sums
        $reportedWorks = $teacherWorkItem->getReportedWorks();
        $reportedWorkSum = 0;
//        if ($month > 5) {
//            $date2 = $semesterFinalEndDate;
//        }
        foreach ($reportedWorks as $reportedWork) {
//            if ($reportedWork->getId() == 1089) {
//                echo "HERE:" . $reportedWork->getDate()->format("Y-m-d H:i:s") . "==" . $date1->format("Y-m-d H:i:s") . "--" . $reportedWork->getDate()->format("m") . " --" . ($reportedWork->getDate() >= $date1 && $reportedWork->getDate() <= $date2) . "<br>";
//            }
            if ($reportedWork->getDate() >= $date1 && $reportedWork->getDate() <= $date2) {
                $reportedWorkSum += $reportedWork->getAmount();
                $reportedSums[$reportedWork->getType()] += $reportedWork->getAmount();
            }
        }

        for ($u = 9; $u < 29; $u++) {
            $reportedSums[29] += $reportedSums[$u];
        }

        return $reportedSums;
    }

    public function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    public function getGroupNamesFromIds(?string $groupIdString) {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupNames = [];
        $groupIds = explode(",", $groupIdString);
        foreach ($groupIds as $groupId) {
            $group = $groupRepository->findOneBy(['systemId' => $groupId]);
            if (!empty($group)) {
                $groupNames[] = $group->getLetterCode();
            }
        }

        return implode(", ", $groupNames);
    }

    public function getGroupNamesFromCodes(?string $groupCoursePairString) {
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

    public function getHoursForClassType($courseDay): int {
        switch ($courseDay['classTypeId']) {
            case ClassTypeEnum::LECTURE:
            case ClassTypeEnum::PRACTICE:
            case ClassTypeEnum::SEMINAR:
            case ClassTypeEnum::LAB:
                if ($courseDay['scheduleItem']->getSchedule()->getId() == 8)
                    return 1;
                else
                    return 2;
            case ClassTypeEnum::SIWSI:
            case ClassTypeEnum::LANGUAGE:
                return 1;
            default:
                return 0;
        }
    }

    public function getScheduleItemFullDays(TeacherWorkItem $teacherWorkItem, $year, $semester) {
        $scheduleItems = $teacherWorkItem->getScheduleItems();
        if ($teacherWorkItem->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
//            echo "This is LLD<br>";
            $beginDate = $this->systemInfoManager->getTrimesterBeginDate($semester);
            $endDate = $this->systemInfoManager->getTrimesterEndDate($semester);
        } else {
            $beginDate = $this->systemInfoManager->getSemesterBeginDate($semester);
            $endDate = $this->systemInfoManager->getSemesterEndDate($semester);
        }
    }

    public function getReplacedSessionsSums(TeacherWorkItem $teacherWorkItem, $year, $semester, $month = null) {
        $result = $this->workColumnsManager->getEmptyWorkColumnsArray();
        $scheduleChangeRepository = $this->getDoctrine()->getRepository(ScheduleChange::class);
        $substitutions = $teacherWorkItem->getTeacherWorkSet()->getScheduleChanges($semester);
        foreach ($substitutions as $substitution) {
            if ($substitution->getScheduleItem()->getTaughtCourse() == $teacherWorkItem->getTaughtCourse()) {
                if ($substitution->getDate()->format('m') == $month) {
                    $hours = $substitution->getClassType()->getHours();
                    switch ($substitution->getClassType()->getSystemId()) {
                        case ClassTypeEnum::LECTURE:
                            $result[WorkColumnEnum::LECTUREACTUAL] += $hours;
                            break;
                        Case ClassTypeEnum::PRACTICE:
                        Case ClassTypeEnum::SEMINAR:
                            $result[WorkColumnEnum::PRACTICEACTUAL] += $hours;
                            break;
                        case ClassTypeEnum::LAB:
                            $result[WorkColumnEnum::LABACTUAL] += $hours;
                            break;
                        case ClassTypeEnum::SIWSI:
                            $result[WorkColumnEnum::SIWSI] += $hours;
                            break;
                        default:
                    }
                    $result[29] += $hours;
                }
            }
        }
        return $result;
    }

    public function getReplacedSessionsSumsForSemester(TeacherWorkItem $teacherWorkItem, $year, $semester) {
        $result = $this->workColumnsManager->getEmptyWorkColumnsArray();
        $scheduleChangeRepository = $this->getDoctrine()->getRepository(ScheduleChange::class);
        $substitutions = $teacherWorkItem->getTeacherWorkSet()->getScheduleChanges($semester);
        foreach ($substitutions as $substitution) {
            if ($substitution->getScheduleItem()->getTaughtCourse() == $teacherWorkItem->getTaughtCourse()) {
                $hours = $substitution->getClassType()->getHours();
                switch ($substitution->getClassType()->getSystemId()) {
                    case ClassTypeEnum::LECTURE:
                        $result[WorkColumnEnum::LECTUREACTUAL] += $hours;
                        break;
                    Case ClassTypeEnum::PRACTICE:
                    Case ClassTypeEnum::SEMINAR:
                        $result[WorkColumnEnum::PRACTICEACTUAL] += $hours;
                        break;
                    case ClassTypeEnum::LAB:
                        $result[WorkColumnEnum::LABACTUAL] += $hours;
                        break;
                    case ClassTypeEnum::SIWSI:
                        $result[WorkColumnEnum::SIWSI] += $hours;
                        break;
                    default:
                }
                $result[29] += $hours;
            }
        }
        return $result;
    }

}
