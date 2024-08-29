<?php

namespace App\Controller\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\TeacherAttendance;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Service\SystemEventManager;
use App\Service\CourseDaysManager;
use App\Service\SystemInfoManager;
use App\Repository\SettingRepository;

class AttendanceJournalController extends AbstractController {

    private $systemEventManager;
    private $courseDaysManager;
    private $systemInfoManager;
    private $settingsRepository;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager, CourseDaysManager $courseDaysManager, SettingRepository $settingRepository) {
        $this->systemEventManager = $systemEventManager;
        $this->courseDaysManager = $courseDaysManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->settingsRepository = $settingRepository;
    }

    /**
     * @Route("/faculty/attendancejournal/course/{id}/{beginDate?}/{endDate?}", name="faculty_attendance_journal_course")
     */
    public function courseAttendance(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $first_semester_year = $this->settingsRepository->findOneBy(['name' => 'first_semester_year'])->getValue();
        $year = $this->settingsRepository->findOneBy(['name' => 'current_year'])->getValue();
        $semester = $this->settingsRepository->findOneBy(['name' => 'current_semester'])->getValue();
        $beginDate = $request->attributes->get('beginDate');
        $endDate = $request->attributes->get('endDate');
        $teacher = null;
        $todayDate = new \DateTime();
        if (!$beginDate) {
            $beginDate = $todayDate->add(\DateInterval::createFromDateString('yesterday'))->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = $todayDate->format('Y-m-d');
        }

        $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
//$id = 1;
        $id = $request->attributes->get('id');
        if (!empty($id)) {
//$id = 1;
            $taughtCourse = $repository->find($id);
        }

        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupPacks = array();
        $group_ids = explode(",", $taughtCourse->getStudentGroups());
        $authorizedDeans = [];
        $authorizedDepartmentHeads = [];
        $authorizedAdvisors = [];
        foreach ($group_ids as $group_id) {
//echo $group_id;
            $group = $groupRepository->findOneBy(['systemId' => $group_id]);
            if (!empty($group)) {
//echo $group->getLetterCode();
                $groupPacks[] = [
                    'group' => $group,
                    'coursedays' => $this->courseDaysManager->getGroupCourseDays($group, $taughtCourse, 'course', $beginDate, $endDate)
                ];
                $dean = $group->getDean();
                $deputy_dean1 = $group->getFaculty()->getFirstDeputyDean();
                $deputy_dean2 = $group->getFaculty()->getSecondDeputyDean();
                $deputy_dean3 = $group->getFaculty()->getThirdDeputyDean();
                if (!in_array($dean, $authorizedDeans))
                    $authorizedDeans[] = $dean;
                if (!in_array($deputy_dean1, $authorizedDeans))
                    $authorizedDeans[] = $deputy_dean1;
                if (!in_array($deputy_dean2, $authorizedDeans))
                    $authorizedDeans[] = $deputy_dean2;
                if (!in_array($deputy_dean3, $authorizedDeans))
                    $authorizedDeans[] = $deputy_dean3;

                $department_head = $group->getDepartmentHead();
                if (!in_array($department_head, $authorizedDepartmentHeads))
                    $authorizedDepartmentHeads[] = $department_head;

                $advisor = $group->getAdvisor();
                if (!in_array($department_head, $authorizedAdvisors))
                    $authorizedAdvisors[] = $advisor;
            }
        }

        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        if ($teacher) {
            if ($teacher->getId() != $taughtCourse->getTeacher()->getId()) {
                if (!in_array($teacher, $authorizedDeans) && !in_array($teacher, $authorizedDepartmentHeads)) {
//$this->denyAccessUnlessGranted([]);
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TAUGHTCOURSE, $taughtCourse->getId(), '');
                    $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
//$groupPacks = [];
                }
            }
        } else {
            $this->denyAccessUnlessGranted([]);
//$groupPacks = [];
        }
//$teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
//$groupPacks = [];
        return $this->render('attendance/course.html.twig', [
                    'viewingTeacher' => $teacher,
                    'controller_name' => 'AttendanceController',
                    'first_semester_year' => $first_semester_year,
                    'year' => $year,
                    'semester' => $semester,
                    'course' => $taughtCourse,
                    'grouppacks' => $groupPacks,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'today' => date("Y-m-d"),
                    'todayDate' => $todayDate,
                    'beginDate' => $beginDate,
                    'endDate' => $endDate,
                    'deans' => $authorizedDeans,
                    'departmentHeads' => $authorizedDepartmentHeads
        ]);
    }

    /**
     * @Route("/faculty/attendancejournal/group/{id}/{beginDate?}/{endDate?}", name="faculty_attendance_journal_group")
     */
    public function groupAttendance(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");
        $teacher = null;
        $id = $request->attributes->get('id');
        $beginDate = $request->attributes->get('beginDate');
        $endDate = $request->attributes->get('endDate');
        $todayDate = new \DateTime();
        if (!$beginDate) {
            $beginDate = $todayDate->add(\DateInterval::createFromDateString('yesterday'))->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = $todayDate->format('Y-m-d');
        }

        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        if (!empty($id)) {
//$id = 1;
            $group = $groupRepository->findOneBy(['systemId' => $id]);
        }

        $groupPacks = array();
        $authorizedDeans = [];
        $authorizedDepartmentHeads = [];
        $authorizedAdvisors = [];
        if (!empty($group)) {
//echo $group->getLetterCode();
            $groupPacks[] = [
                'group' => $group,
                'coursedays' => $this->courseDaysManager->getGroupCourseDays($group, null, 'groupfacultyreport', $beginDate, $endDate)
            ];
            $authorizedDeans[] = $group->getDean();
            $authorizedDeans[] = $group->getFaculty()->getFirstDeputyDean();
            $authorizedDeans[] = $group->getFaculty()->getSecondDeputyDean();
            $authorizedDeans[] = $group->getFaculty()->getThirdDeputyDean();
            $authorizedDepartmentHeads[] = $group->getDepartmentHead();
            $authorizedAdvisors[] = $group->getAdvisor();

            if ($group->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                $periodBeginDate = $this->systemInfoManager->getTrimesterBeginDate($this->systemInfoManager->getCurrentTrimester());
            } else {
                $periodBeginDate = $this->systemInfoManager->getSemesterBeginDate($this->systemInfoManager->getCurrentSemester());
            }
            $periodBeginDate->add(\DateInterval::createFromDateString('yesterday'));
        }

        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        if ($teacher) {
            if (!in_array($teacher, $authorizedDeans) && !in_array($teacher, $authorizedDepartmentHeads)) {
//$this->denyAccessUnlessGranted([]);
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_GROUP, $group->getId(), 'Group attendance journal');
                $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
//$groupPacks = [];
            }
        } else {
            $this->denyAccessUnlessGranted([]);
//$groupPacks = [];
        }
//$teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
//$groupPacks = [];
        return $this->render('attendance/group.html.twig', [
                    'viewingTeacher' => $teacher,
                    'controller_name' => 'AttendanceController',
                    'course' => new TaughtCourse(),
                    'grouppacks' => $groupPacks,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'today' => date("Y-m-d"),
                    'todayDate' => $todayDate,
                    'beginDate' => $beginDate,
                    'endDate' => $endDate,
                    'deans' => $authorizedDeans,
                    'departmentHeads' => $authorizedDepartmentHeads,
                    'year1' => $this->systemInfoManager->getCurrentCommencementYear(),
                    'year2' => $this->systemInfoManager->getCurrentGraduationYear(),
                    'periodBeginDate' => $periodBeginDate,
        ]);
    }

    /**
     * @Route("/faculty/group/links", name="faculty_group_links")
     */
    public function groupAttendanceJournals(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");
        $content = '<div>';
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        $year1 = $this->systemInfoManager->getCurrentCommencementYear();
        $year2 = $year1 + 5;
        if ($teacher) {
            $faculty = $teacherRepository->getManagedFaculty(['teacher_id' => $teacher->getId()]);
            if ($faculty) {
                //if LLD
                if ($faculty->getSystemId() == 6) {
                    $departments = $faculty->getDepartments();
                    $content .= "<table class='table table-condensed'>";
                    foreach ($departments as $department) {
                        $content .= "<tr><td colspan=10><span style='font-weight:bold;'>Department:</span> " . $department->getNameEnglish() . "</td></tr>";
                        $groups = $department->getGroups();
                        foreach ($groups as $group) {
                            //echo $group->getLetterCode() . "<br>";
                            if ($group->getStatus() == 1) {
                                if ($group->getGraduationYear() > $year1 && $group->getGraduationYear() <= $year2) {
                                    $content .= "<tr><td></td><td></td><td>" . $group->getLetterCode() . "</td>";
                                    $content .= "<td><a href='/faculty/attendancejournal/group/" . $group->getSystemId() . "'>Attendance Journal</a></td>";
                                    $content .= "<td><a href='/faculty/attendance/group/" . $group->getId() . "'>Absences</a></td>";
                                    $content .= "<td><a href='/faculty/scheduledisplay/group/" . $group->getSystemId() . "'>Schedule</a></td>";
                                    $content .= "<td><a href='/interop/exporter/simplegrouplist/" . $group->getSystemId() . "'>Download simple list</a></td>";
                                    $content .= "<td><a href='/faculty/enrolledstudent/idcard/" . $group->getSystemId() . "'>Generate ID cards</a></td>";
                                    $content .= "</td></tr>";
                                }
                            }
                        }
                    }
                    $content .= "</table>";
                } else {
                    $departments = $faculty->getDepartments();
                    $content .= "<table class='table table-condensed'>";
                    foreach ($departments as $department) {
                        $studyPrograms = $department->getStudyPrograms();
                        $content .= "<tr><td colspan=10><span style='font-weight:bold;'>Department:</span> " . $department->getNameEnglish() . "</td></tr>";
                        foreach ($studyPrograms as $studyProgram) {
                            $content .= "<tr><td></td><td colspan=10><span style='font-weight:bold;'>Program:</span> " . $studyProgram->getNameEnglish() . " - " . $studyProgram->getApprovalYear() . "</td></tr>";
                            $groups = $studyProgram->getGroups();
                            foreach ($groups as $group) {
                                //echo $group->getLetterCode() . "<br>";
                                if ($group->getStatus() == 1) {
                                    if ($group->getGraduationYear() > $year1 && $group->getGraduationYear() < $year2) {
                                        $content .= "<tr><td></td><td></td><td>" . $group->getLetterCode() . "</td>";
                                        $content .= "<td><a href='/faculty/attendancejournal/group/" . $group->getSystemId() . "'>Attendance Journal</a></td>";
                                        $content .= "<td><a href='/faculty/attendance/group/" . $group->getId() . "'>Absences</a></td>";
                                        $content .= "<td><a href='/faculty/scheduledisplay/group/" . $group->getSystemId() . "'>Schedule</a></td>";
                                        $content .= "<td><a href='/interop/exporter/simplegrouplist/" . $group->getSystemId() . "'>Download simple list</a></td>";
                                        $content .= "<td><a href='/faculty/enrolledstudent/idcard/" . $group->getSystemId() . "'>Generate ID cards</a></td>";
                                        $content .= "</td></tr>";
                                    }
                                }
                            }
                        }
                    }
                    $content .= "</table>";
                }
            }
        } else {
            $content = 'No content found.';
        }
        $content .= '</div>';
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_FACULTY, $faculty->getId(), 'Faculty group list');
        return $this->render('attendance/index.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => $content,
                    'page_title' => 'My faculty groups'
        ]);
    }

    /**
     * @Route("/faculty/attendance/todayclasses", name="faculty_attendance_todayclasses")
     */
    public function todayClasses(Request $request) {
        $this->denyAccessUnlessGranted(['ROLE_DEAN', 'ROLE_DEPARTMENTHEAD', 'ROLE_SPECIALIST']);
        $today = new \DateTime();
        $todayFormat = date("Y-m-d");
        $weekdayNumber = $today->format("N");
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $scheduleItems = $scheduleItemRepository->findBy(["day" => $weekdayNumber]);

        $scheduleItemsData = [];
        $teacherAttendanceRepository = $this->getDoctrine()->getRepository(TeacherAttendance::class);
        foreach ($scheduleItems as $scheduleItem) {
            $teacherAttendance = $teacherAttendanceRepository->findScheduleByDay([
                'teacher_id' => $scheduleItem->getTeacher()->getId(),
                'day' => $scheduleItem->getDay(),
                'session' => $scheduleItem->getSession(),
                'date' => $todayFormat
            ]);
//            if (sizeof($teacherAttendances) > 0) {
//                $teacherAttendance = $teacherAttendances[0];
//            } else {
//                $teacherAttendance = null;
//            }

            $scheduleItemsData[] = ['scheduleItem' => $scheduleItem, 'teacherAttendance' => $teacherAttendance];
        }

        if ($this->isGranted("ROLE_SPECIALIST")) {
            $viewer = 'admin';
        } elseif ($this->isGranted("ROLE_DEAN")) {
            $viewer = 'dean';
        } elseif ($this->isGranted("ROLE_DEPARTMENTHEAD")) {
            $viewer = 'departmenthead';
        }

        return $this->render('attendance/teacherattendance.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'scheduleItems' => $scheduleItemsData,
                    'today' => date("Y-m-d"),
                    'viewer' => $viewer,
                    'report_title' => 'Today classes'
        ]);
    }

}
