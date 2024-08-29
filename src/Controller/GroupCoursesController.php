<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\StudentAbsenceManager;
use App\Service\TeacherAttendanceManager;
use App\Entity\ProgramCourse;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\EnrolledStudent;
use App\Entity\ClassType;
use App\Entity\StudentAbsence;
use App\Entity\Faculty;
use App\Entity\Department;
use App\Entity\TeacherAttendance;
use App\Entity\TeacherWorkItem;
use App\Entity\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class GroupCoursesController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/groupcourses/index", name="groupcourse_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'GroupCoursesController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/groupcourses/groupscheduleitemsall/{id}", name="custom_groupcourses_groupscheduleitemsall")
     */
    public function groupscheduleitemsall(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $id = $request->attributes->get('id');
        $content = '';
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $courseScheduleItems = $scheduleItemRepository->findGroupScheduleItemsAll($id);
        foreach ($courseScheduleItems as $courseScheduleItem) {
            $content .= $courseScheduleItem->getDay() . ":" . $courseScheduleItem->getSession();
            $content .= ", " . $courseScheduleItem->getStudentGroups();
            $content .= "<br>";
        }
        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/groupcourses/listcourses", name="custom_groupcourses_listcourses")
     */
    public function listCourses(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $id = $request->attributes->get('id');
        $content = '';
        $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $items = $repository->findAll();
        foreach ($items as $item) {
//            if ($item->getDataField('groupLetterCodes') == "") {
            $content .= $item->getFullname() . " : " . $item->getDataField('groupLetterCodes');
            $content .= ", <a href='/faculty/taughtcoursecustom/updategrouplettercodes/" . $item->getId() . "' target='new'>Update codes</a>";
            $content .= "<br>";
//                $groupIds = $item->getStudentGroups();
//                $letterCodes = $this->getGroupNamesFromCodes($groupIds);
//                $item->setDataField('groupLetterCodes', $letterCodes);
//                $repository->save($item);
//            }
        }

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'GroupCoursesController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/groupcourses/listcoursesbygroup", name="custom_groupcourses_listcoursesbygroup")
     */
    public function listCoursesByGroup(Request $request) {
//        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
//        $semesters = [1, 2];
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $repository->findAll();
        $content = "<table id='mainTable' class='table table-bordered'>";
        $content .= "<thead><tr><th>Topar</th>";
        $content .= "<th>Topar kody</th>";
        $content .= "<th>Talyp sany</th>";
        $content .= "<th>Hünär</th>";
        $content .= "<th>Semester</th>";
        $content .= "<th>Ders EN</th>";
        $content .= "<th>Ders</th>";
        $content .= "<th>Kafedra</th>";
        $content .= "<th>Jemi</th>";
        $content .= "<th>Umumy</th>";
        $content .= "<th>Amaly</th>";
        $content .= "<th>Tejribe</th>";
        $content .= "<th>Umumy Mugallym</th>";
        $content .= "<th>Amaly Mugallym</th>";
        $content .= "<th>Tejribe Mugallym</th>";
        $content .= "<th>Tapgyr</th>";
        $content .= "<th>Program Course ID</th>";
        $content .= "</tr></thead>";
        $content .= "<tbody>";
        $beginYear = $this->systemInfoManager->getCurrentCommencementYear();
        $endYear = $this->systemInfoManager->getCurrentCommencementYear() + 5;
        foreach ($groups as $group) {
            $totalStudentCount = $group->getTotalStudentCount();
            if ($group->getGraduationYear() > $beginYear && $group->getGraduationYear() < $endYear && $group->getStatus() == 1) {
                $studyProgram = $group->getStudyProgram();
                $programCourses = $studyProgram->getProgramCourses();
                foreach ($semesters as $semester) {
                    foreach ($programCourses as $programCourse) {
                        if (strpos($programCourse->getNameTurkmen(), "Önümçilik") === false && strpos($programCourse->getNameTurkmen(), "diplom işi") === false) {
                            if ($group->getGroupSemester($beginYear, $semester) == $programCourse->getSemester()) {
//                                if ($group->getSystemId() == 18121) {
                                $content .= "<tr>";
                                $content .= "<td>" . $group->getLetterCode() . "</td>";
                                $content .= "<td>" . $group->getSystemId() . "</td>";
                                $content .= "<td>" . $totalStudentCount . "</td>";
                                $content .= "<td>" . $studyProgram->getNameTurkmen() . " - " . $group->getStudyYear() . "</td>";
                                $content .= "<td>" . $programCourse->getSemester() . "</td>";
                                $content .= "<td>" . $programCourse->getNameEnglish() . "</td>";
                                $content .= "<td>" . $programCourse->getNameTurkmen() . "</td>";
                                $content .= "<td>" . $programCourse->getDepartment()->getNameEnglish() . "</td>";
                                $content .= "<td>0</td>";
                                $content .= "<td>" . $programCourse->getLectureHours() . "</td>";
                                $content .= "<td>" . $programCourse->getPracticeHours() . "</td>";
                                $content .= "<td>" . $programCourse->getLabHours() . "</td>";
                                $data = $this->getGroupCourseTeachers($group, $programCourse->getNameTurkmen(), $beginYear, $semester);
                                foreach ($data['teachers'] as $teacher) {
                                    if ($teacher) {
                                        $content .= "<td>" . $teacher->getFullname() . "</td>";
                                    } else {
                                        $content .= "<td></td>";
                                    }
                                }
                                if ($data['workitem']) {
                                    $content .= "<td>" . $data['workitem']->groupLetterCodes() . "</td>";
                                } else {
                                    $content .= "<td></td>";
                                }
                                $content .= "<td>" . $programCourse->getId() . "</td>";
                                $content .= "</tr>";
//                                }
                            }
                        }
                    }
                }
            }
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'GroupCoursesController',
                    'content' => $content,
        ]);
    }

    private function getGroupCourseTeachers($group, $courseTitle, $year, $semester) {
        $data = [
            'teachers' => [
                'lectureTeacher' => null,
                'practiceTeacher' => null,
                'labTeacher' => null,
            ],
            'workitem' => null
        ];

        $teacherWorkItemsRepository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $teacherWorkItems = $teacherWorkItemsRepository->findBy([
            'year' => $year,
            'semester' => $semester,
            'title' => $courseTitle,
        ]);
        foreach ($teacherWorkItems as $teacherWorkItem) {
//            echo $teacherWorkItem->getTitle() . ", " . $teacherWorkItem->getTeacher()->getFullname() . "<br>";
            if ($teacherWorkItem->checkTaughtGroup($group->getSystemId())) {
                if ($teacherWorkItem->getLectureHours() > 0) {
                    $data['teachers']['lectureTeacher'] = $teacherWorkItem->getTeacher();
                }
                if ($teacherWorkItem->getPracticeHours() > 0) {
                    $data['teachers']['practiceTeacher'] = $teacherWorkItem->getTeacher();
                }
                if ($teacherWorkItem->getLabHours() > 0) {
                    $data['teachers']['labTeacher'] = $teacherWorkItem->getTeacher();
                }
                $data['workitem'] = $teacherWorkItem;
            }
        }
        return $data;
    }

    /**
     * @Route("/groupcourses/listtaughtcoursesbygroup", name="custom_groupcourses_listtaughtcoursesbygroup")
     */
    public function listTaughtCoursesByGroup(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $repository->findAll();

        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourses = $taughtCourseRepository->findBy(['semester' => 2, 'year' => '2019']);

        $content = "<table id='mainTable' class='table table-striped'>";
        $content .= "<thead><tr><th>Topar</th><th>Ýyl</th><th>Semester</th><th>Course</th><th>Instructor</th></tr></thead>";
        $content .= "<tbody>";
        foreach ($groups as $group) {
            if ($group->getGraduationYear() < 2025 && $group->getGraduationYear() > 220 && $group->getStatus() == 1) {
//                echo $group->getLetterCode()."<br>";
                foreach ($taughtCourses as $taughtCourse) {
                    //echo $taughtCourse->getFullName()."- "<br>";
                    if ($taughtCourse->checkTaughtGroup($group->getSystemId())) {
                        $content .= "<tr>";
                        $content .= "<td>" . $group->getLetterCode() . "</td>";
                        $content .= "<td>" . $group->getStudyYear() . "</td>";
                        $content .= "<td>" . $taughtCourse->getSemester() . "</td>";
                        $content .= "<td>" . $taughtCourse->getNameEnglish() . "</td>";
                        $content .= "<td>" . $taughtCourse->getTeacherName() . "</td>";
                        $content .= "</tr>";
                    }
                }
            }
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'GroupCoursesController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/groupcourses/listteacherworkitemsbygroup", name="custom_groupcourses_listteacherworkitemsbygroup")
     */
    public function listTeacherWorkItemsByGroup(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $repository->findAll();

        $teacherWorkItemsRepository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $teacherWorkItems = $teacherWorkItemsRepository->findBy(['semester' => 1, 'year' => '2020']);

        $content = "<table id='mainTable' class='table table-striped'>";
        $content .= "<thead><tr><th>Topar</th><th>Ýyl</th><th>Semester</th><th>Title</th><th>Course</th><th>Lecture</th><th>Practice</th><th>Lab</th><th>Final</th><th>Groups</th><th>Count</th><th>Instructor</th></tr></thead>";
        $content .= "<tbody>";
        foreach ($groups as $group) {
            if ($group->getGraduationYear() < 2025 && $group->getGraduationYear() > 2020 && $group->getStatus() == 1) {
//                echo $group->getLetterCode()."<br>";
                foreach ($teacherWorkItems as $teacherWorkItem) {
                    //echo $taughtCourse->getFullName()."- "<br>";
                    if ($teacherWorkItem->getType() < 2 && ($teacherWorkItem->getLectureHours() > 0 || $teacherWorkItem->getPracticeHours() > 0 || $teacherWorkItem->getLabHours() > 0 || $teacherWorkItem->getFinalHours() > 0)) {
                        if ($teacherWorkItem->checkTaughtGroup($group->getSystemId())) {
                            $content .= "<tr>";
                            $content .= "<td>" . $group->getLetterCode() . "</td>";
                            $content .= "<td>" . $group->getStudyYear() . "</td>";
                            $content .= "<td>" . $teacherWorkItem->getSemester() . "</td>";
                            $content .= "<td>" . $teacherWorkItem->getTitle() . "</td>";
                            if ($teacherWorkItem->getTaughtCourse()) {
                                $content .= "<td>" . $teacherWorkItem->getTaughtCourse()->getNameEnglish() . "</td>";
                            } else {
                                $content .= "<td>none</td>";
                            }
                            $content .= "<td>" . $teacherWorkItem->getLectureHours() . "</td>";
                            $content .= "<td>" . $teacherWorkItem->getPracticeHours() . "</td>";
                            $content .= "<td>" . $teacherWorkItem->getLabHours() . "</td>";
                            $content .= "<td>" . $teacherWorkItem->getFinalHours() . "</td>";
                            $data = $this->getGroupNamesFromCodePairs($teacherWorkItem->getStudentGroups());
                            $content .= "<td>" . $data['names'] . "</td>";
                            $content .= "<td>" . $data['count'] . "</td>";
                            $content .= "<td>" . $teacherWorkItem->getTeacher()->getShortFullname() . "</td>";
                            $content .= "</tr>";
                        }
                    }
                }
            }
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'GroupCoursesController',
                    'content' => $content,
        ]);
    }

    private function getGroupNamesFromCodes(?string $groupIdString) {
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

    private function getGroupNamesFromCodePairs(?string $groupCoursePairString) {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupNames = [];
        $groupCoursePairs = explode(",", $groupCoursePairString);
        $totalStudents = 0;
        foreach ($groupCoursePairs as $groupCoursePair) {
            $groupCoursePairData = explode("-", $groupCoursePair);
            $group = $groupRepository->findOneBy(['systemId' => $groupCoursePairData[0]]);
            if (!empty($group)) {
                $groupNames[] = $group->getLetterCode();
                $totalStudents += $group->getTotalStudentCount();
            }
        }

        return ['names' => implode(", ", $groupNames), 'count' => $totalStudents];
    }

}
