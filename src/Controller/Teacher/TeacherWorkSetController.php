<?php

namespace App\Controller\Teacher;

use App\Entity\Department;
use App\Entity\Faculty;
use App\Entity\TeacherWorkItem;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Enum\WorkColumnEnum;
use App\Enum\WorkloadEnum;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\ProgramCourse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TeacherWorkSetFormType;
use App\Entity\TeacherWorkSet;
use App\Controller\Workload\WorkColumnsArray;

class TeacherWorkSetController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/teacherworkset", name="faculty_teacherworkset")
     * @Route("/faculty/teacherworkset/search/{searchField?}/{searchValue?}", name="faculty_teacherworkset_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, $searchField . "=" . $searchValue);
        return $this->render('teacher_work_set/index.html.twig', [
                    'controller_name' => 'TeacherWorkSetController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
        ]);
    }

    /**
     * @Route("/faculty/teacherworkset/list", name="faculty_teacherworkset_list")
     * @Route("/faculty/teacherworkset/list/{offset?0}/{pageSize?20}/{sorting?teacher_work_set.id}/{searchField?}/{searchValue?}", name="faculty_teacherworkset_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'teacher_work_set',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'exactMatch' => false,
            ];
            $exactMatchableFields = [
                'teacher_id',
                'id',
            ];
            if (in_array($params['searchField'], $exactMatchableFields)) {
                $params['exactMatch'] = true;
            }
            $repository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
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
     * @Route("/faculty/teacherworkset/delete/{id?}", name="faculty_teacherworkset_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            if (empty($id)) {
                $id = $request->attributes->get('id');
            }
            $repository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
            $campusBuilding = $repository->find($id);
            $repository->remove($campusBuilding);

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
     * @Route("/faculty/teacherworkset/new", name="faculty_teacherworkset_new")
     * @Route("/faculty/teacherworkset/edit/{id?0}/{source_path?}", name="faculty_teacherworkset_edit")
     * @Route("/faculty/teacherworkset/editinteacher/{id?0}/{teacher_id?0}/{year?}/{workload?}/{department?}", name="faculty_teacherworkset_editinteacher")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $id = $request->attributes->get('id');
        $teacherId = $request->attributes->get('teacher_id');
        $year = $request->attributes->get('year');
        $workload = $request->attributes->get('workload');
        $departmentId = $request->attributes->get('department');
        if (!empty($id)) {
            $teacherWorkItem = $repository->find($id);
        } else {
            $teacherWorkItem = new TeacherWorkSet();
        }

        if (!empty($teacherId)) {
            $teacher = $this->getDoctrine()->getRepository(Teacher::class)->find($teacherId);
        } else {
            $teacher = null;
        }
        if (!empty($departmentId)) {
            $department = $this->getDoctrine()->getRepository(Department::class)->find($departmentId);
        } else {
            if ($teacher) {
                $department = $teacher->getDepartment();
            } else {
                $department = null;
            }
        }

        $form = $this->createForm(TeacherWorkSetFormType::class, $teacherWorkItem, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER')),
            'teacher' => $teacher,
            'department' => $department,
            'year' => $year,
            'workload' => $workload
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $teacherWorkSet = $form->getData();
            $fields = [
                'note'
            ];
            foreach ($fields as $field) {
                $teacherWorkSet->setDataField($field, $form->get($field)->getData());
            }
            $repository->save($teacherWorkSet);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE,
                    EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), 0, 0, 'Teacher work item');
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_teacherworkset');
        }
        return $this->render('teacher_work_set/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/teacherworkset/view/{teacherId?0}/{teacherWorkSetId}/{year?2020}", name="faculty_teacher_workset")
     */
    public function workset(Request $request, WorkColumnsArray $workColumnsManager) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $workcolumn_texts = $workColumnsManager->getWorkColumnTexts();

        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $teacherWorkSetRepository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $teacherId = $request->attributes->get('teacherId');
        $teacherWorkSetId = $request->attributes->get('teacherWorkSetId');
        $year = $request->attributes->get('year');
        $teacher = $teacherRepository->find($teacherId);
        if ($teacherWorkSetId) {
            $teacherWorkSet = $teacherWorkSetRepository->find($teacherWorkSetId);
        } else {
            $teacherWorkSets = $teacher->getTeacherWorkSets();
            if (sizeof($teacherWorkSets) > 0) {
                $teacherWorkSet = $teacherWorkSets[0];
            } else {
                $teacherWorkSet = null;
            }
        }
        if ($teacherWorkSet) {
            $workload = $teacherWorkSet->getWorkload();
            $yearparts = [];
            $workitems = $teacherWorkSet->getTeacherWorkItems();
            $yearpartCount = 2;
            if ($teacher->getDepartment()->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                $yearpartCount = 3;
            }
            $totalsums = $workColumnsManager->getEmptyWorkColumnsArray();
//                [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $taughtCourses = $teacher->getTaughtCourses();
//        $semesterTaughtCourses = [];
//        foreach($taughtCourses as $taughtCourse)
//        {
//            if($taughtCourse->getYear()==$year && $taughtCourse->getSemester()==$this->systemInfoManager->getCurrentSemester())
//            {
//                $semesterTaughtCourses[] = $taughtCourse;
//            }
//        }

            for ($i = 1; $i <= $yearpartCount; $i++) {
                $items = [];
                $sums = $workColumnsManager->getEmptyWorkColumnsArray();
//                    [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                foreach ($workitems as $workitem) {
                    if ($workitem->getWorkload() == $workload) {
                        if ($workitem->getSemester() == $i && $workitem->getYear() == $year) {
                            $groupcourses = explode(",", $workitem->getStudentGroups());
                            $groups = [];
                            $programCourses = [];
                            $totalStudentCount = 0;
                            $groupCount = 0;
                            $lectureHours = 0;
                            $labHours = 0;
                            $practiceHours = 0;
                            $noncredit = 0;
                            $studyYear = 1;
                            foreach ($groupcourses as $groupcourse) {
                                $pair = explode("-", $groupcourse);
                                if (sizeof($pair) > 1) {
                                    $group = $groupRepository->findOneBy(['systemId' => $pair[0]]);
                                    $programCourse = $programCourseRepository->find($pair[1]);
                                    $groups[] = $group;
                                    $programCourses[] = $programCourse;
                                    $totalStudentCount += $group->getTotalStudentCount();
                                    $groupCount++;
                                    //if()
                                    $lectureHours = $programCourse->getLectureHours();
                                    $practiceHours = $programCourse->getPracticeHours();
                                    $labHours = $programCourse->getLabHours();
                                    $studyYear = $group->getStudyYear();
                                }
                            }
                            $items[] = [
                                'workitem' => $workitem,
                                'groups' => $groups,
                                'studyyear' => $studyYear,
                                'programcourses' => $programCourses,
                                'studentcount' => $totalStudentCount,
                                'groupcount' => $workitem->getGroupCount(),
                                'lecturehours' => $lectureHours,
                                'practicehours' => $practiceHours,
                                'labhours' => $labHours,
                            ];
                            $data = $workitem->getData();
                            for ($z = 9; $z < 30; $z++) {
                                $sums[$z] += intval($data[$z]);
                                $totalsums[$z] += intval($data[$z]);
                            }
                        }
                    }
                }
                $yearparts[] = ['items' => $items, 'yearpart' => $i, 'sums' => $sums];
            }
        } else {
            $yearpartCount = 2;
            $workload = 0;
            $yearparts = [1, 2];
            $year = 2020;
            $totalsums = $workColumnsManager->getEmptyWorkColumnsArray();
            $taughtCourses = [];
            $semester = 1;
        }

        return $this->render('teacher_work_set/worksetitems.html.twig', [
                    'teacher' => $teacher,
                    'teacherWorkSet' => $teacherWorkSet,
                    'yearpartCount' => $yearpartCount,
                    'workload' => ['value' => $workload, 'name' => WorkloadEnum::getTypeName($workload)],
                    'yearparts' => $yearparts,
                    'year' => $year,
                    'columnNames' => $workcolumn_texts,
                    'totalsums' => $totalsums,
                    'taughtCourses' => $taughtCourses,
                    'semester' => $this->systemInfoManager->getCurrentSemester(),
        ]);
    }

    /**
     * @Route("/faculty/teacherworkset/updatefield", name="faculty_teacherworkset_updatefield")
     */
    public function updateField(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $teacherWorkSetId = $request->request->get('teacherWorkSetId');
        $teacherWorkSetId = 11;
        $action = $request->request->get('action');
        $value = $request->request->get('value');
        $teacherWorkSet = $repository->find($teacherWorkSetId);
        $i = 0;
        if ($teacherWorkSet) {
            if ($action == "viewOrderUp") {
                $viewOrder = $teacherWorkSet->getViewOrder();
                if ($viewOrder - 1 >= 0) {
                    $teacherWorkSet->setViewOrder($viewOrder - 1);
                }
            } elseif ($action == "viewOrderDown") {
                $viewOrder = $teacherWorkSet->getViewOrder();
                $teacherWorkSet->setViewOrder($viewOrder + 1);
            }
        }
        $repository->save($teacherWorkSet);

        return new Response("<span style='color:green'>OK " . $action . " -- " . $value . "</span>");
    }

    /**
     * @Route("/faculty/departmentworksets/{departmentSystemId?0}/{year?2020}", name="faculty_teacher_workset_departmentworksets")
     */
    public function departmentWorksets(Request $request, WorkColumnsArray $workColumnsManager) {
        $this->denyAccessUnlessGranted("ROLE_DEPARTMENTHEAD");

        $workcolumn_texts = $workColumnsManager->getWorkColumnTexts();
        $year = $request->attributes->get('year');
        $departmentSystemId = $request->attributes->get('departmentSystemId');
        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
        $department = $departmentRepository->findOneBy(['systemId' => $departmentSystemId]);
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        $viewingTeacherDepartment = $teacherRepository->getManagedDepartment(['teacher_id' => $viewingTeacher->getId()]);
        //echo $viewingTeacherDepartment->getNameEnglish()."<br>";
        if ($viewingTeacherDepartment != $department) {
            //$this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        }

        $totalsums = $workColumnsManager->getEmptyWorkColumnsArray();
        $teacherWorkSets = $department->getTeacherWorkSets();
        $yearparts = [];
        $yearpartCount = 2;
        if ($department->getSystemId() == 66) {
            $yearpartCount = 3;
        }
        for ($i = 1; $i <= $yearpartCount; $i++) {
            $yearpartSum = $workColumnsManager->getEmptyWorkColumnsArray();
            $worksets = [];
            $sums = $workColumnsManager->getEmptyWorkColumnsArray();
            foreach ($teacherWorkSets as $teacherWorkSet) {
                if ($teacherWorkSet->getYear() == $year) {
                    $workSetSum = $workColumnsManager->getEmptyWorkColumnsArray();
                    $teacherWorkItems = $teacherWorkSet->getTeacherWorkItems();
                    foreach ($teacherWorkItems as $teacherWorkItem) {
                        if ($teacherWorkItem->getSemester() == $i) {
                            $data = $teacherWorkItem->getData();
                            for ($z = 9; $z < 30; $z++) {
                                $workSetSum[$z] += intval($data[$z]);
                                $yearpartSum[$z] += intval($data[$z]);
                                $totalsums[$z] += intval($data[$z]);
                            }
                        }
                    }

                    $worksets[] = [
                        'workset' => $teacherWorkSet,
                        'sums' => $workSetSum,
                    ];
                }
            }
            $yearparts[] = ['yearpart' => $i, 'worksets' => $worksets, 'sums' => $yearpartSum];
        }

        $yearSum = $workColumnsManager->getEmptyWorkColumnsArray();
        $worksets = [];
        $sums = $workColumnsManager->getEmptyWorkColumnsArray();
        foreach ($teacherWorkSets as $teacherWorkSet) {
            if ($teacherWorkSet->getYear() == $year) {
                $workSetSum = $workColumnsManager->getEmptyWorkColumnsArray();
                $teacherWorkItems = $teacherWorkSet->getTeacherWorkItems();
                foreach ($teacherWorkItems as $teacherWorkItem) {
                    $data = $teacherWorkItem->getData();
                    for ($z = 9; $z < 30; $z++) {
                        $workSetSum[$z] += intval($data[$z]);
                        $yearSum[$z] += intval($data[$z]);
                    }
                }

                $worksets[] = [
                    'workset' => $teacherWorkSet,
                    'sums' => $workSetSum,
                ];
            }
        }
        $yearData = ['worksets' => $worksets, 'sums' => $yearSum];

        return $this->render('teacher_work_set/departmentworksets.html.twig', [
                    'yearpartCount' => $yearpartCount,
                    'yearparts' => $yearparts,
                    'year' => $year,
                    'department' => $department,
                    'columnNames' => $workcolumn_texts,
                    'totalsums' => $totalsums,
                    'yearData' => $yearData,
                    'viewingTeacher' => $viewingTeacher,
        ]);
    }

    /**
     * @Route("/faculty/teacherworksetchangeorder/{id?0}/{viewOrder?0}", name="faculty_teacherworkset_changeorder")
     */
    public function changeOrder(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $id = $request->attributes->get('id');
        $viewOrder = $request->attributes->get('viewOrder');
        $teacherWorkSet = $repository->find($id);
        $teacherWorkSet->setViewOrder($viewOrder);
        $repository->save($teacherWorkSet);

        return new Response("<span style='color:green'>" . $viewOrder . "</span>");
    }

    /**
     * @Route("/faculty/alldepartmentworksets/{year?2020}", name="faculty_teacher_workset_alldepartmentworksets")
     */
    public function allDepartmentWorksets(Request $request, WorkColumnsArray $workColumnsManager) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $workcolumn_texts = $workColumnsManager->getWorkColumnTexts();
        $year = $request->attributes->get('year');
        $departmentSystemId = $request->attributes->get('departmentSystemId');
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculties = $facultyRepository->findAll();
        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);

        $totalsums = $workColumnsManager->getEmptyWorkColumnsArray();

        $yearparts = [];
        $departmentSets = [];
        foreach ($faculties as $faculty) {
            $departments = $faculty->getDepartments();
            foreach ($departments as $department) {
                $yearSum = $workColumnsManager->getEmptyWorkColumnsArray();
                $worksets = [];
                $sums = $workColumnsManager->getEmptyWorkColumnsArray();
                $teacherWorkSets = $department->getTeacherWorkSets();
                foreach ($teacherWorkSets as $teacherWorkSet) {
                    if ($teacherWorkSet->getYear() == $year) {
                        $workSetSum = $workColumnsManager->getEmptyWorkColumnsArray();
                        $teacherWorkItems = $teacherWorkSet->getTeacherWorkItems();
                        foreach ($teacherWorkItems as $teacherWorkItem) {
                            $data = $teacherWorkItem->getData();
                            for ($z = 9; $z < 30; $z++) {
                                $workSetSum[$z] += intval($data[$z]);
                                $yearSum[$z] += intval($data[$z]);
                            }
                        }

                        $worksets[] = [
                            'workset' => $teacherWorkSet,
                            'sums' => $workSetSum,
                        ];
                    }
                }
                $departmentSets[] = ['department' => $department, 'worksets' => $worksets, 'sums' => $yearSum];
            }
        }

        return $this->render('teacher_work_set/alldepartmentworksets.html.twig', [
                    'year' => $year,
                    'department' => $department,
                    'columnNames' => $workcolumn_texts,
//                    'totalsums' => $totalsums,
                    'departmentSets' => $departmentSets,
        ]);
    }

    /**
     * @Route("/faculty/teacherworkset/savevieworder/{orderdata?}", name="faculty_teacherworkset_savevieworder")
     */
    public function saveViewOrder(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $orderdata = $request->attributes->get('orderdata');
        $arrPairs = explode("|", $orderdata);
        $content = "Saving order: ";
        foreach ($arrPairs as $pair) {
            $arrValues = explode(":", $pair);
            if (sizeof($arrValues) == 2) {
                $teacherWorkSet = $repository->find(intval($arrValues[0]));
                if ($teacherWorkSet) {
                    $teacherWorkSet->setViewOrder(intval($arrValues[1]));
                    $repository->save($teacherWorkSet);
                    $content .= $arrValues[0] . ":" . $arrValues[1] . ", ";
                }
            }
        }

        return new Response($content);
    }

    /**
     * @Route("/faculty/teacherworkset/viewschedulechanges/{teacherWorkSetId}/{year?2021}/{semester?1}", name="faculty_teacher_workset_schedulechanges")
     */
    public function scheduleChanges(Request $request, WorkColumnsArray $workColumnsManager) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $teacherWorkSetRepository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $teacherWorkSetId = $request->attributes->get('teacherWorkSetId');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $result = "";
        $scheduleChanges = [];
        if ($teacherWorkSetId) {
            $teacherWorkSet = $teacherWorkSetRepository->find($teacherWorkSetId);
            if ($teacherWorkSet) {
                $scheduleChanges = $teacherWorkSet->getScheduleChanges($semester);
            }
        }
        return $this->render('teacher_work_set/schedulechanges.html.twig', [
                    'year' => $year,
                    'semester' => $semester,
                    'teacherWorkSet' => $teacherWorkSet,
                    'scheduleChanges' => $scheduleChanges,
        ]);
    }

}
