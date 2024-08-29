<?php

namespace App\Controller\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\EnrolledStudent;
use App\Entity\StudentAbsence;
use App\Entity\Faculty;
use App\Entity\Department;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Enum\TurkmenMonthsEnum;
use App\Service\CourseDaysManager;
use App\Repository\SettingRepository;

class AbsenceReportController extends AbstractController {

    private $courseDaysManager;
    private $systemEventManager;
    private $systemInfoManager;
    private $settingsRepository;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager, CourseDaysManager $courseDaysManager, SettingRepository $settingRepository) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->courseDaysManager = $courseDaysManager;
        $this->settingsRepository = $settingRepository;
    }

    /**
     * @Route("/faculty/attendance/faculty/{id}", name="faculty_attendance_faculty")
     */
    public function facultyAbsence(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = null;
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
//$id = 1;
        $id = $request->attributes->get('id');
        if (!empty($id)) {
//$id = 1;
            $faculty = $facultyRepository->find($id);
        }
        $year = $this->settingsRepository->findOneBy(['name' => 'current_year'])->getValue();

        $absences = [];
        if ($faculty) {
            if ($faculty->getId() == 4) {
                $departments = $faculty->getDepartments();
                foreach ($departments as $department) {
                    $groups = $department->getGroups();
                    foreach ($groups as $group) {
                        $groupAbsences = $group->getStudentAbsences();
                        foreach ($groupAbsences as $absence) {
                            $absences[] = $absence;
                        }
                    }
                }
            } else {
                $departments = $faculty->getDepartments();
                foreach ($departments as $department) {
                    $studyPrograms = $department->getStudyPrograms();
                    foreach ($studyPrograms as $studyProgram) {
                        $groups = $studyProgram->getGroups();
                        foreach ($groups as $group) {
                            $groupAbsences = $group->getStudentAbsences();
                            foreach ($groupAbsences as $absence) {
                                $absences[] = $absence;
                            }
                        }
                    }
                }
            }
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_FACULTY, $faculty->getId(), 'Faculty absence report');
        }
        return $this->render('attendance/report.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'viewingTeacher' => $viewingTeacher,
                    'faculty' => $faculty,
                    'department' => null,
                    'advisor' => null,
                    'absences' => $absences,
                    'year' => $year,
                    'report_title' => 'Faculty Attendance Report'
        ]);
    }

    /**
     * @Route("/faculty/attendance/department/{id}", name="faculty_attendance_department")
     */
    public function departmentAbsence(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEPARTMENTHEAD");
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = null;
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
        $year = $this->settingsRepository->findOneBy(['name' => 'current_year'])->getValue();
//$id = 1;
        $id = $request->attributes->get('id');
        if (!empty($id)) {
//$id = 1;
            $department = $departmentRepository->find($id);
        }
        $absences = [];
        if ($department) {
            $absences = $department->getStudentAbsences();
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_DEPARTMENT, $department->getId(), 'Department absence report');
        }
        return $this->render('attendance/report.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'viewingTeacher' => $viewingTeacher,
                    'faculty' => $department->getFaculty(),
                    'department' => $department,
                    'advisor' => null,
                    'absences' => $absences,
                    'year' => $year,
                    'report_title' => 'Department Attendance Report'
        ]);
    }

    /**
     * @Route("/faculty/attendance/group/{id}", name="faculty_attendance_group")
     */
    public function groupAbsence(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = null;
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $year = $this->settingsRepository->findOneBy(['name' => 'current_year'])->getValue();
//$id = 1;
        $id = $request->attributes->get('id');
        if (!empty($id)) {
//$id = 1;
            $group = $groupRepository->find($id);
        }
        $absences = [];
        if ($group) {
            $absences = $group->getStudentAbsences();
            $department = $group->getDepartment();
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_GROUP, $group->getId(), 'Group absence report');
        }
        return $this->render('attendance/report.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'viewingTeacher' => $viewingTeacher,
                    'faculty' => $department->getFaculty(),
                    'department' => $department,
                    'advisor' => $group->getAdvisor(),
                    'absences' => $absences,
                    'year' => $year,
                    'report_title' => 'Group Attendance Report'
        ]);
    }

    /**
     * @Route("/faculty/attendance/teacher/{id}", name="faculty_attendance_teacher")
     */
    public function teacherAbsence(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = null;
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        $year = $this->settingsRepository->findOneBy(['name' => 'current_year'])->getValue();
        $id = $request->attributes->get('id');
        if (!empty($id)) {
//$id = 1;
            $teacher = $teacherRepository->find($id);
        }

        $authorizedTeachers = [];

        if ($teacher) {
            $authorizedTeachers[] = $teacher;
            $authorizedTeachers[] = $teacher->getDepartment()->getFaculty()->getDean();
            $authorizedTeachers[] = $teacher->getFaculty()->getFirstDeputyDean();
            $authorizedTeachers[] = $teacher->getFaculty()->getSecondDeputyDean();
            $authorizedTeachers[] = $teacher->getFaculty()->getThirdDeputyDean();
            $authorizedTeachers[] = $teacher->getDepartment()->getDepartmentHead();

            if (!in_array($teacher, $authorizedTeachers)) {
                $this->denyAccessUnlessGranted();
            }
        }

        $absences = [];
        if ($teacher) {
            $absences = $teacher->getStudentAbsences();
            $department = $teacher->getDepartment();
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHER, $teacher->getId(), 'Teacher absence report');
        }
        return $this->render('attendance/report.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'viewingTeacher' => $viewingTeacher,
                    'faculty' => $department->getFaculty(),
                    'department' => $department,
                    'advisor' => null,
                    'absences' => $absences,
                    'year' => $year,
                    'report_title' => 'Teacher Attendance Report'
        ]);
    }

    /**
     * @Route("/faculty/attendance/student/{id?}", name="faculty_attendance_student")
     */
    public function studentAbsence(Request $request) {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_DEAN', 'ROLE_DEPARTMENTHEAD', 'ROLE_STUDENT']);
        $viewingTeacher = null;
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
//        $managedFaculty = $teacherRepository->getManagedFaculty(['teacher_id' => $teacher->getId()]);
//        $managedDepartment = $teacherRepository->getManagedDepartment(['teacher_id' => $teacher->getId()]);
        $year = $this->settingsRepository->findOneBy(['name' => 'current_year'])->getValue();
        $teacher = null;
        $access = false;
        $absences = [];
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
//$id = 1;
        $id = $request->attributes->get('id');
        if (empty($id)) {
            $student = $studentRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($student) {
                $id = $student->getId();
                $access = true;
            }
        }
        if (!empty($id)) {
            $authorizedTeachers = [];

//$id = 1;
            $student = $studentRepository->find($id);
            if ($student) {
                $authorizedTeachers[] = $student->getFaculty()->getDean();
                $authorizedTeachers[] = $student->getFaculty()->getFirstDeputyDean();
                $authorizedTeachers[] = $student->getFaculty()->getSecondDeputyDean();
                $authorizedTeachers[] = $student->getFaculty()->getThirdDeputyDean();
                $authorizedTeachers[] = $student->getDepartment()->getDepartmentHead();
                $authorizedTeachers[] = $student->getStudentGroup()->getAdvisor();

                if ($this->getUser()->getSystemId() == $student->getSystemId()) {
                    $access = true;
                } else {
                    $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                    $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                    if (in_array($teacher, $authorizedTeachers)) {
                        $access = true;
                    }
                    if ($this->isGranted("ROLE_ADMIN")) {
                        $access = true;
                    }
                }
            }
        }
        if ($access) {
            $absences = $student->getStudentAbsences();
        } else {
            $absencesAll = $student->getStudentAbsences();
            foreach ($absencesAll as $absence) {
                if ($absence->getAuthor() == $teacher) {
                    $absences[] = $absence;
                }
            }
        }
//        if($this->)
//        if($student->getFaculty()==$)
//$absences = $student->getStudentAbsences();
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Student absence report');
        return $this->render('attendance/report.html.twig', [
                    'viewingTeacher' => $viewingTeacher,
                    'controller_name' => 'AttendanceController',
                    'faculty' => $student->getFaculty(),
                    'department' => $student->getDepartment(),
                    'advisor' => $student->getStudentGroup()->getAdvisor(),
                    'absences' => $absences,
                    'year' => $year,
                    'report_title' => 'Student Attendance Report'
        ]);
    }

    /**
     * @Route("/faculty/attendance/totalabsences/{unit}/{id}", name="faculty_attendance_totalabsences")
     * @Route("/faculty/attendance/totalabsences/{unit}/{id}/{beginDate?}/{endDate?}", name="faculty_attendance_totalabsences_time")
     */
    public function totalAbsencesFaculty(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");
        $unit = $request->attributes->get('unit');
        switch ($unit) {
            case "faculty":
                $field = 'faculty_id';
                break;
            case "department":
                $field = 'department_id';
                break;
            case "group":
                $field = 'student_group_id';
                break;
            default:
                $field = 'faculty_id';
        }
        $beginDate = $request->attributes->get('beginDate');
        $endDate = $request->attributes->get('endDate');
        if (!$beginDate) {
            $beginDate = $this->systemInfoManager->getSemesterBeginDate($this->systemInfoManager->getCurrentSemester())->format("Y-m-d");
        }
        if (!$endDate) {
            $endDate = $this->systemInfoManager->getSemesterEndDate($this->systemInfoManager->getCurrentSemester())->format("Y-m-d");
        }

        $beginDateObj = new \DateTime($beginDate);
        $endDateObj = new \DateTime($endDate);
        $year = $this->settingsRepository->findOneBy(['name' => 'current_year'])->getValue();

        $id = $request->attributes->get('id');
        $studentAbsenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
//$id = 1;
        $absences = $studentAbsenceRepository->getTotalAbsences([
            'field' => $field,
            'value' => $id,
            'beginDate' => $beginDate,
            'endDate' => $endDate
        ]);

        return $this->render('attendance/topabsences.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'unit' => $unit,
                    'id' => $id,
                    'faculty' => null,
                    'department' => null,
                    'beginDate' => $beginDateObj,
                    'endDate' => $endDateObj,
                    'absences' => $absences,
                    'year' => $year,
                    'report_title' => 'Total Absences Report'
        ]);
    }

    /**
     * @Route("/faculty/attendance/topabsences", name="faculty_attendance_topabsences")
     */
    public function absenceAnalytics(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $studentAbsenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
//$id = 1;
        $absences = $studentAbsenceRepository->getTopAbsences();

        return $this->render('attendance/topabsences.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'faculty' => null,
                    'department' => null,
                    'absences' => $absences,
                    'report_title' => 'University Attendance Report'
        ]);
    }

    /**
     * @Route("/faculty/attendance/facultyreport/{id}/{month?}", name="faculty_attendance_facultyreport")
     */
    public function facultyReport(Request $request) {
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculty = $facultyRepository->find($request->attributes->get('id'));
        $commencementYear = $this->settingsRepository->findOneBy(['name' => 'first_semester_year'])->getValue();
        $content = '';
        $month = $request->attributes->get('month');
        if (!$month) {
            $today = new \DateTime();
            $month = $today->format('n');
        }
        if ($faculty) {
            $isLLD = ($faculty->getSystemId() == 6 ? true : false);
            $content .= '<tbody>';
            $departments = $faculty->getDepartments();
            $studyProgramsArray = [];
            $studyProgramsData = [];
            $totalProgramsData = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0];
            $programId = 0;
            if ($isLLD) {
                foreach ($departments as $department) {
                    $departmentGroups = $department->getGroups();
                    foreach ($departmentGroups as $departmentGroup) {
                        //echo $studyProgram->getNameTurkmen().":".$studyProgram->getLetterCode()."<br>";
                        if (!in_array($departmentGroup->getStudyProgram()->getNameTurkmen(), $studyProgramsArray)) {
                            $studyProgramsArray[] = $departmentGroup->getStudyProgram()->getNameTurkmen();
                            $studyProgramsData += array($departmentGroup->getStudyProgram()->getNameTurkmen() => [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0]);
                            $programId++;
                        }
                    }
                }
            } else {
                foreach ($departments as $department) {
                    $studyPrograms = $department->getStudyPrograms();
                    foreach ($studyPrograms as $studyProgram) {
                        if ($studyProgram->getStatus() == 1) {
                            //echo $studyProgram->getNameTurkmen().":".$studyProgram->getLetterCode()."<br>";
                            if (!in_array($studyProgram->getNameTurkmen(), $studyProgramsArray)) {
                                $studyProgramsArray[] = $studyProgram->getNameTurkmen();
                                $studyProgramsData += array($studyProgram->getNameTurkmen() => [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0]);
                                $programId++;
                            }
                        }
                    }
                }
            }

            $date = new \DateTime();

            $year = $this->systemInfoManager->getFirstSemesterYear();
            if ($month < 9) {
                $year = $this->systemInfoManager->getSecondSemesterYear();
            }

            $date->setDate($year, $month, 1);
            $beginDate = $date->format("Y-m-d");
            $date2 = clone $date;
            $date2->modify('first day of next month');
            $endDate = $date2->format("Y-m-d");
//date('Y-m-t', strtotime($beginDate));

            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            if ($isLLD) {
                $groups = $groupRepository->findGroupsBetweenYears($commencementYear+5, $commencementYear+5);
            } else {
                $groups = $groupRepository->findGroupsBetweenYears($commencementYear+1, $commencementYear+4);
            }
//echo "Month:" . $month . "<br>";
            foreach ($groups as $group) {
                //echo $group->getLetterCode() . "<br>";
                if (in_array($group->getStudyProgram()->getNameTurkmen(), $studyProgramsArray)) {
                    if ($isLLD) {
                        $offset = 1;
                    } elseif ($group->getStudyProgram()->getProgramLevel()->getSystemId() == 7) {
                        //echo $group->getLetterCode() . ":" . $group->getAbsencesCount($month) . "<br>";
                        $offset = 5;
                    } else {
                        $offset = 1;
                    }

                    $totalStudentCount = $group->getTotalStudentCount();
                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][0] += $totalStudentCount;
                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][$group->getStudyYear() + $offset] += $totalStudentCount;

                    $totalAbsencesCount = $group->getAbsencesCount($month);
                    $unexcusedAbsencesCount = $group->getUnexcusedAbsencesCount($month);
                    $otherAbsencesCount = $group->getOtherAbsencesCount($month);
                    $healthAbsencesCount = $totalAbsencesCount - $unexcusedAbsencesCount - $otherAbsencesCount;
                    $courseDays = $this->courseDaysManager->getGroupCourseDays($group, null, 'groupfacultyreport', $beginDate, $endDate);
                    $coursePairs = 0;
                    foreach ($courseDays as $courseDay) {
                        if (!$courseDay['changed']) {
                            $coursePairs++;
                        }
                    }

                    $totalAttendableClasses = $coursePairs * $totalStudentCount;
                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][7] += $totalAttendableClasses;
//                        $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][7] += $attendanceRate;

                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][9] += $totalAbsencesCount;
                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][9 + $group->getStudyYear() + $offset] += $totalAbsencesCount;
                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][16] += $unexcusedAbsencesCount;
                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][17] += $healthAbsencesCount;
                    $studyProgramsData[$group->getStudyProgram()->getNameTurkmen()][18] += $otherAbsencesCount;

                    $totalProgramsData[0] += $totalStudentCount;
                    $totalProgramsData[$group->getStudyYear() + $offset] += $totalStudentCount;
                    $totalProgramsData[7] += $totalAttendableClasses;
                    $totalProgramsData[9] += $totalAbsencesCount;
                    $totalProgramsData[9 + $group->getStudyYear() + $offset] += $totalAbsencesCount;
                    $totalProgramsData[16] += $unexcusedAbsencesCount;
                    $totalProgramsData[17] += $healthAbsencesCount;
                    $totalProgramsData[18] += $otherAbsencesCount;
                }
            }

            $i = 1;
            foreach ($studyProgramsArray as $studyProgramName) {
                $studyProgramsData[$studyProgramName][8] = round(100 - ($studyProgramsData[$studyProgramName][9] / ($studyProgramsData[$studyProgramName][7] + 0.0000001)), 2);
                $content .= "<tr class='excellablerow'>";
                $content .= "<td>" . $i . "</td>";
                $content .= "<td>" . $studyProgramName . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][0] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][1] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][2] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][3] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][4] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][5] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][6] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][7] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][8] . "%</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][9] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][10] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][11] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][12] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][13] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][14] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][15] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][16] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][17] . "</td>";
                $content .= "<td>" . $studyProgramsData[$studyProgramName][18] . "</td>";
                $content .= "</tr>";
                $i++;
            }
            $totalProgramsData[8] = round(100 - ($totalProgramsData[9] / ($totalProgramsData[7] + 0.0000001)), 2);
            $content .= "<tr id='footerrow' class='boldtext'>";
            $content .= "<td></td>";
            $content .= "<td>Fakultet boýunça jemi</td>";
            $content .= "<td>" . $totalProgramsData[0] . "</td>";
            $content .= "<td>" . $totalProgramsData[1] . "</td>";
            $content .= "<td>" . $totalProgramsData[2] . "</td>";
            $content .= "<td>" . $totalProgramsData[3] . "</td>";
            $content .= "<td>" . $totalProgramsData[4] . "</td>";
            $content .= "<td>" . $totalProgramsData[5] . "</td>";
            $content .= "<td>" . $totalProgramsData[6] . "</td>";
            $content .= "<td>" . $totalProgramsData[7] . "</td>";
            $content .= "<td>" . $totalProgramsData[8] . "%</td>";
            $content .= "<td>" . $totalProgramsData[9] . "</td>";
            $content .= "<td>" . $totalProgramsData[10] . "</td>";
            $content .= "<td>" . $totalProgramsData[11] . "</td>";
            $content .= "<td>" . $totalProgramsData[12] . "</td>";
            $content .= "<td>" . $totalProgramsData[13] . "</td>";
            $content .= "<td>" . $totalProgramsData[14] . "</td>";
            $content .= "<td>" . $totalProgramsData[15] . "</td>";
            $content .= "<td>" . $totalProgramsData[16] . "</td>";
            $content .= "<td>" . $totalProgramsData[17] . "</td>";
            $content .= "<td>" . $totalProgramsData[18] . "</td>";
            $content .= "</tr>";
            $content .= '</tbody>';
        }
        return $this->render('attendance/facultyreport.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'faculty' => $faculty,
                    'year' => $year,
                    'month' => TurkmenMonthsEnum::getTypeName($month),
                    'content' => $content
        ]);
    }

    /**
     * @Route("/faculty/attendance/facultydailyreport/{id}/{date?}", name="faculty_attendance_facultydailyreport")
     */
    public function facultyDailyReport(Request $request) {
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculty = $facultyRepository->find($request->attributes->get('id'));
        $content = '';
        $date = $request->attributes->get('date');
        if (!$date) {
            $checkDate = new \DateTime();
        } else {
            $checkDate = new \DateTime($date);
        }
        $sessionsData = [
            1 => "8.30:09:10 /\n 12.50-13:30",
            2 => "9:20-10:00 /\n 13:40-14:20",
            3 => "10:10-10:50 /\n 14:30-15:10",
            4 => "11:10-11:50 /\n 15:20-16:00"
        ];

        $absentStudents = [];
        $absentStudentsData = [];
        if ($faculty) {
            $content .= '<tbody>';
            $absences = $faculty->getStudentAbsences();
            $i = 1;
            foreach ($absences as $absence) {
                if ($absence->getDate() == $checkDate) {
                    //echo $absence->getDate()->format("Y-m-d")."<br>";
                    if (!in_array($absence->getStudent(), $absentStudents)) {
                        $sessions = [1 => '', 2 => '', 3 => '', 4 => ''];
                        foreach ($absences as $absence2) {
                            if ($absence2->getDate() == $checkDate) {
                                if ($absence->getStudent() == $absence2->getStudent()) {
                                    switch ($absence2->getSession()) {
                                        case 1:
                                            $sessions[1] = $sessionsData[1];
                                            break;
                                        case 2:
                                            $sessions[2] = $sessionsData[2];
                                            break;
                                        case 3:
                                            $sessions[3] = $sessionsData[3];
                                            break;
                                        case 4:
                                            $sessions[4] = $sessionsData[4];
                                            break;
                                    }
                                }
                            }
                        }
                        $absentStudents[] = $absence->getStudent();
                        $absentStudentsData[] = [
                            'student' => $absence->getStudent(),
                            'sessions' => $sessions,
                        ];
                    }
                }
            }

            foreach ($absentStudentsData as $absentStudentData) {
                $student = $absentStudentData['student'];
                $sessions = $absentStudentData['sessions'];
                $content .= "<tr class='excellablerow'>";
                $content .= "<td>" . $i . "</td>";
                $content .= "<td>" . $student->getSystemId() . "</td>";
                $content .= "<td>" . $student->getFullname() . "</td>";
                if ($student->getStudentGroup()->getStudyYear() == 0) {
                    $content .= "<td>" . $student->getMajorInTurkmen() . ", DÖB</td>";
                } else {
                    $content .= "<td>" . $student->getMajorInTurkmen() . ", " . $student->getStudentGroup()->getStudyYear() . "</td>";
                }
                foreach ($sessions as $session) {
                    $content .= "<td>" . $session . "</td>";
                }
                $content .= "<td>" . $checkDate->format('d.m.Y') . "</td>";
                $content .= "<td></td>";
                $content .= "</tr>";
                $i++;
            }
            $content .= "<tr id='excellablerow' class='boldtext'>";
            $content .= "</tr>";

            $content .= "<tr id='footerrow' class='boldtext'>";
            $content .= "</tr>";
            $content .= '</tbody>';
        }


        return $this->render('attendance/facultydailyreport.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'faculty' => $faculty,
                    'checkDate' => $checkDate,
                    'content' => $content
        ]);
    }

}
