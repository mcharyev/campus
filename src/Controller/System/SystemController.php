<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\ProgramCourse;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\EnrolledStudent;
use App\Entity\ClassType;
use App\Entity\Classroom;
use App\Entity\StudentAbsence;
use App\Entity\Faculty;
use App\Entity\Department;
use App\Entity\TeacherAttendance;
use App\Entity\User;
use App\Service\SystemEventManager;
use App\Enum\EntityTypeEnum;
use App\Enum\EventTypeEnum;
use App\Entity\AlumnusStudent;
use App\Hr\Entity\Employee;

class SystemController extends AbstractController {

    /**
     * @Route("/system/index", name="system_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('system/index.html.twig', [
                    'controller_name' => 'SystemController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/system/monitor/", name="system_monitor")
     */
    public function monitor(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        return $this->render('system/monitor.html.twig', [
                    'controller_name' => 'SystemController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/system/systemevent/after/{time?}", name="system_systemevent_after")
     */
    public function getsystemEventsAfter(Request $request, SystemEventManager $systemEventManager, RegistryInterface $registry) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        //$result_array = [];
        $time = $request->attributes->get('time');
        if (!$time) {
            $time = new \DateTime();
            $time->sub(new \DateInterval('1 day'));
        }
        $lastEvents = $systemEventManager->getSystemEventsAfter($time);
        $result_array = [];
        foreach ($lastEvents as $event) {
//            $subjectData = '';
            $subjectData = $this->getObjectDataFromRepository($registry, $event->getSubjectType(), $event->getSubjectId());
//            $objectData = '';
            $objectData = $this->getObjectDataFromRepository($registry, $event->getObjectType(), $event->getObjectId());
            $result_array[] = [
                'id' => $event->getId(),
                'type' => $event->getType(),
                'subject_type' => $event->getSubjectType(),
                'subject_id' => $event->getSubjectId(),
                'subject_data' => $subjectData[0],
                'subject_data2' => $subjectData[1],
                'object_type' => $event->getObjectType(),
                'object_id' => $event->getObjectId(),
                'object_data' => $objectData[0],
                'object_data2' => $objectData[1],
                'data' => $event->getData(),
                'date' => $event->getDateUpdated()->format("Y-m-d H:i:s")
            ];
        }

        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    private function getObjectDataFromRepository(RegistryInterface $registry, $entityType, $id) {
        $entityClass = $this->getEntityClass($entityType);
        if (!$entityClass) {
            return [null, null];
        }

        $repository = new ServiceEntityRepository($registry, $entityClass);
        $entity = $repository->find($id);
        if ($entity) {
            switch ($entityType) {
                case EntityTypeEnum::ENTITY_NULL:
                    return [null, null];
                case EntityTypeEnum::ENTITY_USER:
                    return [$entity->getFullName(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_TEACHER:
                    return [$entity->getLastname(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_FACULTY:
                    return [$entity->getNameEnglish(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_DEPARTMENT:
                    return [$entity->getNameEnglish(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_GROUP:
                    return [$entity->getLetterCode(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_ENROLLEDSTUDENT:
                    return [$entity->getFullname(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_EXPELLEDSTUDENT:
                    return [$entity->getFullname(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_ALUMNUSSTUDENT:
                    return [$entity->getFullname(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_STUDYPROGRAM:
                    return [$entity->getNameEnglish(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_PROGRAMCOURSE:
                    return [$entity->getNameEnglish(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_EMPLOYEE:
                    return [$entity->getLastnameFirstname(), $entity->getSystemId()];
                case EntityTypeEnum::ENTITY_TAUGHTCOURSE:
                    return [$entity->getNameEnglish(), null];
                case EntityTypeEnum::ENTITY_SCHEDULEITEM:
                    return [$entity->getId(), null];
                case EntityTypeEnum::ENTITY_SCHEDULE:
                    return [$entity->getNameEnglish(), null];
                case EntityTypeEnum::ENTITY_STUDENTABSENCE:
                    return [$entity->getStudent()->getFullname(), $entity->getStudent()->getSystemId()];
                case EntityTypeEnum::ENTITY_TEACHERATTENDANCE:
                    return [$entity->getTeacher()->getFullname(), $entity->getTeacher()->getSystemId()];
                case EntityTypeEnum::ENTITY_CLASSROOM:
                    return [$entity->getNameEnglish(), null];
                default:
                    return null;
            }
        }
    }

    private function getEntityClass(int $entityType) {
        switch ($entityType) {
            case EntityTypeEnum::ENTITY_NULL:
                return null;
            case EntityTypeEnum::ENTITY_USER:
                return User::class;
            case EntityTypeEnum::ENTITY_TEACHER:
                return Teacher::class;
            case EntityTypeEnum::ENTITY_FACULTY:
                return Faculty::class;
            case EntityTypeEnum::ENTITY_DEPARTMENT:
                return Department::class;
            case EntityTypeEnum::ENTITY_GROUP:
                return Group::class;
            case EntityTypeEnum::ENTITY_ENROLLEDSTUDENT:
                return EnrolledStudent::class;
            case EntityTypeEnum::ENTITY_EXPELLEDSTUDENT:
                return ExpelledStudent::class;
            case EntityTypeEnum::ENTITY_ALUMNUSSTUDENT:
                return AlumnusStudent::class;
            case EntityTypeEnum::ENTITY_STUDYPROGRAM:
                return \App\Entity\StudyProgram::class;
            case EntityTypeEnum::ENTITY_PROGRAMCOURSE:
                return ProgramCourse::class;
            case EntityTypeEnum::ENTITY_EMPLOYEE:
                return Employee::class;
            case EntityTypeEnum::ENTITY_TAUGHTCOURSE:
                return TaughtCourse::class;
            case EntityTypeEnum::ENTITY_SCHEDULEITEM:
                return ScheduleItem::class;
            case EntityTypeEnum::ENTITY_SCHEDULE:
                return Schedule::class;
            case EntityTypeEnum::ENTITY_STUDENTABSENCE:
                return StudentAbsence::class;
            case EntityTypeEnum::ENTITY_TEACHERATTENDANCE:
                return TeacherAttendance::class;
            case EntityTypeEnum::ENTITY_CLASSROOM:
                return Classroom::class;
            default:
                return null;
        }
    }

    /**
     * @Route("/system/getimage/{entity?}/{id?}", name="system_getimage")
     */
    public function getimage(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $entity = $request->attributes->get('entity');
        $id = $request->attributes->get('id');
        $url = '/build/photo.jpg';
        if ($entity == 'TEACHER') {
            $url = '/build/teachers/' . $id . '.jpg';
        } elseif ($entity == 'USER') {
            if (strlen($id) == 6) {
                $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
                $student = $studentRepository->findOneBy(['systemId' => $id]);
                $url = '/build/photos/' . $student->getGroupCode() . '/' . $id . '.jpg';
            } elseif (strlen($id) == 4) {
                $url = '/build/teachers/' . $id . '.jpg';
            }
            elseif (strlen($id) == 3) {
                $url = '/build/employee_photos/' . $id . '.jpg';
            }
        }

        return new RedirectResponse($url);
    }

}
