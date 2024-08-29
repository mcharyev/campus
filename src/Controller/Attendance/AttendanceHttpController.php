<?php

namespace App\Controller\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\ClassType;
use App\Entity\StudentAbsence;
use App\Entity\User;

class AttendanceHttpController extends AbstractController {

    /**
     * @Route("/interop/attendance/get", name="interop_attendance_get")
     */
    public function getAttendance(Request $request) {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $course_id = $request->request->get('course_id');
        $session = intval($request->request->get('session'));
        $group_code = $request->request->get('group_code');
        $date = $request->request->get('date');
        $datetime = new \DateTime($date);
        $weekDay = $datetime->format("N");
        $weekDayFull = $datetime->format("l");
        $error = 0;
        $error_message = '';
        $studentAbsenceData = [];
        $courseName = '';

        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $username]);

        if ($user && $this->checkUser($user, $password)) {
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->findOneBy(['systemId' => $user->getSystemId()]);
            if ($teacher) {
                $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
                $taughtCourse = $taughtCourseRepository->find($course_id);
                if ($taughtCourse) {
                    if ($taughtCourse->getTeacher() == $teacher) {
                        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
                        $scheduleItem = $scheduleItemRepository->findCourseScheduleItems([
                            'course_id' => $course_id,
                            'session' => $session,
                            'day' => $weekDay
                        ]);
                        if ($scheduleItem) {
                            $courseName = $taughtCourse->getDataField('course_name');
                            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
                            $group = $groupRepository->findOneBy(['systemId' => $group_code]);
                            $absenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
                            $students = $group->getStudents();
                            foreach ($students as $student) {
                                $absence = $absenceRepository->findStudentAbsence([
                                    'course_id' => $course_id,
                                    'session' => $session,
                                    'student_id' => $student->getId(),
                                    'date' => $date
                                ]);
                                if ($absence) {
                                    $absenceData = [
                                        'system_id' => $student->getSystemId(),
                                        'attendance' => 'A',
                                        'session' => $absence->getSession(),
                                        'note' => $absence->getNote()
                                    ];
                                } else {
                                    $absenceData = [
                                        'system_id' => $student->getSystemId(),
                                        'attendance' => 'P',
                                        'session' => $session,
                                        'note' => ''
                                    ];
                                }

                                $studentAbsenceData[] = $absenceData;
                            }
                        } else {
                            $error = 5;
                            $error_message = "The course " . $course_id . " does not have a " .
                                    "schedule for Day:" . $weekDayFull . ", Session:" . $session;
                        }
                    } else {
                        $error = 4;
                        $error_message = "The course " . $course_id . " does not belong to " .
                                "teacher " . $username . ". Only authorized teachers can access attendance data.";
                    }
                } else {
                    $error = 3;
                    $error_message = "Course " . $course_id . " not found. Please check the course id row value.";
                }
            } else {
                $error = 2;
                $error_message = "Teacher account for " . $username . " not found. This user is not linked to a teacher account.";
            }
        } else {
            $error = 1;
            $error_message = "User " . $username . " not found. Please check your credentials in the settings file.";
        }

        $result_array = [
            'username' => $username,
            'course_name' => $courseName,
            'course_id' => $course_id,
            'session_id' => $session,
            'date' => $date,
            'students' => $studentAbsenceData,
            'error' => $error,
            'error_message' => $error_message
        ];
        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/interop/attendance/post", name="interop_attendance_post")
     */
    public function postAttendance(Request $request) {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $course_id = $request->request->get('course_id');
        $session = intval($request->request->get('session'));
        $group_code = $request->request->get('group_code');
        $studentAttendance = $request->request->get('student_attendance');
        $studentAttendanceItems = explode("|", $studentAttendance);
        $date = $request->request->get('date');
        $datetime = new \DateTime($date);
        $weekDay = $datetime->format("N");
        $weekDayFull = $datetime->format("l");
        $error = 0;
        $error_message = '';
        $studentAbsenceData = [];
        $courseName = '';

        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $username]);

        if ($user && $this->checkUser($user, $password)) {
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->findOneBy(['systemId' => $user->getSystemId()]);
            if ($teacher) {
                $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
                $taughtCourse = $taughtCourseRepository->find($course_id);
                if ($taughtCourse) {
                    if ($taughtCourse->getTeacher() == $teacher) {
                        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
                        $scheduleItem = $scheduleItemRepository->findCourseScheduleItems([
                            'course_id' => $course_id,
                            'session' => $session,
                            'day' => $weekDay
                        ]);
                        if ($scheduleItem) {
                            $typeRepository = $this->getDoctrine()->getRepository(ClassType::class);
                            $type = $typeRepository->find(intval($request->request->get('class_type_id')));

                            $courseName = $taughtCourse->getDataField('course_name');
                            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
                            $group = $groupRepository->findOneBy(['systemId' => $group_code]);
                            $absenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
                            $students = $group->getStudents();
                            foreach ($students as $student) {
                                $studentAttendance = $this->findStudentFromArray($student->getSystemId(), $studentAttendanceItems);
                                //echo $studentIndex . ":" . $student->getSystemId() . "<br>";
                                if ($studentAttendance) {
                                    $studentArray = explode("#", $studentAttendance);
                                    $absence = $absenceRepository->findStudentAbsence([
                                        'course_id' => $course_id,
                                        'session' => $session,
                                        'student_id' => $student->getId(),
                                        'date' => $date
                                    ]);
                                    if ($absence) {
                                        if ($studentArray[1] == 'A') {
                                            //echo $studentArray[0] . ":" . $studentArray[1] . "<br>";
                                            //already exists, therefore do nothing
                                            $absenceData = [
                                                'system_id' => $student->getSystemId(),
                                                'attendance' => 'A',
                                                'session' => $absence->getSession(),
                                                'note' => $absence->getNote(),
                                                'message' => 'Absence already exists'
                                            ];
                                        } elseif ($studentArray[1] == 'P') {
                                            //remove absence because teacher changed value
                                            $absenceData = [
                                                'system_id' => $student->getSystemId(),
                                                'attendance' => 'P',
                                                'session' => $absence->getSession(),
                                                'note' => $absence->getNote(),
                                                'message' => 'Absence removed'
                                            ];
                                        }
                                    } else {
                                        if ($studentArray[1] == 'A') {
                                            //add new absence
                                            $absenceData = [
                                                'system_id' => $student->getSystemId(),
                                                'attendance' => 'A',
                                                'session' => $session,
                                                'note' => '',
                                                'message' => 'Absence recorded'
                                            ];
                                        } else {
                                            //do nothing, when absence does not exist
                                            //and new value is presence
                                            $absenceData = [
                                                'system_id' => $student->getSystemId(),
                                                'attendance' => 'P',
                                                'session' => $session,
                                                'note' => '',
                                                'message' => 'Presence ignored'
                                            ];
                                        }
                                    }

                                    $studentAbsenceData[] = $absenceData;
                                } else {
                                    //do nothing because student not found
                                    $absenceData = [
                                        'system_id' => $student->getSystemId(),
                                        'attendance' => 'P',
                                        'session' => $session,
                                        'note' => '',
                                        'message' => 'Student not found'
                                    ];
                                    $studentAbsenceData[] = $absenceData;
                                }
                            }
                        } else {
                            $error = 5;
                            $error_message = "The course " . $course_id . " does not have a " .
                                    "schedule for Day:" . $weekDayFull . ", Session:" . $session;
                        }
                    } else {
                        $error = 4;
                        $error_message = "The course " . $course_id . " does not belong to " .
                                "teacher " . $username . ". Only authorized teachers can access attendance data.";
                    }
                } else {
                    $error = 3;
                    $error_message = "Course " . $course_id . " not found. Please check the course id row value.";
                }
            } else {
                $error = 2;
                $error_message = "Teacher account for " . $username . " not found. This user is not linked to a teacher account.";
            }
        } else {
            $error = 1;
            $error_message = "User " . $username . " not found or not authorized. Please check your credentials in the settings file.";
        }

        $result_array = [
            'username' => $username,
            'course_name' => $courseName,
            'course_id' => $course_id,
            'session_id' => $session,
            'date' => $date,
            'students' => $studentAbsenceData,
            'error' => $error,
            'error_message' => $error_message
        ];
        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    private function findStudentFromArray(string $studentId, array $studentAttendanceArray) {
        foreach ($studentAttendanceArray as $studentAttendance) {
            if (strpos($studentAttendance, $studentId) === 0) {
                return $studentAttendance;
            }
        }
        return null;
    }

    private function checkUser(User $user, string $password) {
        if (!$user) {
            return false;
        }
        if ($this->isGranted("ROLE_ADMIN")) {
            return true;
        }
        $adServer = $_SERVER['APP_AD_SERVER'];
        $ldap = ldap_connect($adServer);
        $ldaprdn = $_SERVER['APP_AD_DOMAIN'] . "\\" . $user->getUsername();
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        if (strlen($password) == 0) {
            $bind = 0;
        } else {
            $bind = @ldap_bind($ldap, $ldaprdn, $password);
        }

        if ($bind) {
            return true;
        } else {
            //$this->logger->debug('Domain binding returned FALSE!');
            return false;
        }
    }

}