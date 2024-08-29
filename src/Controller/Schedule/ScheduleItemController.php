<?php

namespace App\Controller\Schedule;

use App\Entity\ScheduleItem;
use App\Entity\Schedule;
use App\Entity\Teacher;
use App\Entity\TaughtCourse;
use App\Entity\Classroom;
use App\Entity\TeacherWorkSet;
use App\Entity\TeacherWorkItem;
use App\Entity\Group;
use App\Entity\ClassType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ScheduleItemFormType;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class ScheduleItemController extends AbstractController {

    //private $manager;
    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/scheduleitem", name="faculty_scheduleitem")
     * @Route("/faculty/scheduleitem/search/{searchField?}/{searchValue?}", name="faculty_scheduleitem_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('schedule_item/index.html.twig', [
                    'controller_name' => 'ScheduleItemController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);

//        // Creates a simple grid based on your entity (ORM)
//		$source = new Entity('App:ScheduleItem');
//
//		// Get a Grid instance
//		$grid = $this->get('grid');
//
//		// Attach the source to the grid
//		$grid->setSource($source);
//
//		// Return the response of the grid to the template
//		return $grid->getGridResponse('App::gridd.html.twig');
    }

    /**
     * @Route("/faculty/scheduleitem/list", name="faculty_scheduleitem_list")
     * @Route("/faculty/scheduleitem/list/{offset?0}/{pageSize?20}/{sorting?schedule_item.id}/{searchField?}/{searchValue?}", name="faculty_scheduleitem_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'schedule_item',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'exactMatch' => false,
            ];
            if ($request->attributes->get('searchField') == 'teacher_id') {
                $params['exactMatch'] = true;
            } elseif ($request->attributes->get('searchField') == 'taught_course_id') {
                $params['exactMatch'] = true;
            }
            $repository = $this->getDoctrine()->getRepository(ScheduleItem::class);
            $recordCount = $repository->getRecordCount($params);
            $results = $repository->getRecords($params);
            $result_array = [
                'Result' => "OK",
                'TotalRecordCount' => $recordCount,
                'Records' => $results
            ];
        } catch (\Exception $e) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $e->getMessage()
            ];
        }

        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/faculty/scheduleitem/delete", name="faculty_scheduleitem_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(ScheduleItem::class);
            $scheduleItem = $repository->find($id);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_DELETE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_SCHEDULEITEM, $scheduleItem->getId(), 'Teacher: ' . $scheduleItem->getTeacher()->getFullname());
            $repository->remove($scheduleItem);

            //Return result
            $result_array = [
                'Result' => "OK"
            ];
        } catch (\Exception $e) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $e->getMessage()
            ];
        }
        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/faculty/scheduleitem/new", name="faculty_scheduleitem_new")
     * @Route("/faculty/scheduleitem/add/{teacherId?}/{taughtCourseId?}/{day?1}/{session?1}/{scheduleId?}", name="faculty_scheduleitem_add")
     * @Route("/faculty/scheduleitem/edit/{id?0}", name="faculty_scheduleitem_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $id = $request->attributes->get('id');
        $teacherId = $request->attributes->get('teacherId');
        $taughtCourseId = $request->attributes->get('taughtCourseId');
        $day = $request->attributes->get('day');
        $session = $request->attributes->get('session');
        $scheduleId = $request->attributes->get('scheduleId');
        if (!$day) {
            $day = 1;
        }
        if (!$session) {
            $session = 1;
        }
        if (!empty($id)) {
            $scheduleItem = $repository->find($id);
        } else {
            $scheduleItem = new ScheduleItem();
        }
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        if ($teacherId) {
            $teacher = $teacherRepository->find($teacherId);
        } else {
            $teacher = null;
        }
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        if ($taughtCourseId) {
            $taughtCourse = $taughtCourseRepository->find($taughtCourseId);
        } else {
            $taughtCourse = null;
        }

        $scheduleRepository = $this->getDoctrine()->getRepository(Schedule::class);
        if ($scheduleId) {
            $schedule = $scheduleRepository->find($scheduleId);
        } else {
            $schedule = null;
        }

        $startDate = null;
        $endDate = null;
        $currentSemester = 1;
        if ($teacher) {
            if ($teacher->getDepartment()) {
                $department = $teacher->getDepartment();
                if ($department->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                    $startDate = $this->systemInfoManager->getTrimesterBeginDate($this->systemInfoManager->getCurrentTrimester());
                    $currentSemester = $this->systemInfoManager->getCurrentTrimester();
                } else {
                    $startDate = $this->systemInfoManager->getSemesterBeginDate($this->systemInfoManager->getCurrentSemester());
                    $currentSemester = $this->systemInfoManager->getCurrentSemester();
                }

                if ($department->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                    $endDate = $this->systemInfoManager->getTrimesterEndDate($this->systemInfoManager->getCurrentTrimester());
                } else {
                    $endDate = $this->systemInfoManager->getSemesterEndDate($this->systemInfoManager->getCurrentSemester());
                }
            }
        }

        $form = $this->createForm(ScheduleItemFormType::class, $scheduleItem, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER')),
            'year' => $this->systemInfoManager->getCurrentCommencementYear(),
            'semester' => $currentSemester,
            'teacher' => $teacher,
            'taughtCourse' => $taughtCourse,
            'day' => $day,
            'session' => $session,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'schedule' => $schedule,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $scheduleItem = $form->getData();
            $fields = [
                'teacher', 'course_name', 'seminar_combined'
            ];
            foreach ($fields as $field) {
                $scheduleItem->setDataField($field, $form->get("note_" . $field)->getData());
            }
            $scheduleItem->setDataField("groups", $scheduleItem->getStudentGroups());
            $scheduleItem->setDataField("course_name", $scheduleItem->getTaughtCourse()->getNoteCourseName());
            //$courseName = $form->get('course_name')->getData();
            //$scheduleItem->setNoteCourseName($courseName);
            $repository->save($scheduleItem);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_SCHEDULEITEM, $scheduleItem->getId(), 'Teacher: ' . $scheduleItem->getTeacher()->getFullname());
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                if ($scheduleItem->getTaughtCourse()) {
                    return new RedirectResponse($sourcePath . "#section" . $scheduleItem->getTaughtCourse()->getId());
                } else {
                    return new RedirectResponse($sourcePath);
                }
            }

            return $this->redirectToRoute('faculty_scheduleitem');
        }
        return $this->render('schedule_item/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/scheduleitem/scheduleOverlap/{scheduleId?0}", name="faculty_scheduleitem_scheduleoverlap")
     */
    public function checkOverlappingScheduleItems(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $id = $request->attributes->get('id');
        $scheduleId = $request->attributes->get('scheduleId');
        $content = '';

        $repository = $this->getDoctrine()->getRepository(Teacher::class);
        $teachers = $repository->findAll();
        foreach ($teachers as $teacher) {
            $week = [
                [0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 0],
            ];
            $scheduleItems = $teacher->getScheduleItems();
            if (sizeof($scheduleItems) > 0) {
                $content .= "<h5>" . $teacher->getFullname() . "</h5>";
                for ($day = 1; $day < 7; $day++) {
                    for ($session = 1; $session < 5; $session++) {
                        foreach ($scheduleItems as $courseScheduleItem) {
                            if ($courseScheduleItem->getSchedule()->getId() == $scheduleId) {
                                if ($courseScheduleItem->getDay() == $day && $courseScheduleItem->getSession() == $session) {
                                    $week[$day - 1][$session - 1] += 1;
                                    $count = $week[$day - 1][$session - 1];
                                    if ($count > 1) {
                                        $content .= $courseScheduleItem->getDay() . ":" . $courseScheduleItem->getSession();
                                        $content .= " = " . $week[$day - 1][$session - 1];
                                        $content .= " <a href='javascript:deleteItem(" . $courseScheduleItem->getId() . ");'>Delete</a>";
                                        $content .= "&nbsp;&nbsp;&nbsp;<span id='info" . $courseScheduleItem->getId() . "'></span><br>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->render('schedule_item/custom.html.twig', [
                    'controller_name' => 'ScheduleItemController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/faculty/scheduleitem/updateitem/{itemId}/{field}/{value}", name="faculty_scheduleitem_updateitem")
     */
    public function updateItem(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $id = $request->attributes->get('itemId');
        $field = $request->attributes->get('field');
        $value = $request->attributes->get('value');

        $scheduleItem = $repository->find($id);
        switch ($field) {
            case 'period':
                $scheduleItem->setPeriod($value);
                break;
        }
        $repository->save($scheduleItem);

        return new Response("<span style='color:green'>OK " . $field . "->" . $value . "</span>");
    }

    /**
     * @Route("/faculty/scheduleitem/specialAction/{action}/{itemId}", name="faculty_scheduleitem_specialaction")
     */
    public function specialAction(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $schedule = $this->getDoctrine()->getRepository(Schedule::class)->find(6);
        $id = $request->attributes->get('itemId');
        $action = $request->attributes->get('action');

        if ($action == "1") {
            $scheduleItem = $repository->find($id);
            $scheduleItemNew = clone $scheduleItem;
            $scheduleItemNew->setSession($scheduleItem->getSession() + 5);
            $repository->save($scheduleItemNew);
        } elseif ($action == "2") {
            $scheduleItem = $repository->find($id);
            $scheduleItemNew = clone $scheduleItem;
            $scheduleItemNew->setStartDate(new \DateTime("2023-09-01"));
            $scheduleItemNew->setEndDate(new \DateTime("2024-01-05"));
            $repository->save($scheduleItemNew);
        } elseif ($action == "3") {
            $scheduleItem = $repository->find($id);
            $scheduleItem->setStartDate(new \DateTime("2023-09-01"));
            $scheduleItem->setEndDate(new \DateTime("2024-01-05"));
            $repository->save($scheduleItem);
        } elseif ($action == "4") {
            $scheduleItem = $repository->find($id);
            $scheduleItem->setStartDate(new \DateTime("2023-09-01"));
            $scheduleItem->setEndDate(new \DateTime("2024-01-05"));
            $repository->save($scheduleItem);
        } elseif ($action == "5") {
            $scheduleItem = $repository->find($id);
            $scheduleItem->setStartDate(new \DateTime("2023-09-01"));
            $scheduleItem->setEndDate(new \DateTime("2024-01-05"));
            $repository->save($scheduleItem);
        }

        return new Response("<span style='color:green'>OK " . $id . "->" . $action . "</span>");
    }

    /**
     * @Route("/faculty/scheduleitem/taughtCourseAction", name="faculty_scheduleitem_taughtcourseaction")
     */
    public function taughtCourseAction(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $action = $request->request->get('action');
        $taughtCourseId = $request->request->get('course_id');

        if ($action == "1") {
            $startDate = $request->request->get('start_date');
            $endDate = $request->request->get('end_date');

            $date1 = new \DateTime($startDate);
            $date2 = new \DateTime($endDate);
            $taughtCourse = $taughtCourseRepository->find($taughtCourseId);
            $scheduleItems = $taughtCourse->getScheduleItems();
            foreach ($scheduleItems as $scheduleItemUpdated) {
                $scheduleItemUpdated->setStartDate($date1);
                $scheduleItemUpdated->setEndDate($date2);
                $repository->save($scheduleItemUpdated);
            }
        }

        if ($action == "2") {
            $newTaughtCourseId = $request->request->get('new_course_id');
            $taughtCourse = $taughtCourseRepository->find($taughtCourseId);
            $newTaughtCourse = $taughtCourseRepository->find($newTaughtCourseId);
            $scheduleItems = $taughtCourse->getScheduleItems();
            foreach ($scheduleItems as $scheduleItem) {
                $scheduleItemNew = clone $scheduleItem;
                $scheduleItemNew->setTaughtCourse($newTaughtCourse);
                $repository->save($scheduleItemNew);
            }
        }

        if ($action == "3") {
//            $startDate = $request->request->get('start_date');
//            $endDate = $request->request->get('end_date');
//            $date1 = new \DateTime($startDate);
//            $date2 = new \DateTime($endDate);
            $taughtCourse = $taughtCourseRepository->find($taughtCourseId);
            $scheduleCodeInput = $request->request->get('schedule_codes');
            $scheduleCodeLines = explode(";", $scheduleCodeInput);
            if (sizeof($scheduleCodeLines) > 2) {
                $scheduleId = $scheduleCodeLines[0];
                $scheduleType = $scheduleCodeLines[1];
                $groups = $scheduleCodeLines[2];
                $dateInterval = $scheduleCodeLines[3];
                $scheduleCodes = $scheduleCodeLines[4];
                $schedule = $this->getDoctrine()->getRepository(Schedule::class)->find(intval($scheduleId));
                $classType = $this->getDoctrine()->getRepository(\App\Entity\ClassType::class)->find(intval($scheduleType));
                $scheduleCodeItems = explode(",", $scheduleCodes);
                foreach ($scheduleCodeItems as $scheduleCodeItem) {
                    if (strlen($scheduleCodeItem) == 2) {
                        $day = intval(substr($scheduleCodeItem, 0, 1));
                        $session = intval(substr($scheduleCodeItem, 1, 1));
                        $scheduleItem = new ScheduleItem();
                        $scheduleItem->setSchedule($schedule);
                        $scheduleItem->setDay($day);
                        $scheduleItem->setSession($session);
                        $scheduleItem->setStudentGroups($groups);
                        $scheduleItem->setClassType($classType);
                        $scheduleItem->setTeacher($taughtCourse->getTeacher());
                        $scheduleItem->setTaughtCourse($taughtCourse);
                        $scheduleItem->setPeriod(1);
                        if ($dateInterval == "0") {
                            $scheduleItem->setStartDate($schedule->getStartDate());
                            $scheduleItem->setEndDate($schedule->getEndDate());
                        } elseif ($dateInterval == "1") {
                            $scheduleItem->setStartDate(new \DateTime("2023-09-01"));
                            $scheduleItem->setEndDate(new \DateTime("2024-01-05"));
                        } elseif ($dateInterval == "2") {
                            $scheduleItem->setStartDate(new \DateTime("2023-09-01"));
                            $scheduleItem->setEndDate(new \DateTime("2024-01-05"));
                        }

                        $data = [
                            'room' => '',
                            'course' => $taughtCourse->getNameEnglish(),
                            'groups' => '',
                            'teacher' => $taughtCourse->getTeacher()->getShortFullname(),
                            'teacher_id' => $taughtCourse->getTeacher()->getId(),
                            'seminar_combined' => 0
                        ];

                        $scheduleItem->setData($data);

                        $repository->save($scheduleItem);
                    }
                }
            }
        }


        return new Response("<span style='color:green'>OK " . $taughtCourseId . "->" . $action . "</span>");
    }

    /**
     * @Route("/faculty/scheduleitem/addexpress", name="faculty_scheduleitem_addexpress")
     */
    public function addExpress(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
//        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
//        $departmentId = $request->request->get('departmentId');
//        $teacherId = $request->request->get('teacherId');
        $teacherWorkSetId = $request->request->get('teacherWorkSetId');
        $year = intval($request->request->get('year'));
        $yearpart = intval($request->request->get('yearpart'));
        $addCourseIfNotFound = boolval($request->request->get('addtaughtcourse'));
        $commit = $request->request->get('commit');

        $data = $request->request->get('data');
        $rows = explode("\n", $data);
        $i = 1;
        $content = "Processing ..<br>";
        $teacherWorkSetRepository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $content .= "Teacher workset id: " . $teacherWorkSetId . "<br>";
        $teacherWorkSet = $teacherWorkSetRepository->find($teacherWorkSetId);
        $content .= "Department: " . $teacherWorkSet->getDepartment()->getNameEnglish() . "<br>";
        $content .= "Teacher: " . $teacherWorkSet->getTeacher()->getFullname() . "<br>";
        $classroomRepository = $this->getDoctrine()->getRepository(Classroom::class);
        $scheduleItemRepository = $this->getDoctrine()->getRepository(ScheduleItem::class);
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $scheduleRepository = $this->getDoctrine()->getRepository(Schedule::class);

        foreach ($rows as $row) {
            $errors = [];
            $fields = explode("\t", $row);
            if (sizeof($fields) == 10) {
                $content .= $i . ") " . $row . "<br>";
                $content .= "<strong>Length:</strong> " . sizeof($fields) . ", ";
                $content .= "<strong>Semester(Trimester):</strong> '" . $fields[0] . "'</span>, ";
                $content .= "<strong>Schedule ID:</strong> '" . $fields[1] . "'</span>, ";
                $content .= "<strong>Date Interval:</strong> '" . $fields[2] . "'- '" . $fields[3] . "'</span>, ";
                $content .= "<strong>Room:</strong> '" . $fields[4] . "' ";
                $content .= "<strong>Course:</strong> '" . $fields[5] . "' ";
                $content .= "<strong>Teacher:</strong> '" . $fields[6] . "' ";
                $content .= "<strong>Day:</strong> '" . $fields[7] . "' ";
                $content .= "<strong>Session:</strong> '" . $fields[8] . "' ";
                $content .= "<strong>Groups:</strong> '" . $fields[9] . "'<br>";

                $semester = intval($fields[0]);
                if ($semester == $yearpart) {
                    $content .= " SEMESTER CHECK: <span class='green'>YES</span>.";
                } else {
                    $content .= " SEMESTER CHECK: <span class='red'>NO</span>.";
                    $errors[] = 'Semester value INVALID';
                }
                $content .= "<br>";

                $schedule = $fields[1];
                $scheduleFound = $scheduleRepository->find(intval($schedule));
                if ($scheduleFound) {
                    $content .= " SCHEDULE FOUND: <span class='green'>YES</span>." . $scheduleFound->getNameEnglish();
                } else {
                    $content .= " SCHEDULE FOUND: <span class='red'>NO</span>.";
                    $errors[] = 'Schedule cannot be found for ID: ' . $schedule;
                }
                $content .= "<br>";

                $teacher = $fields[6];
                $teacherFound = $teacherRepository->findOneBy(['scheduleName' => $teacher]);
                if ($teacherFound) {
                    $content .= " TEACHER FOUND: <span class='green'>YES</span>. " . $teacherFound->getShortFullname();
                    if ($teacherFound->getId() == $teacherWorkSet->getTeacher()->getId()) {
                        $content .= " ID EQUAL: <span class='green'>YES</span>.";
                    } else {
                        $content .= " ID EQUAL: <span class='red'>NO</span>.";
                        $errors[] = 'Teacher ID not equal to the found teacher.';
                    }
                } else {
                    $content .= " TEACHER FOUND: <span class='red'>NO</span>.";
                    $errors[] = 'Teacher cannot be found for: ' . $teacher;
                }
                $content .= "<br>";

                $groups = $fields[9];
                $groupIds = $this->getGroupIdsFromScheduleNames($groups);
                if (strpos($groupIds, "NOT FOUND") === false) {
                    $content .= " GROUPS FOUND: <span class='green'>YES</span>." . $groupIds;
                } else {
                    $content .= " GROUPS FOUND: <span class='red'>NO</span>. " . $groupIds;
                    $errors[] = 'Group cannot be found for: ' . $groups;
                }
                $content .= "<br>";

                $course = $fields[5];
                $courseFound = $this->getCourseFromScheduleName($teacherFound, $yearpart, $course, $groupIds);
                if ($courseFound) {
                    $content .= " COURSE FOUND: <span class='green'>YES</span>. " . $courseFound->getFullName();
                } else {
                    $content .= " COURSE FOUND: <span class='red'>NO</span>.";
                    if ($addCourseIfNotFound) {
                        $params = [
                            'coursetitle' => $course,
                            'teacher' => $teacherFound,
                            'year' => $year,
                            'semester' => $yearpart,
                            'department' => $teacherWorkSet->getDepartment(),
                            'startdate' => new \DateTime($fields[2]),
                            'enddate' => new \DateTime($fields[3]),
                            'groupIds' => $groupIds,
                        ];

                        $content .= $this->addTaughtCourse($params);
                    } else {
                        $errors[] = 'Course cannot be found for: ' . $course;
                    }
                }
                $content .= "<br>";

                $classTypeFound = $this->getClassType($course);
                if ($classTypeFound) {
                    $content .= " CLASS TYPE FOUND: <span class='green'>YES</span>." . $classTypeFound->getNameEnglish();
                } else {
                    $content .= " CLASS TYPE FOUND: <span class='red'>NO</span>.";
                    $errors[] = 'Class type cannot be found for: ' . $course;
                }
                $content .= "<br>";

                $room = $fields[4];
                $roomFound = $classroomRepository->findOneBy(["scheduleName" => $room]);
                if ($roomFound) {
                    $content .= " ROOM FOUND: <span class='green'>YES</span>." . $roomFound->getLetterCode();
                } else {
                    $content .= " ROOM FOUND: <span class='red'>NO</span>.";
                    $errors[] = 'Room cannot be found for: ' . $room;
                }
                $content .= "<br>";

                $day = intval($fields[7]);
                $session = intval($fields[8]);
                if (($day > 0 && $day < 7) && ($session > 0 && $session < 20)) {
                    $content .= " DAY AND SESSION CHECK: <span class='green'>YES</span>.";
                } else {
                    $content .= " DAY AND SESSION CHECK: <span class='red'>NO</span>.";
                    $errors[] = 'Day and session values are INVALID';
                }
                $content .= "<br>";

                if ($commit == 1) {
                    try {
                        $scheduleItem = new ScheduleItem();
                        $scheduleItem->setSchedule($scheduleFound);
                        $scheduleItem->setDay($day);
                        $scheduleItem->setSession($session);
                        $scheduleItem->setStudentGroups($groupIds);
                        $scheduleItem->setClassType($classTypeFound);
                        $scheduleItem->setTeacher($courseFound->getTeacher());
                        $scheduleItem->setTaughtCourse($courseFound);
                        $scheduleItem->setPeriod(1);
                        $scheduleItem->setStartDate(new \DateTime($fields[2]));
                        $scheduleItem->setEndDate(new \DateTime($fields[3]));

                        $data = [
                            'room' => $roomFound->getLetterCode(),
                            'course' => $courseFound->getNameEnglish(),
                            'groups' => $groupIds,
                            'teacher' => $courseFound->getTeacher()->getShortFullname(),
                            'teacher_id' => $courseFound->getTeacher()->getId(),
                            'seminar_combined' => 0
                        ];

                        $scheduleItem->setData($data);

                        $scheduleItemRepository->save($scheduleItem);

                        $content .= "<h6>DATA COMMIT: <span class='green'>YES</span></h6><br>";
                    } catch (Exception $e) {
                        $content .= "ERROR: <span class='red'>" . $e->getMessage() . "</span><br>";
                    }
                }
            }
            $content .= '<br>';
            $i++;
        }
        if (sizeof($errors) > 0) {
            $content .= "ERRORS:<br>" . implode("<br>", $errors);
        }

        return new Response($content);
    }

    private function isAddableCourse($courseName) {
        $endings = ['(L)', 'Purposes', 'Language Elective', 'Physical Education'];
        foreach ($endings as $ending) {
            if ($this->endsWith($courseName, $ending))
                return true;
        }
        return false;
    }

    private function addTaughtCourse($params) {
        $content = '';
        try {
            if ($params['teacher'] == null) {
                return "<h6>INVALID DATA: <span class='red'>Teacher value is invalid</span></h6>";
            } elseif ($params['department'] == null) {
                return "<h6>INVALID DATA: <span class='red'>Department value is invalid</span></h6>";
            } elseif (!$this->isAddableCourse($params['coursetitle'])) {
                return "<h6>COURSE CREATED: <span class='green'>NO</span> Does not end with (L) or (P) or Purposes or Education" . $params['coursetitle'] . "</h6>";
            }
            $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
            $taughtCourse = new TaughtCourse();
            //$taughtCourse->setProgramCourse(null);
            $taughtCourse->setTeacher($params['teacher']);
            $taughtCourse->setYear($params['year']);
            $taughtCourse->setSemester($params['semester']);
            $taughtCourse->setDepartment($params['department']);
            $taughtCourse->setStartDate($params['startdate']);
            $taughtCourse->setEndDate($params['enddate']);
            $taughtCourse->setStudentGroups($params['groupIds']);
            $taughtCourse->setGradingType(1);
            $taughtCourse->setNameEnglish($this->getPureCourseTitle($params['coursetitle']));
            $data = [
                'note' => '',
                'lecture_topics' => '',
                'practice_topics' => '',
                'groups' => $params['groupIds'],
                'teacher' => $params['teacher']->getShortFullname(),
                'coursecode' => '',
                'course_name' => $this->getPureCourseTitle($params['coursetitle']),
                'seminar_combined' => 0
            ];

            $taughtCourse->setData($data);
            $taughtCourseRepository->save($taughtCourse);
            $content .= " COURSE CREATED: <span class='green'>YES</span> " . $params['coursetitle'] . "<br>";
        } catch (Exception $e) {
            $content .= "ERROR: <span class='red'>" . $e->getMessage() . "</span><br>";
        }
        return $content;
    }

    private function getGroupIdsFromScheduleNames(string $groupScheduleNames) {
        $result = [];
        if (strlen($groupScheduleNames) == 0) {
            return "NOT FOUND";
        }

        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupsArray = explode(";", $groupScheduleNames);
        foreach ($groupsArray as $groupScheduleName) {
            $group = $groupRepository->findOneBy(['scheduleName' => trim($groupScheduleName)]);
            if ($group) {
                $result[] = $group->getSystemId();
            } else {
                $result[] = 'NOT FOUND';
            }
        }
        return implode(",", $result);
    }

    private function getCourseFromScheduleName($teacher, $yearpart, $courseName, $groupIds) {
        if ($teacher && $yearpart > 0 && strlen($courseName) > 0) {
            $taughtCourses = $teacher->getTaughtCourses();
            $courseTitle = $this->getPureCourseTitle($courseName);
            foreach ($taughtCourses as $taughtCourse) {
                $matching = false;
                if ($taughtCourse->getSemester() == $yearpart && $taughtCourse->getDataField("course_name") == $courseTitle) {
                    $groupsArray = explode(",", $groupIds);
                    $studentGroups = $taughtCourse->getStudentGroups();
                    foreach ($groupsArray as $groupId) {
                        $matching = (strpos($studentGroups, $groupId) !== false);
                        //echo "checking " . $groupId . " IN " . $studentGroups . " " . intval($matching) . "<br>";
                    }
                    if ($matching) {
                        return $taughtCourse;
                    }
                }
            }
        }
        return null;
    }

    private function getPureCourseTitle($courseName) {
        if ($this->endsWith($courseName, "(L)") || $this->endsWith($courseName, "(S)") || $this->endsWith($courseName, "(P)")) {
            return substr($courseName, 0, strlen($courseName) - 4);
        } elseif ($this->endsWith($courseName, "(LAB)")) {
            return substr($courseName, 0, strlen($courseName) - 6);
        } else {
            return $courseName;
        }
    }

    private function getClassType($courseName) {
        $classTypeId = 7;
        if ($this->endsWith($courseName, "(L)")) {
            $classTypeId = 1;
        } elseif ($this->endsWith($courseName, "(S)")) {
            $classTypeId = 2;
        } elseif ($this->endsWith($courseName, "(P)")) {
            $classTypeId = 3;
        } elseif ($this->endsWith($courseName, "(LAB)")) {
            $classTypeId = 4;
        }
        return $this->getDoctrine()->getRepository(ClassType::class)->find($classTypeId);
    }

    private function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }

    private function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }

}
