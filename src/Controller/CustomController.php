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
use App\Utility\PDFDocument;
use App\Entity\TranscriptCourse;

class CustomController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/custom/index", name="custom_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/custom/checkduplicates/", name="custom_checkduplicates")
     */
    public function checkDuplicates(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);

        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $teachers = $teacherRepository->findAll();
        $scheduleItemsAll = array();
        foreach ($teachers as $teacher) {
            $scheduleItems = $scheduleItemRepository->findTeacherScheduleItemsAll($teacher->getId());
            $teacherSessions = array();
            foreach ($scheduleItems as $scheduleItem) {
                if (in_array($scheduleItem->getDay() . "_" . $scheduleItem->getSession() . "_" . $scheduleItem->getSchedule()->getId(), $teacherSessions)) {
                    $scheduleItemsAll[] = array('item' => $scheduleItem, 'status' => 'duplicate');
                } else {
                    $scheduleItemsAll[] = array('item' => $scheduleItem, 'status' => '');
                }
                $teacherSessions[] = $scheduleItem->getDay() . "_" . $scheduleItem->getSession() . "_" . $scheduleItem->getSchedule()->getId();
            }
        }
        return $this->render('custom/report.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'scheduleItems' => $scheduleItemsAll,
        ]);
    }

    /**
     * @Route("/custom/deletescheduleitem/{id}", name="custom_deletescheduleitem")
     */
    public function deleteScheduleItem(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $id = $request->attributes->get('id');
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);

        if (!empty($id)) {
            //$id = 1;
            $scheduleItem = $scheduleItemRepository->find($id);
            if ($scheduleItem) {
                //$scheduleItemRepository->remove($scheduleItem);
                $result = 'Item ' . $id . ' removed';
            } else {
                $result = 'Item ' . $id . ' not found';
            }
        } else {
            $result = 'Item ' . $id . ' not found';
        }

        return new Response($result);
    }

    /**
     * @Route("/custom/checkduplicateabsences/", name="custom_checkduplicate_absences")
     */
    public function checkDuplicateAbsences(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculties = $facultyRepository->findAll();
        $absences = [];
        foreach ($faculties as $faculty) {
            $departments = $faculty->getDepartments();
            foreach ($departments as $department) {
                $studyPrograms = $department->getStudyPrograms();
                foreach ($studyPrograms as $studyProgram) {
                    $groups = $studyProgram->getGroups();
                    foreach ($groups as $group) {
                        $groupAbsences = $group->getStudentAbsences();
                        $studentAbsences = [];
                        foreach ($groupAbsences as $absence) {
                            if (in_array($absence->getStudent()->getId() . "_" . $absence->getDate()->format("m") . "_" . $absence->getDate()->format("d") . "_" . $absence->getSession(), $studentAbsences)) {
                                $absences[] = array('item' => $absence, 'status' => 'duplicate', 'code' => $absence->getStudent()->getId() . "_" . $absence->getDate()->format("m") . "_" . $absence->getDate()->format("d") . "_" . $absence->getSession());
                            } else {
                                $absences[] = array('item' => $absence, 'status' => '', 'code' => $absence->getStudent()->getId() . "_" . $absence->getDate()->format("m") . "_" . $absence->getDate()->format("d") . "_" . $absence->getSession());
                            }
                            $studentAbsences[] = $absence->getStudent()->getId() . "_" . $absence->getDate()->format("m") . "_" . $absence->getDate()->format("d") . "_" . $absence->getSession();
                        }
                    }
                }
            }
        }
        return $this->render('custom/absencereport.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'absences' => $absences,
        ]);
    }

    /**
     * @Route("/custom/deleteabsence/{id}", name="custom_deleteabsence")
     */
    public function deleteAbsence(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $id = $request->attributes->get('id');
        $studentAbsenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);

        if (!empty($id)) {
            //$id = 1;
            $studentAbsence = $studentAbsenceRepository->find($id);
            if ($studentAbsence) {
                $studentAbsenceRepository->remove($studentAbsence);
                $result = 'Item ' . $id . ' removed';
            } else {
                $result = 'Item ' . $id . ' not found';
            }
        } else {
            $result = 'Item ' . $id . ' not found';
        }

        return new Response($result);
    }

    /**
     * @Route("/custom/liststudentsbygroup", name="custom_liststudentsbygroup")
     */
    public function listStudentsByGroup(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $repository->findAll();

        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $students = $studentRepository->findAll();

        $content = "<table id='mainTable' class='table table-striped table-compact table-sm'>";
        $content .= "<thead><tr>";
        $content .= "<th>Umumy t/b</th>";
        $content .= "<th>T/b</th>";
        $content .= "<th>Ýyl</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Gender</th>";
        $content .= "<th>Region</th>";
        $content .= "</tr></thead>";
        $content .= "<tbody>";
        $totalNumber = 1;
        foreach ($groups as $group) {
            if ($group->getGraduationYear() < 2026 && $group->getGraduationYear() > 2020 && $group->getStatus() == 1) {
//                echo $group->getLetterCode()."<br>";
                $students = $group->getStudents();
                $groupNumber = 1;
                foreach ($students as $student) {
                    //echo $taughtCourse->getFullName()."- "<br>";
                    $content .= "<tr>";
                    $content .= "<td>" . $totalNumber . "</td>";
                    $content .= "<td>" . $groupNumber . "</td>";
                    $content .= "<td>" . $student->getThreenames() . "</td>";
                    $content .= "<td>" . $group->getStudyYear() . "</td>";
                    //$content .= "<td>" . $group->getStudyProgram()->getNameTurkmen() . "</td>";
                    $content .= "<td>" . $group->getLetterCode() . "</td>";
                    $content .= "<td>" . $group->getSystemId() . "</td>";
                    //$content .= "<td>" . $student->getDataField('address') . "</td>";
                    $content .= "<td>" . $student->getSystemId() . "</td>";
                    $content .= "<td>" . $student->getGender() . "</td>";
                    $content .= "<td>" . mb_strtoupper($student->getRegion()->getLetterCode()) . "</td>";
                    $content .= "</tr>";
                    $groupNumber++;
                    $totalNumber++;
                }
            }
//            $group->setDepartment($group->getStudyProgram()->getDepartment());
//            $repository->save($group);
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'CustomController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/custom/photocheck", name="custom_photocheck")
     */
    public function photoCheck(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $repository->findAll();

        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $students = $studentRepository->findAll();

        $content = "<table id='mainTable' class='table table-striped table-compact table-sm'>";
        $content .= "<thead><tr>";
        $content .= "<th>Umumy t/b</th>";
        $content .= "<th>T/b</th>";
        $content .= "<th>Ýyl</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Gender</th>";
        $content .= "<th>Photo Exists</th>";
        $content .= "</tr></thead>";
        $content .= "<tbody>";
        $totalNumber = 1;
        $photoPath = $this->systemInfoManager->getPublicBuildPath() . "/photos/";
        foreach ($groups as $group) {
            if ($group->getGraduationYear() < 2026 && $group->getGraduationYear() > 2020 && $group->getStatus() == 1) {
//                echo $group->getLetterCode()."<br>";
                $students = $group->getStudents();
                $groupNumber = 1;
                foreach ($students as $student) {
                    //echo $taughtCourse->getFullName()."- "<br>";
                    $content .= "<tr>";
                    $content .= "<td>" . $totalNumber . "</td>";
                    $content .= "<td>" . $groupNumber . "</td>";
                    $content .= "<td>" . $student->getThreenames() . "</td>";
                    $content .= "<td>" . $group->getStudyYear() . "</td>";
                    //$content .= "<td>" . $group->getStudyProgram()->getNameTurkmen() . "</td>";
                    $content .= "<td>" . $group->getLetterCode() . "</td>";
                    $content .= "<td>" . $group->getSystemId() . "</td>";
                    //$content .= "<td>" . $student->getDataField('address') . "</td>";
                    $content .= "<td>" . $student->getSystemId() . "</td>";
                    $content .= "<td>" . $student->getGender() . "</td>";
                    $content .= "<td>" . file_exists($photoPath . $group->getSystemId() . "./" . $student->getSystemId() . ".jpg") . "</td>";
                    $content .= "</tr>";
                    $groupNumber++;
                    $totalNumber++;
                }
            }
//            $group->setDepartment($group->getStudyProgram()->getDepartment());
//            $repository->save($group);
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'CustomController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/custom/updatetaughtcourse", name="custom_updatetaughtcourse")
     */
//    public function updateTaughtCourse(Request $request) {
//        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
//        $id = $request->attributes->get('id');
//        $semesters = [2];
//        $content = '';
//        $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
//        $taughtCourses = $repository->findBy(['nameEnglish' => null]);
//        foreach ($taughtCourses as $taughtCourse) {
//            $taughtCourse->setNameEnglish($taughtCourse->getDataField("course_name"));
//            $content .= "Course updated:" . $taughtCourse->getNameEnglish() . "<br>";
//            //$repository->save($taughtCourse);
//        }
//        return $this->render('custom/index.html.twig', [
//                    'controller_name' => 'CustomController',
//                    'content' => $content,
//        ]);
//    }

    /**
     * @Route("/custom/tc", name="custom_tc")
     */
    public function updateTransriptCourse(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $repository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $transcriptCourses = $repository->findBy(['groupCode' => 0]);
        $content = '';
        foreach ($transcriptCourses as $tc) {
            $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $student = $studentRepository->findOneBy(['systemId' => $tc->getStudentId()]);
                $tc->setGroupCode($student->getGroupCode());
                $content .= 'updated:' . $student->getSystemId() . "<br>";
                $repository->save($tc);
        }
        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'CustomController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/custom/listmigration", name="custom_listmigration")
     */
    public function listMigration(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $repository->findBy([], ['departmentCode' => 'ASC', 'graduationYear' => 'DESC']);

        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $students = $studentRepository->findAll();

        $content = "<table id='mainTable' class='table table-striped table-compact table-sm'>";
        $content .= "<thead><tr>";
        $content .= "<th>Umumy t/b</th>";
        $content .= "<th>T/b</th>";
        $content .= "<th>Ýyl</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "</tr></thead>";
        $content .= "<tbody>";
        $totalNumber = 1;
        $year1 = 0;
        $year2 = 4;
        foreach ($groups as $group) {
            if ($group->getStudyYear() >= $year1 && $group->getStudyYear() <= $year2 && $group->getStatus() == 1) {
//                echo $group->getLetterCode()."<br>";
                $students = $group->getStudents();
                $groupNumber = 1;
                foreach ($students as $student) {
                    //echo $taughtCourse->getFullName()."- "<br>";
                    $content .= "<tr>";
                    $content .= "<td>" . $group->getLetterCode() . "</td>";
                    $content .= "<td>" . $groupNumber . "</td>";
                    $content .= "<td>" . $student->getSystemId() . "</td>";
                    $content .= "<td>" . $totalNumber . "</td>";
                    $content .= "<td>" . $student->getThreenames() . "</td>";
                    $content .= "<td>" . $student->getDataField('dob') . "</td>";
                    $content .= "<td>" . $student->getNationality()->getNameTurkmen() . "</td>";
                    $content .= "<td>" . $group->getStudyYear() . "</td>";
                    $content .= "<td>" . $group->getStudyProgram()->getNameTurkmen() . "</td>";
                    $content .= "<td>" . $student->getNationalId() . "</td>";
                    $content .= "<td>" . $student->getDataField('address') . "</td>";
                    $content .= "<td>" . $student->getDataField('temporary_registration_address') . "</td>";
                    $content .= "<td>" . $student->getDataField('temporary_address') . "</td>";
                    $content .= "<td>" . $student->getDataField('mobile_phone') . "</td>";
                    $content .= "</tr>";
                    $groupNumber++;
                    $totalNumber++;
                }
            }
//            $group->setDepartment($group->getStudyProgram()->getDepartment());
//            $repository->save($group);
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'CustomController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/custom/lldrepeat", name="custom_lldrepeat")
     */
    public function lldRepeat(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $repository->findAll();

        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $students = $studentRepository->findAll();

        $content = "<table id='mainTable' class='table table-striped table-compact table-sm'>";
        $content .= "<thead><tr>";
        $content .= "<th>Umumy t/b</th>";
        $content .= "<th>T/b</th>";
        $content .= "<th>Ýyl</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Field</th>";
        $content .= "<th>Gender</th>";
        $content .= "<th>Region</th>";
        $content .= "</tr></thead>";
        $content .= "<tbody>";
        $totalNumber = 1;
        foreach ($groups as $group) {
            if ($group->getGraduationYear() == 2025 && $group->getStatus() == 1) {
//                echo $group->getLetterCode()."<br>";
                $students = $group->getStudents();
                $groupNumber = 1;
                foreach ($students as $student) {
                    //echo $taughtCourse->getFullName()."- "<br>";
                    $year = substr(strval($student->getSystemId()), 0, 2);
                    if ($year == '19') {
                        $content .= "<tr>";
                        $content .= "<td>" . $totalNumber . "</td>";
                        $content .= "<td>" . $groupNumber . "</td>";
                        $content .= "<td>" . $student->getThreenames() . "</td>";
                        $content .= "<td>" . $group->getStudyYear() . "</td>";
                        //$content .= "<td>" . $group->getStudyProgram()->getNameTurkmen() . "</td>";
                        $content .= "<td>" . $group->getLetterCode() . "</td>";
                        $content .= "<td>" . $group->getSystemId() . "</td>";
                        //$content .= "<td>" . $student->getDataField('address') . "</td>";
                        $content .= "<td>" . $student->getSystemId() . "</td>";
                        $content .= "<td>" . $student->getGender() . "</td>";
                        $content .= "<td>" . mb_strtoupper($student->getRegion()->getLetterCode()) . "</td>";
                        $content .= "</tr>";
                        $groupNumber++;
                        $totalNumber++;
                    }
                }
            }
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'CustomController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/custom/phpinfo", name="custom_phpinfo")
     */
    public function generatesiwsi(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $content = '';

        phpinfo();
    }

    /**
     * @Route("/custom/pagecover", name="custom_pagecover")
     */
    public function pageCover(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $content = '';
        $pdfDocument = new PDFDocument();
        //$result = $pdfDocument->getPreviewImage('c:/campus/www/campus3/var/uploads/ebooks/Arbitrazh_ish_yoeredish.pdf', 1);
        $result = $pdfDocument->create_preview("c:/campus/www/campus3/var/uploads/ebooks/Arbitrazh_ish_yoeredish.pdf", "1", "jpeg", "4", "72");
        $content = '';
        $content = $result['error'] . ": " . $result['message'];
        return new Response($content);
    }

//    private function getGroupNamesFromCodes(?string $groupIdString) {
//        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
//        $groupNames = [];
//        $groupIds = explode(",", $groupIdString);
//        foreach ($groupIds as $groupId) {
//            $group = $groupRepository->findOneBy(['systemId' => $groupId]);
//            if (!empty($group)) {
//                $groupNames[] = $group->getLetterCode();
//            }
//        }
//        return implode(", ", $groupNames);
//    }
//
//    private function getGroupNamesFromCodePairs(?string $groupCoursePairString) {
//        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
//        $groupNames = [];
//        $groupCoursePairs = explode(",", $groupCoursePairString);
//        $totalStudents = 0;
//        foreach ($groupCoursePairs as $groupCoursePair) {
//            $groupCoursePairData = explode("-", $groupCoursePair);
//            $group = $groupRepository->findOneBy(['systemId' => $groupCoursePairData[0]]);
//            if (!empty($group)) {
//                $groupNames[] = $group->getLetterCode();
//                $totalStudents += $group->getTotalStudentCount();
//            }
//        }
//
//        return ['names' => implode(", ", $groupNames), 'count' => $totalStudents];
//    }

    /**
     * @Route("/custom/generatesiwsi", name="custom_generatesiwsi")
     */
//    public function generatesiwsi(Request $request) {
//        $this->denyAccessUnlessGranted("ROLE_ADMIN");
//        $content = '';
//
//        $classTypeRepository = $this->getDoctrine()->getRepository(ClassType::class);
//        $siwsiClassType = $classTypeRepository->find(6);
//
//        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
//        $scheduleItems = $scheduleItemRepository->findAll();
//        $i = 1;
//        $hourlyIds = [28, 46, 149, 14, 72, 29, 38, 42, 49, 96, 150, 31, 32,
//            10, 74, 16, 152, 154, 153, 155, 33, 34, 18, 8, 24, 44, 62, 47,
//            73, 116, 158, 4, 145, 61, 30, 13];
//        foreach ($scheduleItems as $scheduleItem) {
//            if ($scheduleItem->getClassType()->getSystemId() == 1 &&
//                    !in_array($scheduleItem->getTeacher()->getId(), $hourlyIds)) {
//                $groupYear = substr($scheduleItem->getStudentGroups(), 0, 2);
//                if ($groupYear != "18" && $groupYear != "19") {
//                    $content .= $i . ") ";
//                    $content .= $scheduleItem->getClassType()->getNameEnglish() . " -- ";
//                    $content .= $scheduleItem->getDay() . ":" . $scheduleItem->getSession() . " -- ";
//                    $content .= $scheduleItem->getDay() . ":" . $scheduleItem->getSession() . " -- ";
//                    $content .= $scheduleItem->getTaughtCourse()->getTeacher()->getId() . " -- ";
//                    $content .= $scheduleItem->getTaughtCourse()->getTeacher()->getFullname() . " -- ";
//                    $content .= $scheduleItem->getTaughtCourse()->getNameEnglish() . " -- ";
//                    $content .= $scheduleItem->getStudentGroups();
//                    //$content .= "<br>";
//
//                    $siwsiItem = new ScheduleItem();
//                    $siwsiItem->setTaughtCourse($scheduleItem->getTaughtCourse())
//                            ->setDay($scheduleItem->getDay())
//                            ->setSession(7)
//                            ->setSchedule($scheduleItem->getSchedule())
//                            ->setRooms($scheduleItem->getRooms())
//                            ->setStudentGroups($scheduleItem->getStudentGroups())
//                            ->setData($scheduleItem->getData())
//                            ->setClassType($siwsiClassType)
//                            ->setTeacher($scheduleItem->getTeacher())
//                            ->setStartDate($scheduleItem->getStartDate())
//                            ->setEndDate($scheduleItem->getEndDate())
//                    ;
//                    if ($i > 3) {
//                        //$scheduleItemRepository->save($siwsiItem);
//                        $content .= " CREATED";
//                    }
//                    $i++;
//                    $content .= "<br>";
//                }
//            }
//        }
//        return $this->render('custom/index.html.twig', [
//                    'controller_name' => 'AttendanceController',
//                    'content' => $content,
//        ]);
//    }
}
