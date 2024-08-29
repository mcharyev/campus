<?php

namespace App\Controller\Workload;

use App\Entity\Department;
use App\Entity\DepartmentWorkItem;
use App\Service\DepartmentWorkItemManager;
use App\Entity\Faculty;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Enum\WorkColumnEnum;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\ProgramCourse;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\DepartmentWorkItemFormType;
use App\Service\PersonalInfoManager;
use App\Entity\StudyProgram;
use App\Controller\Workload\WorkColumnsArray;
use App\Enum\WorkloadEnum;

class DepartmentWorkItemController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/departmentworkitem", name="faculty_departmentworkitem")
     * @Route("/faculty/departmentworkitem/search/{searchField?}/{searchValue?}", name="faculty_departmentworkitem_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupRepository->findAll();
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, $searchField . "=" . $searchValue);
        return $this->render('department_work_item/index.html.twig', [
                    'controller_name' => 'DepartmentWorkItemController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'groups' => $groups
        ]);
    }

    /**
     * @Route("/faculty/departmentworkitem/list", name="faculty_departmentworkitem_list")
     * @Route("/faculty/departmentworkitem/list/{offset?0}/{pageSize?20}/{sorting?department_work_item.id}/{searchField?}/{searchValue?}", name="faculty_departmentworkitem_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'department_work_item',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
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
     * @Route("/faculty/departmentworkitem/delete/{id?}", name="faculty_departmentworkitem_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            if (empty($id)) {
                $id = $request->attributes->get('id');
            }
            $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
            $departmentWorkItem = $repository->find($id);
            if ($departmentWorkItem) {
                $repository->remove($departmentWorkItem);
            }

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
     * @Route("/faculty/departmentworkitem/new", name="faculty_departmentworkitem_new")
     * @Route("/faculty/departmentworkitem/edit/{id?0}/{source_path?}", name="faculty_departmentworkitem_edit")
     * @Route("/faculty/departmentworkitem/editindepartment/{id?0}/{department_id?0}/{year?}/{semester?}/{title?}/{groupcourse?}", name="faculty_departmentworkitem_editindepartment")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $id = $request->attributes->get('id');
        $departmentId = $request->attributes->get('department_id');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $title = $request->attributes->get('title');
        echo "Title:" . $title . "<br>";
        $groupcourse = $request->attributes->get('groupcourse');
        if (!empty($id)) {
            $departmentWorkItem = $repository->find($id);
        } else {
            $departmentWorkItem = new DepartmentWorkItem();
        }

        if (!empty($departmentId)) {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            $department = $departmentRepository->findOneBy(['systemId' => $departmentId]);
        } else {
            $department = null;
        }

        $form = $this->createForm(DepartmentWorkItemFormType::class, $departmentWorkItem, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER')),
            'department' => $department,
            'year' => $year,
            'semester' => $semester,
            'title' => $title,
            'groupcourse' => $groupcourse
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $departmentWorkItem = $form->getData();
            $departmentWorkItem->setDataField(WorkColumnEnum::GROUPS, $form->get('customGroupCount')->getData());
//            $studentCount = $departmentWorkItem->get
//            $departmentWorkItem = $

            $repository->save($departmentWorkItem);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE,
                    EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), 0, 0, 'Department work item');
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_departmentworkitem');
        }
        return $this->render('department_work_item/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/departmentworkitem/addfast/{id?0}/{department_id?0}/{year?}/{semester?}/{title?}/{groupcourse?}/{type}/{lettercode?}/{studentCount?0}", name="faculty_departmentworkitemaddfast")
     */
    public function addFast(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $departmentId = $request->attributes->get('department_id');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $title = $request->attributes->get('title');
        $groupcourse = $request->attributes->get('groupcourse');
        $type = $request->attributes->get('type');
        $letterCode = $request->attributes->get('lettercode');
        $studentCount = $request->attributes->get('studentCount');

        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
        $department = $departmentRepository->findOneBy(["systemId" => $departmentId]);

        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $groupCourseArray = explode("-", $groupcourse);
        $programCourseId = $groupCourseArray[1];
        $programCourse = $programCourseRepository->find($programCourseId);

        $departmentWorkItemRepository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $departmentWorkItem = new DepartmentWorkItem();
        $departmentWorkItem->setTitle($title);
        $departmentWorkItem->setDataField(WorkColumnEnum::TITLE, $title);
        $departmentWorkItem->setDepartment($department);
        $departmentWorkItem->setYear($year);
        $departmentWorkItem->setSemester($semester);
        $departmentWorkItem->setType($type);
        $departmentWorkItem->setStudentCount($studentCount);
        $departmentWorkItem->setDataField(WorkColumnEnum::STUDENTCOUNT, $studentCount);
        $departmentWorkItem->setStudentGroups($groupcourse);
        $departmentWorkItem->setDataField(WorkColumnEnum::COHORT, 1);
        $departmentWorkItem->setDataField(WorkColumnEnum::GROUPS, 1);
        $departmentWorkItem->setDataField(WorkColumnEnum::GROUPNAMES, $letterCode);
        $departmentWorkItem->setDataField(WorkColumnEnum::PRACTICEPLAN, $programCourse->getPracticeHours());
        $departmentWorkItem->setDataField(WorkColumnEnum::PRACTICEACTUAL, $programCourse->getPracticeHours());
        $departmentWorkItem->setDataField(WorkColumnEnum::CONSULTATION, 2);
        $departmentWorkItem->setDataField(WorkColumnEnum::FINALEXAM, floor($studentCount * 0.5));
        $departmentWorkItem->setDataField(WorkColumnEnum::GROUPNAMES, $letterCode);
        $departmentWorkItem->setViewOrder(1);
        $departmentWorkItem->setStatus(1);
        $departmentWorkItem->setDateUpdated(new \DateTime());
        $departmentWorkItemRepository->save($departmentWorkItem);
        return new Response("<span style='color:green'>OK " . $letterCode . " added</span>");
    }

    /**
     * @Route("/faculty/departmentworkitem/groupcount/{id?0}", name="faculty_departmentworkitem_groupcount")
     */
    public function groupCount(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $departmentWorkItemRepository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $departmentWorkItem = $departmentWorkItemRepository->find($id);
        $studentGroupsString = $departmentWorkItem->getStudentGroups();
        $studentGroups = explode(",", $studentGroupsString);
        $total = 0;
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupCount = 0;
        foreach ($studentGroups as $studentGroup) {
            $groupCourseArray = explode("-", $studentGroup);
            $group = $groupRepository->findOneBy(["systemId" => $groupCourseArray[0]]);
            $total += $group->getTotalStudentCount();
            $groupCount++;
        }
        return new Response($total . "|" . $groupCount);
    }

    private function getGroupNamesFromCodePairs(?string $groupCoursePairString) {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupNames = [];
        $groupCoursePairs = explode(",", $groupCoursePairString);
        foreach ($groupCoursePairs as $groupCoursePair) {
            $groupCoursePairData = explode("-", $groupCoursePair);
            $group = $groupRepository->findOneBy(['systemId' => $groupCoursePairData[0]]);
            if (!empty($group)) {
                $groupNames[] = $group->getLetterCode();
            }
        }

        return implode(", ", $groupNames);
    }

    /**
     * @Route("/faculty/departmentworkitem/updatefield/", name="faculty_departmentworkitem_updatefield")
     */
    public function updateField(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $id = $request->request->get('workitem_id');
        $field = $request->request->get('field');
        $value = $request->request->get('value');
        $bigdata = $request->request->get('bigdata');
        $departmentWorkItem = $repository->find($id);
        $arrPairs = explode("|", $bigdata);
        $i = 0;
        foreach ($arrPairs as $pair) {
            $arrValues = explode(":", $pair);
            if ($i < 2) {
                $fieldValue = $arrValues[0];
                $valueValue = $arrValues[1];
            } else {
                $fieldValue = intval($arrValues[0]);
                $valueValue = intval($arrValues[1]);
            }
            $departmentWorkItem->setDataField($fieldValue, $valueValue);
            $i++;
        }
        $departmentWorkItem->setDataField(WorkColumnEnum::GROUPNAMES, $this->getGroupNamesFromCodePairs($departmentWorkItem->getStudentGroups()));
        $repository->save($departmentWorkItem);

        return new Response("<span style='color:green'>OK " . $field . "->" . $value . "</span>");
    }

    /**
     * @Route("/faculty/departmentworkitem/savevieworder/{orderdata}", name="faculty_departmentworkitem_savevieworder")
     */
    public function saveViewOrder(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $orderdata = $request->attributes->get('orderdata');
        $arrPairs = explode("|", $orderdata);
        $content = "Saving order: ";
        foreach ($arrPairs as $pair) {
            $arrValues = explode(":", $pair);
            if (sizeof($arrValues) == 2) {
                $departmentWorkItem = $repository->find(intval($arrValues[0]));
                if ($departmentWorkItem) {
                    $departmentWorkItem->setViewOrder(intval($arrValues[1]));
                    $repository->save($departmentWorkItem);
                    $content .= $arrValues[0] . ":" . $arrValues[1] . ", ";
                }
            }
        }

        return new Response($content);
    }

    /**
     * @Route("/faculty/departmentworkitem/changeorder/{id?0}/{viewOrder?0}", name="faculty_departmentworkitem_changeorder")
     */
    public function changeOrder(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $id = $request->attributes->get('id');
        $viewOrder = $request->attributes->get('viewOrder');
        $departmentWorkItem = $repository->find($id);
        $departmentWorkItem->setViewOrder($viewOrder);
        $repository->save($departmentWorkItem);

        return new Response("<span style='color:green'>" . $viewOrder . "</span>");
    }

    /**
     * @Route("/faculty/departmentworkitem/changegroups/{id?0}/{groups?0}", name="faculty_departmentworkitem_changegroups")
     */
    public function changeGroups(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $id = $request->attributes->get('id');
        $groups = $request->attributes->get('groups');
        $departmentWorkItem = $repository->find($id);
        $departmentWorkItem->setStudentGroups($groups);
        $repository->save($departmentWorkItem);

        return new Response("<span style='color:green'>" . $groups . "</span>");
    }

    /**
     * @Route("/faculty/departmentworkitem/duplicate/", name="faculty_departmentworkitem_duplicate")
     */
    public function duplicate(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $id = $request->request->get('workitem_id');
        $departmentWorkItem = $repository->find($id);
        $newDepartmentWorkItem = clone $departmentWorkItem;
        //$newDepartmentWorkItem->setId(null);
        $newDepartmentWorkItem->setViewOrder($departmentWorkItem->getViewOrder() + 1);
        $repository->save($newDepartmentWorkItem);

        return new Response("<span style='color:green'>OK Duplication success</span>");
    }

    /**
     * @Route("/faculty/departmentworkitem/jsonlistitems/{department?}/{year?}/{semester?}", name="faculty_departmentworkitem_jsonlistitems")
     */
    public function departmentWorkItemJson(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_TEACHER");
        $result_array = array();
        $departmentId = $request->attributes->get('department');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        //$odd = intval($semester) % 2;
        //return new Response('ok'.$ids.":".$year.":".$semester);
        try {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            if (!empty($departmentId)) {
                $department = $departmentRepository->find($departmentId);
                $departmentWorkItems = $department->getDepartmentWorkItems();
                foreach ($departmentWorkItems as $departmentWorkItem) {
                    if ($departmentWorkItem->getSemester() == $semester && $departmentWorkItem->getYear() == $year) {
                        $result_array[] = array(
                            "id" => $departmentWorkItem->getId(),
                            "value" => "[" . $departmentWorkItem->getId() . "] " . $departmentWorkItem->getTitle() . " - " . $departmentWorkItem->getStudentGroups() . " - " . $departmentWorkItem->getGroupNames()
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            //Return error message
            $result_array[] = [
                'id' => "ERROR",
                'value' => $e->getMessage()
            ];
        }

        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/faculty/departmentworkitem/department/{id?0}/{year?2019}/{viewmode?0}/{counts?}", name="faculty_departmentworkitem_workitem")
     */
    public function workitem(Request $request, WorkColumnsArray $workColumnsManager) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $year = $request->attributes->get('year');
        $viewmode = $request->attributes->get('viewmode');
        if (empty($viewmode)) {
            $viewmode = 0;
        }

        $counts = $request->attributes->get('counts');
        if (empty($counts)) {
            // counts[3] = consultation, [4]=hasap [5]=midterm [6]=final [7]=cusw, [8]=round down final examination
            $counts = "[34, 34, 0, 2, 0, 0.5, 0.5, 0.35, 0]";
        }

        $workcolumnNames = $workColumnsManager->getWorkColumnTexts();

        $repository = $this->getDoctrine()->getRepository(Department::class);
        $department = $repository->findOneBy(['systemId' => $id]);
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $editable = true;
        if (!$this->isGranted("ROLE_SPECIALIST")) {
            $teacher = $this->getDoctrine()->getRepository(Teacher::class)->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($teacher) {
                if ($teacher != $department->getDepartmentHead()) {
                    //$this->denyAccessUnlessGranted();
                } else {
                    if ($year == 2020) {
                        $editable = true;
                    } else {
                        $editable = false;
                    }
                }
            }
        }

        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $yearpartCount = 2;
        if ($department->getSystemId() == 66) {
            $yearpartCount = 3;
        }
        $yearparts = [];
        $workitems = $department->getDepartmentWorkItems();
        $assignedCourses = [];
        $totalsums = $workColumnsManager->getEmptyWorkColumnsArray();
        $totalVacancySums = $workColumnsManager->getEmptyWorkColumnsArray();
        $totalAssignedSums = $workColumnsManager->getEmptyWorkColumnsArray();
        for ($i = 1; $i <= $yearpartCount; $i++) {
            $items = [];
            $sums = $workColumnsManager->getEmptyWorkColumnsArray();
            $vacancySums = $workColumnsManager->getEmptyWorkColumnsArray();
            $assignedSums = $workColumnsManager->getEmptyWorkColumnsArray();
            foreach ($workitems as $workitem) {
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
                        $assignedCourses[] = $groupcourse;
                        $pair = explode("-", $groupcourse);
                        if (sizeof($pair) > 1) {
                            $group = $groupRepository->findOneBy(['systemId' => $pair[0]]);
                            $programCourse = $programCourseRepository->find($pair[1]);
                            $programCourses[] = $programCourse;
                            //if()
                            $lectureHours = $programCourse->getLectureHours();
                            $practiceHours = $programCourse->getPracticeHours();
                            $labHours = $programCourse->getLabHours();
                            $noncredit = 0.35;
                            if ($group) {
                                $groups[] = $group;
                                $totalStudentCount += $group->getTotalStudentCount();
                                $studyYear = $group->getStudyYear();
                                $groupCount++;
                            }
                        }
                    }
                    //calculate vacancies for the department work item
                    $vacancy = $workColumnsManager->getEmptyWorkColumnsArray();
                    $totalAssigned = $workColumnsManager->getEmptyWorkColumnsArray();
                    $teacherWorkItems = $workitem->getTeacherWorkItems();
                    foreach ($teacherWorkItems as $teacherWorkItem) {
                        if ($teacherWorkItem->getWorkload() != WorkloadEnum::LOADREPLACEMENT)
                            for ($z = 9; $z < 30; $z++) {
                                $totalAssigned[$z] += intval($teacherWorkItem->getData()[$z]);
                                $assignedSums[$z] += intval($teacherWorkItem->getData()[$z]);
                                $totalAssignedSums[$z] += intval($teacherWorkItem->getData()[$z]);
                            }
                    }

                    for ($z = 9; $z < 30; $z++) {
                        if ($z != 9 && $z != 11 && $z != 13) {
                            $vacancy[$z] = $workitem->getData()[$z] - $totalAssigned[$z];
                        }
                    }

                    $items[] = [
                        'workitem' => $workitem,
                        'vacancy' => $vacancy,
                        'groups' => $groups,
                        'studyyear' => $studyYear,
                        'programcourses' => $programCourses,
                        'studentcount' => $totalStudentCount,
                        'groupcount' => $workitem->getGroupCount(),
                        'lecturehours' => $lectureHours,
                        'practicehours' => $practiceHours,
                        'labhours' => $labHours,
                        'noncredit' => $noncredit
                    ];
                    $data = $workitem->getData();
                    for ($z = 9; $z < 30; $z++) {
                        if ($z != 9 && $z != 11 && $z != 13) {
                            $vacancySums[$z] += $vacancy[$z];
                            $totalVacancySums[$z] += $vacancy[$z];
                        }
                        $sums[$z] += $data[$z];
                        $totalsums[$z] += $data[$z];
                    }
                }
            }
            $yearparts[] = ['items' => $items, 'yearpart' => $i, 'sums' => $sums, 'vacancySums' => $vacancySums, 'assignedSums' => $assignedSums];
        }

        $groupCourses = $this->departmentTaughtProgramCourses($department, $year);
        $unassignedCourses = [];
        foreach ($groupCourses as $groupCourse) {
            if (!in_array($groupCourse['code'], $assignedCourses)) {
                $unassignedCourses[] = $groupCourse;
            }
        }

        return $this->render('department_work_item/workitem.html.twig', [
                    'viewmode' => $viewmode,
                    'department' => $department,
                    'yearpartCount' => $yearpartCount,
                    'yearparts' => $yearparts,
                    'year' => $year,
                    'totalsums' => $totalsums,
                    'totalVacancySums' => $totalVacancySums,
                    'totalAssignedSums' => $totalAssignedSums,
                    'groupcourses' => $unassignedCourses,
                    'workcolumnNames' => $workcolumnNames,
                    'counts' => $counts,
                    'editable' => $editable
        ]);
    }

    private function departmentTaughtProgramCourses(Department $department, int $year) {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);

        //get Language Learning groups if department is LLD
        $isLLD = ($department->getSystemId() == $this->systemInfoManager->getLLDSystemId());
        $studyProgramRepository = $this->getDoctrine()->getRepository(StudyProgram::class);
        $lldProgram = $studyProgramRepository->find(37);
        $lldProgramCourses = $lldProgram->getProgramCourses();
        if ($isLLD) {
            $groups = $groupRepository->findGroupsBetweenYears($year + 5, $year + 5);
            $groupCourses = [];
            $semesters = [1, 2, 3];
            $beginYear = $this->systemInfoManager->getCurrentCommencementYear();
            if ($year == $beginYear) {
                $offset = 0;
            } else {
                $offset = 1;
            }
            $programCourses = $lldProgramCourses;
            foreach ($groups as $group) {
//            if ($group->getLetterCode() == "1EM") {
//                echo $group->getLetterCode() . "," . $group->getStudyYear() . "<br>";
//            }

                foreach ($semesters as $semester) {
                    $currentSemester = $semester;
                    foreach ($programCourses as $programCourse) {
//                        echo $programCourse->getNameEnglish() . " " . $programCourse->getSemester() . "=" . $currentSemester . "<br>";
                        //$data = $programCourse->getData();
                        //echo $group->getLetterCode()."<br>";
                        if ($programCourse->getSemester() == $currentSemester && $programCourse->getDepartment() == $department) {
                            $groupCourses[] = [
                                "course" => $programCourse->getNameTurkmen(),
                                "group" => $group,
                                "semester" => $currentSemester,
                                "code" => $group->getSystemId() . "-" . $programCourse->getId()
                            ];
                        }
                    }
                }
            }
        } else {
            // get 4 years if not Language Learning year
            $groups = $groupRepository->findGroupsBetweenYears($year + 1, $year + 4);
            $groupCourses = [];
            $semesters = [1, 2];
            $beginYear = $this->systemInfoManager->getCurrentCommencementYear();
            if ($year == $beginYear) {
                $offset = 0;
            } else {
                $offset = 1;
            }
            foreach ($groups as $group) {
                if ($group->getStatus() == 1) {
//            if ($group->getLetterCode() == "1EM") {
//                echo $group->getLetterCode() . "," . $group->getStudyYear() . "<br>";
//            }
                    $programCourses = $group->getStudyProgram()->getProgramCourses();
                    foreach ($semesters as $semester) {
                        if ($group->getStudyProgram()->getProgramLevel()->getSystemId() == 7) {
                            $currentSemester = ($group->getStudyYear() + $offset - 1) * 2 + $semester;
                        } else {
                            $currentSemester = ($group->getStudyYear() + $offset - 1) * 2 + $semester;
                        }
                        foreach ($programCourses as $programCourse) {
//                    if ($group->getLetterCode() == "1EM") {
//                        echo $programCourse->getNameEnglish() . " " . $programCourse->getSemester() . "=" . $currentSemester . "<br>";
//                    }
                            //$data = $programCourse->getData();
                            //echo $group->getLetterCode()."<br>";
                            if ($programCourse->getSemester() == $currentSemester && $programCourse->getDepartment() == $department) {
                                $groupCourses[] = [
                                    "course" => $programCourse->getNameTurkmen(),
                                    "group" => $group,
                                    "semester" => $currentSemester,
                                    "code" => $group->getSystemId() . "-" . $programCourse->getId()
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $groupCourses;
    }

    /**
     * @Route("/faculty/departmentworkitem/university/{year?2019}/{viewmode?0}", name="faculty_departmentworkitem_university")
     */
    public function university(Request $request, WorkColumnsArray $workColumnsManager) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $year = $request->attributes->get('year');
        $viewmode = $request->attributes->get('viewmode');
        if (empty($viewmode)) {
            $viewmode = 0;
        }

        $workcolumnNames = $workColumnsManager->getWorkColumnTexts();

        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculties = $facultyRepository->findAll();
        $editable = false;


        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $departmentSets = [];
        $universityTotalSums = $workColumnsManager->getEmptyWorkColumnsArray();
        foreach ($faculties as $faculty) {
            $departments = $faculty->getDepartments();
            foreach ($departments as $department) {
                $yearpartCount = 2;
                if ($department->getSystemId() == 66) {
                    $yearpartCount = 3;
                }
                $yearparts = [];
                $workitems = $department->getDepartmentWorkItems();
                $assignedCourses = [];
                $totalsums = $workColumnsManager->getEmptyWorkColumnsArray();
                $totalVacancySums = $workColumnsManager->getEmptyWorkColumnsArray();
                $totalAssignedSums = $workColumnsManager->getEmptyWorkColumnsArray();
                for ($i = 1; $i <= $yearpartCount; $i++) {
                    $items = [];
                    $sums = $workColumnsManager->getEmptyWorkColumnsArray();
                    $vacancySums = $workColumnsManager->getEmptyWorkColumnsArray();
                    $assignedSums = $workColumnsManager->getEmptyWorkColumnsArray();
                    foreach ($workitems as $workitem) {
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
                                $assignedCourses[] = $groupcourse;
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
                                    $noncredit = 0.35;
                                    $studyYear = $group->getStudyYear();
                                }
                            }
                            //calculate vacancies for the department work item
                            $vacancy = $workColumnsManager->getEmptyWorkColumnsArray();
                            $totalAssigned = $workColumnsManager->getEmptyWorkColumnsArray();
                            $teacherWorkItems = $workitem->getTeacherWorkItems();
                            foreach ($teacherWorkItems as $teacherWorkItem) {
                                for ($z = 9; $z < 30; $z++) {
                                    $totalAssigned[$z] += intval($teacherWorkItem->getData()[$z]);
                                    $assignedSums[$z] += intval($teacherWorkItem->getData()[$z]);
                                    $totalAssignedSums[$z] += intval($teacherWorkItem->getData()[$z]);
                                }
                            }

                            for ($z = 9; $z < 30; $z++) {
                                if ($z != 9 && $z != 11 && $z != 13) {
                                    $vacancy[$z] = $workitem->getData()[$z] - $totalAssigned[$z];
                                }
                            }

                            $items[] = [
                                'workitem' => $workitem,
                                'vacancy' => $vacancy,
                                'groups' => $groups,
                                'studyyear' => $studyYear,
                                'programcourses' => $programCourses,
                                'studentcount' => $totalStudentCount,
                                'groupcount' => $workitem->getGroupCount(),
                                'lecturehours' => $lectureHours,
                                'practicehours' => $practiceHours,
                                'labhours' => $labHours,
                                'noncredit' => $noncredit
                            ];
                            $data = $workitem->getData();
                            for ($z = 9; $z < 30; $z++) {
                                if ($z != 9 && $z != 11 && $z != 13) {
                                    $vacancySums[$z] += $vacancy[$z];
                                    $totalVacancySums[$z] += $vacancy[$z];
                                }
                                $sums[$z] += $data[$z];
                                $totalsums[$z] += $data[$z];
                                $universityTotalSums[$z] += $data[$z];
                            }
                        }
                    }
//                    $yearparts[] = ['items' => $items, 'yearpart' => $i, 'sums' => $sums, 'vacancySums' => $vacancySums, 'assignedSums' => $assignedSums];
                }


                $groupCourses = $this->departmentTaughtProgramCourses($department, $year);
                $unassignedCourses = [];
                foreach ($groupCourses as $groupCourse) {
                    if (!in_array($groupCourse['code'], $assignedCourses)) {
                        $unassignedCourses[] = $groupCourse;
                    }
                }
                $departmentSets[] = [
                    'department' => $department,
                    'totalSums' => $totalsums,
                    'totalVacancySums' => $totalVacancySums,
                    'totalAssignedSums' => $totalAssignedSums,
                    'groupcourses' => $unassignedCourses,
                ];
            }
        }

        return $this->render('department_work_item/university_workitem.html.twig', [
                    'viewmode' => $viewmode,
                    'departmentSets' => $departmentSets,
                    'universityTotalSums' => $universityTotalSums,
                    'year' => $year,
                    'workcolumnNames' => $workcolumnNames,
                    'editable' => $editable
        ]);
    }

}
