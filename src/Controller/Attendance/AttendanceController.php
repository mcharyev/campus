<?php

namespace App\Controller\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\StudentAbsenceManager;
use App\Service\TeacherAttendanceManager;
use App\Entity\TaughtCourse;
use App\Entity\Teacher;
use App\Entity\EnrolledStudent;
use App\Entity\ClassType;
use App\Entity\StudentAbsence;
use App\Entity\TeacherAttendance;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Enum\AbsenceStatusEnum;
use App\Enum\AbsenceExcuseStatusEnum;
use App\Enum\ClassTypeEnum;
use App\Service\CourseDaysManager;

class AttendanceController extends AbstractController {

    private $systemEventManager;
    private $courseDaysManager;

    public function __construct(SystemEventManager $systemEventManager, CourseDaysManager $courseDaysManager) {
        $this->systemEventManager = $systemEventManager;
        $this->courseDaysManager = $courseDaysManager;
    }

    /**
     * @Route("/faculty/attendance/process", name="faculty_attendance_process")
     */
    public function process(Request $request, Connection $connection, StudentAbsenceManager $manager,
            TeacherAttendanceManager $teacherAttendanceManager) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");

        $action = $request->request->get('action');
        $absence_id = $request->request->get('absence_id');
        $result = "Action: ";
        $absenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
        try {
            if ($action == "present") {
                if (intval($absence_id) > 0) {
                    $studentAbsence = $absenceRepository->find($absence_id);
                    if ($studentAbsence) {
                        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_DELETE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $studentAbsence->getId(), 'Removed absence');
                        $result .= ' Absence removed';
                        $absenceRepository->remove($studentAbsence);
                    } else {
                        $result .= ' There is no absence';
                    }
                } else {
                    $result .= ' There is no absence';
                }
            } else {
//$result = ' ' . $action;
                $courseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
                $course = $courseRepository->find(intval($request->request->get('course_id')));
                $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                $teacher = $teacherRepository->find(intval($request->request->get('teacher_id')));
                $typeRepository = $this->getDoctrine()->getRepository(ClassType::class);
                $type = $typeRepository->find(intval($request->request->get('absence_type')));
                $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);

//                $result .= ' id:' . $request->request->get('absence_id');
//                $result .= ' student:' . $request->request->get('student_id');
//                $result .= ' course:' . $request->request->get('course_id');
//                $result .= ' teacher:' . $request->request->get('teacher_id');
//                $result .= ' type:' . $request->request->get('absence_type');

                if (($action == 'absent' || $action == 'note') && intval($absence_id) == 0) {
                    $student = $studentRepository->find(intval($request->request->get('student_id')));
                    $absence = $absenceRepository->findStudentAbsence([
                        'course_id' => $course->getId(),
                        'session' => $request->request->get('absence_session'),
                        'student_id' => $student->getId(),
                        'date' => $request->request->get('absence_date')
                    ]);
                    if (!$absence) {
                        $studentAbsence = $manager->createFromRequest($request, $student, $course, $teacher, $type);
                        if ($type->getSystemId() != ClassTypeEnum::SIWSI) {
                            $absenceRepository->save($studentAbsence);
                            $result .= ' Absence recorded';
                            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $studentAbsence->getId(), '' . $student->getFullname());
                        } else {
                            $result .= ' Cannot record absence for SIWSI type';
                        }
                    } else {
                        //$studentAbsence = $manager->updateFromRequest($request, $absence, $student, $course, $teacher, $type);
                        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Already recorded');
                        $result .= ' Absence ALREADY recorded';
                    }
                } elseif ($action == 'excuse' || $action == 'unexcuse' 
                        || $action == 'recoverdean' || $action == 'recoverteacher') {
                    $student = $studentRepository->find(intval($request->request->get('student_id')));
                    $absence = $absenceRepository->findStudentAbsence([
                        'course_id' => $course->getId(),
                        'session' => $request->request->get('absence_session'),
                        'student_id' => $student->getId(),
                        'date' => $request->request->get('absence_date')
                    ]);

                    if ($absence) {
                        if ($action == "excuse") {
                            $absence->setExcuseNote($request->request->get('absence_excusenote'));
                            $absence->setDateUpdated(new \DateTime());
                            $absence->setExcuseStatus(AbsenceExcuseStatusEnum::EXCUSED);
                            $absenceRepository->save($absence);
                            $result .= ' Excuse recorded';
                            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Excuse recorded');
                        } elseif ($action == "unexcuse") {
                            $absence->setExcuseNote('');
                            $absence->setDateUpdated(new \DateTime());
                            $absence->setExcuseStatus(AbsenceExcuseStatusEnum::UNEXCUSED);
                            $absenceRepository->save($absence);
                            $result .= ' Un-excuse recorded';
                            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Un-excuse recorded');
                        } elseif ($action == "recoverteacher") {
                            if ($absence->getStatus() == AbsenceStatusEnum::DEAN_APPROVED) {
                                $result .= ' Recovery already approved by dean';
                                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Recovery already approved by dean');
                            } else {
                                $absence->setAuthorApprovalDate(new \DateTime());
                                $absenceRepository->save($absence);
                                $absence->setStatus(AbsenceStatusEnum::TEACHER_APPROVED);
                                $result .= ' Teacher approval of recovery recorded';
                                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Teacher recovery recorded');
                            }
                        } elseif ($action == "recoverdean") {
//                            if ($student->isDean($teacher)) {
                            if ($absence->getStatus() == AbsenceStatusEnum::DEAN_APPROVED) {
                                $result .= ' Recovery already approved by dean';
                                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Recovery already approved by dean');
//                            } elseif ($absence->getStatus() == AbsenceStatusEnum::NULL) {
//                                $result .= ' Teacher has not approved the recovery yet';
//                                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Teacher has not approved');
                            } else {
                                $absence->setRecoverNote($request->request->get('absence_recovernote'));
                                $absence->setDeanApprovalDate(new \DateTime());
                                $absence->setStatus(AbsenceStatusEnum::DEAN_APPROVED);
                                $absenceRepository->save($absence);
                                $result .= ' Dean approval of recovery recorded';
                                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $absence->getId(), 'Dean recovery recorded');
                            }
//                            } else {
//                                $result .= ' You are not a dean or vice-dean for this student';
//                            }
                        }
                    } else {
                        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, 0, 'Absence does not exist');
                        $result .= ' Absence does not exist';
                    }
                } elseif ($action == 'teacher') {
                    $teacherAttendanceRepository = $this->getDoctrine()->getRepository(TeacherAttendance::class);
                    $teacherAttendance = $teacherAttendanceRepository->findScheduleByDay([
                        'teacher_id' => $teacher->getId(),
                        'session' => $request->request->get('absence_session'),
                        'date' => $request->request->get('absence_date')
                    ]);
                    if (!$teacherAttendance) {
                        if ($type) {
                            $teacherAttendance = $teacherAttendanceManager->createFromRequest($request, $course, $teacher, $type);
                            $teacherAttendanceRepository->save($teacherAttendance);
                            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHERATTENDANCE, $teacherAttendance->getId(), '');
                            $result .= ' Teacher attendance recorded';
                        } else {
                            $result .= ' Unknown teacher attendance type. You can submit teacher attendance only on the day of the class.';
                        }
                    } else {
                        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHERATTENDANCE, $teacherAttendance->getId(), 'Already recorded');
                        $result .= ' Teacher attendance ALREADY recorded.';
                    }
                } else {
                    $student = $studentRepository->find(intval($request->request->get('student_id')));
                    $studentAbsence = $absenceRepository->find($absence_id);
                    $studentAbsence = $manager->updateFromRequest($request, $studentAbsence, $student, $course, $teacher, $type);
                    $absenceRepository->update($studentAbsence);
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_STUDENTABSENCE, $studentAbsence->getId(), '');
                    $result .= ' Absence updated';
                }
            }
        } catch (\Exception $e) {
            $result = $e->getMessage();
            //$result .= " " . $e->getFile() . " " . $e->getLine();
        }

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/html');
        return $response;
    }

}
