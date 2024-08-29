<?php

namespace App\Controller\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\StudentAbsenceManager;
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
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Entity\Setting;

class ScheduleDisplayController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/scheduledisplay", name="faculty_scheduledisplay")
     * @Route("/faculty/scheduledisplay/teacher/{id}/{date?}", name="faculty_scheduledisplay_teacher")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $days = $this->systemInfoManager->getWeekdays();
        //$classtimes_lld = ['1) 8:30 - 9:15', '2) 9:25 - 10:10', '3) 10:20 - 11:05', '4) 12:00 - 12:45', '5) 12:55 - 13:40', '6) 13:55 - 14:40', '7) ', '8) '];
//        $trimester_begin_dates = ['01-09-2018', '01-09-2018', '01-09-2018'];
//        $trimester_end_dates = ['24-11-2018', '24-11-2018', '24-11-2018'];
        //$id = 99;
        $id = $request->attributes->get('id');
        $date = $request->attributes->get('date');
        $today = new \DateTime();
        if ($date) {
            $today = new \DateTime($date);
        }

        $today = $this->getStartOfWeekDate($today);
        //echo $today->format("Y-m-d");
//        $entity = $request->attributes->get('entity');
//        switch($entity)
//        {
//            case 'teacher':
//                $this->buildTeacher($id);
//        }
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $roomRepository = $this->getDoctrine()->getRepository(Classroom::class);
        $teacher = $teacherRepository->find($id);
        if ($teacher->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
            $sessions = $this->getSessions("lld_sessions");
        } else {
            $sessions = $this->getSessions("bachelor_sessions");
        }

        $taughtCourses = $teacher->getTaughtCourses();
        $allScheduleItems = array();
        $itemsAdded = array();
        foreach ($taughtCourses as $taughtCourse) {
            $scheduleItems = $taughtCourse->getScheduleItems();
            foreach ($scheduleItems as $scheduleItem) {
                if ($scheduleItem->getStartDate() <= $today && $scheduleItem->getEndDate() >= $today) {
                    if (!in_array($scheduleItem->getDay() . "_" . $scheduleItem->getSession(), $itemsAdded)) {
                        $groups = array();
                        $groupSystemIds = explode(",", $scheduleItem->getStudentGroups());
                        foreach ($groupSystemIds as $groupSystemId) {
                            $group = $groupRepository->findOneBy(['systemId' => $groupSystemId]);
                            if ($group != null) {
                                $groups[] = $group;
                            }
                        }

                        $rooms = array();
                        $roomIds = explode(",", $scheduleItem->getRooms());
                        foreach ($roomIds as $roomId) {
                            //$rooms[] = ['id'=>'0', 'nameEnglish'=>'hop'];
                            $room = $roomRepository->find(intval($roomId));
                            if ($room != null) {
                                $rooms[] = $room;
                            }
                        }

                        $teachers = array();
                        $teachers[] = $scheduleItem->getTeacher();

                        $allScheduleItems[] = [
                            'item' => $scheduleItem,
                            'groups' => $groups,
                            'rooms' => $rooms,
                            'teachers' => $teachers
                        ];
                        $itemsAdded[] = $scheduleItem->getDay() . "_" . $scheduleItem->getSession();
                    }
                }
            }
        }
        $debug = '';
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHER, $teacher->getId(), 'Teacher schedule');
        return $this->render('scheduledisplay/index.html.twig', [
                    'controller_name' => 'ScheduleDisplayController',
                    'object' => [
                        'name' => $teacher->getFullname(),
                        'type' => 'teacher',
                        'longname' => ''
                    ],
                    'scheduleItems' => $allScheduleItems,
                    'days' => $days,
                    'sessions' => $sessions,
                    'debug' => $debug
        ]);
    }

    /**
     * @Route("/faculty/scheduledisplay/group/{groupSystemId}/{date?}", name="faculty_scheduledisplay_group")
     */
    public function group(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $days = $this->systemInfoManager->getWeekdays();
        $groupSystemId = $request->attributes->get('groupSystemId');
        $date = $request->attributes->get('date');
        $today = new \DateTime();
        if ($date) {
            $today = new \DateTime($date);
        }
        $today = $this->getStartOfWeekDate($today);

        $roomRepository = $this->getDoctrine()->getRepository(Classroom::class);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $group = $groupRepository->findOneBy(['systemId' => $groupSystemId]);
        if ($group->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
            $sessions = $this->getSessions("lld_sessions");
        } else {
            $sessions = $this->getSessions("bachelor_sessions");
        }
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $allScheduleItems = array();
        $itemsAdded = array();
        $debug = '';
        //$scheduleItems = $scheduleItemRepository->findBy(['studentGroups' => $group->getSystemId()]);
        $scheduleItems = $scheduleItemRepository->findScheduleItems([
            'class' => 'App\Entity\ScheduleItem',
            'table' => 'schedule_item',
            'field' => 'student_groups',
            'value' => $group->getSystemId(),
            'exactMatch' => false,
        ]);
        foreach ($scheduleItems as $scheduleItem) {
//            $debug .= $scheduleItem->getCourseTeacherName() . ", " . $scheduleItem->getTaughtCourse()->getNoteCourseName() .
//                    ", " . $scheduleItem->getData()['course'] . ", " . $scheduleItem->getData()['teacher'] . ", " .
//                    ", " . $scheduleItem->getData()['groups'] .
//                    "<br>";
            if ($scheduleItem->getStartDate() <= $today && $scheduleItem->getEndDate() >= $today) {
                if (!in_array($scheduleItem->getDay() . "_" . $scheduleItem->getSession(), $itemsAdded)) {
                    $groups = array();
                    $groupSystemIds = explode(",", $scheduleItem->getStudentGroups());
                    foreach ($groupSystemIds as $groupSystemId) {
                        $groupIn = $groupRepository->findOneBy(['systemId' => $groupSystemId]);
                        if ($groupIn != null) {
                            $groups[] = $groupIn;
                        }
                    }

                    $rooms = array();
                    $roomIds = explode(",", $scheduleItem->getRooms());
                    foreach ($roomIds as $roomId) {
                        $room = $roomRepository->find($roomId);
                        if ($room != null) {
                            $rooms[] = $room;
                        }
                    }

                    $teachers = array();
                    $teachers[] = $scheduleItem->getTeacher();

                    $allScheduleItems[] = [
                        'item' => $scheduleItem,
                        'groups' => $groups,
                        'rooms' => $rooms,
                        'teachers' => $teachers
                    ];
                    $itemsAdded[] = $scheduleItem->getDay() . "_" . $scheduleItem->getSession();
                }
            }
        }

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_GROUP, $group->getId(), 'Group schedule');
        return $this->render('scheduledisplay/index.html.twig', [
                    'controller_name' => 'ScheduleDisplayController',
                    'object' => [
                        'name' => $group->getLetterCode(),
                        'type' => 'group',
                        'longname' => $group->getStudyProgram()->getNameEnglish()
                    ],
                    'scheduleItems' => $allScheduleItems,
                    'days' => $days,
                    'sessions' => $sessions,
                    'debug' => $debug
        ]);
    }

    /**
     * @Route("/faculty/scheduledisplay/room/{id}/{date?}", name="faculty_scheduledisplay_room")
     */
    public function room(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $days = $this->systemInfoManager->getWeekdays();
        $sessions = $sessions = $this->getSessions("bachelor_sessions");
        $id = $request->attributes->get('id');
        $date = $request->attributes->get('date');
        $today = new \DateTime();
        if ($date) {
            $today = new \DateTime($date);
        }
        $today = $this->getStartOfWeekDate($today);
        
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $roomRepository = $this->getDoctrine()->getRepository(Classroom::class);
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $room = $roomRepository->find($id);
        $allScheduleItems = array();
        $itemsAdded = array();
        $debug = '';
        //$scheduleItems = $scheduleItemRepository->findBy(['studentGroups' => $group->getSystemId()]);
        $scheduleItems = $scheduleItemRepository->findScheduleItems([
            'class' => 'App\Entity\ScheduleItem',
            'table' => 'schedule_item',
            'field' => 'rooms',
            'value' => $room->getId(),
            'exactMatch' => true,
        ]);
        foreach ($scheduleItems as $scheduleItem) {
//            $debug .= $scheduleItem->getCourseTeacherName() . ", " . $scheduleItem->getTaughtCourse()->getNoteCourseName() .
//                    ", " . $scheduleItem->getData()['course'] . ", " . $scheduleItem->getData()['teacher'] . ", " .
//                    ", " . $scheduleItem->getData()['groups'] .
//                    "<br>";
            if ($scheduleItem->getStartDate() <= $today && $scheduleItem->getEndDate() >= $today) {
                if (!in_array($scheduleItem->getDay() . "_" . $scheduleItem->getSession(), $itemsAdded)) {
                    $groups = array();
                    $groupSystemIds = explode(",", $scheduleItem->getStudentGroups());
                    foreach ($groupSystemIds as $groupSystemId) {
                        $group = $groupRepository->findOneBy(['systemId' => $groupSystemId]);
                        if ($group != null) {
                            $groups[] = $group;
                        }
                    }

                    $rooms = array();
                    $roomIds = explode(",", $scheduleItem->getRooms());
                    foreach ($roomIds as $roomId) {
                        $roomItem = $roomRepository->find($roomId);
                        if ($roomItem != null) {
                            $rooms[] = $roomItem;
                        }
                    }

                    $teachers = array();
                    $teachers[] = $scheduleItem->getTeacher();

                    $allScheduleItems[] = [
                        'item' => $scheduleItem,
                        'groups' => $groups,
                        'rooms' => $rooms,
                        'teachers' => $teachers
                    ];
                    $itemsAdded[] = $scheduleItem->getDay() . "_" . $scheduleItem->getSession();
                }
            }
        }

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW,
                EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(),
                EntityTypeEnum::ENTITY_CLASSROOM, $room->getId(), 'Room schedule');
        return $this->render('scheduledisplay/index.html.twig', [
                    'controller_name' => 'ScheduleDisplayController',
                    'object' => [
                        'name' => $room->getLetterCode(),
                        'type' => 'room',
                        'longname' => $room->getNameEnglish()
                    ],
                    'scheduleItems' => $allScheduleItems,
                    'days' => $days,
                    'sessions' => $sessions,
                    'debug' => $debug
        ]);
    }

    private function getSessions($sessions_name) {
        $settingRepository = $this->getDoctrine()->getRepository(Setting::class);
        $sessions = $settingRepository->findOneBy(['name' => $sessions_name])->getValue();
        return explode(",", $sessions);
    }

    private function getStartOfWeekDate($date = null) {
        if ($date instanceof \DateTime) {
            $date = clone $date;
        } else {
            // If date is falsy, it won't harm
            $date = new \DateTime($date);
        }

        return $date->modify('saturday this week');
    }

}
