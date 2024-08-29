<?php

namespace App\Controller\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Teacher;
use App\Entity\Faculty;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Service\CourseDaysManager;
use App\Entity\ScheduleItem;
use App\Entity\SystemEvent;
use App\Entity\User;
use App\Entity\Schedule;

class AttendanceTeacherJournalController extends AbstractController {

    private $systemEventManager;
    private $courseDaysManager;
    private $systemInfoManager;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager, CourseDaysManager $courseDaysManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->courseDaysManager = $courseDaysManager;
    }

    /**
     * @Route("/faculty/teacherjournalreport/{scheduleId}/{date?}", name="faculty_teacherjournalreport")
     */
    public function facultyAbsence(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");

        $scheduleId = $request->attributes->get('scheduleId');
        $date = $request->attributes->get('date');
        if ($date) {
            $today = new \DateTime($date);
        } else {
            $today = new \DateTime();
            //$this->redirect("/faculty/teacherjournalreport/".$scheduleId."/".$today->format('d-m-Y'));
        }

        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = null;
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $id = $request->attributes->get('id');
        $scheduleId = $request->attributes->get('scheduleId');
        $scheduleRepository = $this->getDoctrine()->getRepository(Schedule::class);
        $schedule = $scheduleRepository->find($scheduleId);
        if (!empty($id)) {
            $faculty = $facultyRepository->find($id);
        } else {
            $faculty = $facultyRepository->find(3);
        }

        $todayText = $today->format('d.m.Y');
        $weekday = $today->format('N');

        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $scheduleItemsToday = $scheduleItemRepository->findBy(['day' => $weekday, 'schedule' => $schedule]);

        $systemEventRepository = $this->getDoctrine()->getRepository(SystemEvent::class);
        $userRepository = $this->getDoctrine()->getRepository(User::class);
//        echo 'last login'.$lastLogin->getDateUpdated()->format('d-m-Y');
//        return;
        $journals = [];
        foreach ($scheduleItemsToday as $scheduleItem) {
            $user = $userRepository->findOneBy(['systemId' => $scheduleItem->getTeacher()->getSystemId()]);
            if ($user) {
                $lastLogin = $systemEventRepository->findOneBy(['type' => 5, 'subjectType' => 1, 'subjectId' => $user->getId()]);
            } else {
                $lastLogin = null;
            }

            if ($lastLogin) {
                $lastLoginDate = $lastLogin->getDateUpdated()->format('d.m.Y');
            } else {
                $lastLoginDate = null;
            }
            if ($lastLoginDate === $todayText) {
                $loginStatus = "OK";
            } else {
                $loginStatus = "No login today";
            }

            $journals[] = [
                'scheduleItem' => $scheduleItem,
                'lastLogin' => $lastLoginDate,
                'loginStatus' => $loginStatus,
            ];
        }


        return $this->render('attendance/teacherjournalattendance.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'viewingTeacher' => $viewingTeacher,
                    'faculty' => $faculty,
                    'department' => null,
                    'advisor' => null,
                    'today' => $today,
                    'scheduleItems' => $scheduleItemsToday,
                    'journals' => $journals,
                    'schedule' => $schedule,
                    'report_title' => 'Teacher Journal Usage Report'
        ]);
    }

}
