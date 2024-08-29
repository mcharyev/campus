<?php

namespace App\Controller\Workload;

use App\Entity\Department;
use App\Entity\DepartmentWorkItem;
use App\Entity\TeacherWorkItem;
use App\Entity\TeacherWorkSet;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Enum\WorkColumnEnum;
use App\Enum\WorkloadEnum;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\ProgramCourse;
use App\Entity\TaughtCourse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TeacherWorkItemFormType;
use App\Controller\Workload\WorkColumnsArray;

class TeacherWorkItemController extends AbstractController {

    private $systemEventManager;

    function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/faculty/teacherworkitem", name="faculty_teacherworkitem")
     * @Route("/faculty/teacherworkitem/search/{searchField?}/{searchValue?}", name="faculty_teacherworkitem_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, $searchField . "=" . $searchValue);
        return $this->render('teacher_work_item/index.html.twig', [
                    'controller_name' => 'TeacherWorkItemController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
        ]);
    }

    /**
     * @Route("/faculty/teacherworkitem/list", name="faculty_teacherworkitem_list")
     * @Route("/faculty/teacherworkitem/list/{offset?0}/{pageSize?20}/{sorting?teacher_work_item.id}/{searchField?}/{searchValue?}", name="faculty_teacherworkitem_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'teacher_work_item',
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
                'teacher_work_set_id',
                'taught_course_id',
            ];
            if (in_array($params['searchField'], $exactMatchableFields)) {
                $params['exactMatch'] = true;
            }
            $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
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
     * @Route("/faculty/teacherworkitem/delete/{id?}", name="faculty_teacherworkitem_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->attributes->get('id');
            if (empty($id)) {
                $id = $request->request->get('id');
            }
            //echo "id :".$id;
            $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
            $item = $repository->find($id);
            if ($item) {
                $repository->remove($item);

                //Return result
                $result_array = [
                    'Result' => "OK"
                ];
            } else {
                $result_array = [
                    'Result' => "ERROR",
                    'Message' => "Item does not exist"
                ];
            }
        } catch (\Exception $ex) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $ex->getMessage()
            ];
        }
        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/faculty/teacherworkitem/new", name="faculty_teacherworkitem_new")
     * @Route("/faculty/teacherworkitem/edit/{id?0}/{source_path?}", name="faculty_teacherworkitem_edit")
     * @Route("/faculty/teacherworkitem/editinteacher/{id?0}/{teacher_id?0}/{year?}/{semester?}/{workload?}/{department?}", name="faculty_teacherworkitem_editinteacher")
     * @Route("/faculty/teacherworkitem/editinworkset/{id?0}/{teacher_id?0}/{teacherWorkSetId?}/{year?}/{semester?}/{department?}", name="faculty_teacherworkitem_editinworkset")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $id = $request->attributes->get('id');
        $teacherId = $request->attributes->get('teacher_id');
        $teacherWorkSetId = $request->attributes->get('teacherWorkSetId');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $workload = $request->attributes->get('workload');
        $departmentId = $request->attributes->get('department');
        if (!empty($id)) {
            $teacherWorkItem = $repository->find($id);
        } else {
            $teacherWorkItem = new TeacherWorkItem();
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
        if (!empty($teacherWorkSetId)) {
            $teacherWorkSet = $this->getDoctrine()->getRepository(TeacherWorkSet::class)->find($teacherWorkSetId);
        } else {
            $teacherWorkSet = null;
        }

        $form = $this->createForm(TeacherWorkItemFormType::class, $teacherWorkItem, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER')),
            'teacher' => $teacher,
            'department' => $department,
            'year' => $year,
            'semester' => $semester,
            'workload' => $workload,
            'teacherWorkSet' => $teacherWorkSet,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $teacherWorkItem = $form->getData();
            $teacherWorkItem->setDataField(WorkColumnEnum::GROUPS, $form->get('customGroupCount')->getData());
            $teacherWorkItem->setDataField('includeColumn', $form->get('includeColumn')->getData());
//            $studentCount = $teacherWorkItem->get
//            $teacherWorkItem = $
            $groupLetterCodes = $this->getGroupNamesFromCodes($teacherWorkItem->getStudentGroups());
            $teacherWorkItem->setDataField('groupLetterCodes', $groupLetterCodes);
            $teacherWorkItem->setGroupLetterCodes($groupLetterCodes);
            $repository->save($teacherWorkItem);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE,
                    EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), 0, 0, 'Teacher work item');
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_teacherworkitem');
        }
        return $this->render('teacher_work_item/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/teacherworkitem/updatefield/", name="faculty_teacherworkitem_updatefield")
     */
    public function updateField(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $id = $request->request->get('workitem_id');
        $field = $request->request->get('field');
        $value = $request->request->get('value');
        $bigdata = $request->request->get('bigdata');
        $teacherWorkItem = $repository->find($id);
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
            $teacherWorkItem->setDataField($fieldValue, $valueValue);
            $i++;
        }
        $repository->save($teacherWorkItem);

        return new Response("<span style='color:green'>OK " . $field . "->" . $value . "</span>");
    }

    /**
     * @Route("/faculty/teacherworkitem/updateitem/{workitemId}/{field}/{value}", name="faculty_teacherworkitem_updateitem")
     */
    public function updateItem(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $id = $request->attributes->get('workitemId');
        $field = $request->attributes->get('field');
        $value = $request->attributes->get('value');

        $teacherWorkItem = $repository->find($id);
        switch ($field) {
            case 'taughtCourseId':
                $teacherWorkItem->setTaughtCourse($this->getDoctrine()->getRepository(TaughtCourse::class)->find($value));
                break;
            case 'includeColumn':
                $teacherWorkItem->setDataField('includeColumn', $value);
                break;
        }
        $repository->save($teacherWorkItem);

        return new Response("<span style='color:green'>OK " . $field . "->" . $value . "</span>");
    }

    /**
     * @Route("/faculty/teacherworkitem/teacher/{id?0}/{year?2019}/{workload?}", name="faculty_teacher_workitem")
     */
    public function workitem(Request $request, WorkColumnsArray $workColumnsManager) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $workcolumn_texts = $workColumnsManager->getWorkColumnTexts();

        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $id = $request->attributes->get('id');
        $year = $request->attributes->get('year');
        $workload = $request->attributes->get('workload');
        $teacher = $teacherRepository->find($id);
        $yearparts = [];
        $workitems = $teacher->getTeacherWorkItems();
        $yearpartCount = 2;
        if ($teacher->getDepartment()->getId() == 15) {
            $yearpartCount = 3;
        }
        $totalsums = $workColumnsManager->getEmptyWorkColumnsArray();
//                [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
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

        return $this->render('teacher_work_item/workitem.html.twig', [
                    'teacher' => $teacher,
                    'yearpartCount' => $yearpartCount,
                    'workload' => ['value' => $workload, 'name' => WorkloadEnum::getTypeName($workload)],
                    'yearparts' => $yearparts,
                    'year' => $year,
                    'columnNames' => $workcolumn_texts,
                    'totalsums' => $totalsums
        ]);
    }

    private function getGroupNamesFromCodes(?string $groupCoursePairString) {
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
     * @Route("/faculty/teacherworkitem/addtoworkset/{teacherWorkItemId?0}/{teacherWorkSetId?0}", name="faculty_teacherworkitem_addtoworkset")
     */
    public function addToWorkSet(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $teacherWorkSetRepository = $this->getDoctrine()->getRepository(TeacherWorkSet::class);
        $teacherWorkItemId = $request->attributes->get('teacherWorkItemId');
        $teacherWorkSetId = $request->attributes->get('teacherWorkSetId');

        $teacherWorkItem = $repository->find($teacherWorkItemId);
        $teacherWorkSet = $teacherWorkSetRepository->find($teacherWorkSetId);
        if ($teacherWorkItem && $teacherWorkSet) {
            $teacherWorkItem->setTeacherWorkSet($teacherWorkSet);
            $repository->save($teacherWorkItem);
            return new Response("Added! " . $teacherWorkItem->getTitle() . " to " . $teacherWorkSet->getTitle());
        } else {
            return new Response("Wrong data!");
        }
    }

    /**
     * @Route("/faculty/teacherworkitem/changeorder/{id?0}/{viewOrder?0}", name="faculty_teacherworkitem_changeorder")
     */
    public function changeOrder(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $id = $request->attributes->get('id');
        $viewOrder = $request->attributes->get('viewOrder');
        $teacherWorkItem = $repository->find($id);
        $teacherWorkItem->setViewOrder($viewOrder);
        $repository->save($teacherWorkItem);

        return new Response("<span style='color:green'>" . $viewOrder . "</span>");
    }

    /**
     * @Route("/faculty/teacherworkitem/changegroups/{id?0}/{groups?0}", name="faculty_teacherworkitem_changegroups")
     */
    public function changeGroups(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $id = $request->attributes->get('id');
        $groups = $request->attributes->get('groups');
        $teacherWorkItem = $repository->find($id);
        $teacherWorkItem->setStudentGroups($groups);
        $repository->save($teacherWorkItem);

        return new Response("<span style='color:green'>" . $groups . "</span>");
    }

    /**
     * @Route("/faculty/teacherworkitem/addexpress", name="faculty_teacherworkitem_addexpress")
     */
    public function addExpress(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
//        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
//        $departmentId = $request->request->get('departmentId');
//        $teacherId = $request->request->get('teacherId');
        $teacherWorkSetId = $request->request->get('teacherWorkSetId');
        $yearpart = $request->request->get('yearpart');
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
        $teacherWorkItemRepository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);

        foreach ($rows as $row) {
            $fields = explode("\t", "*\t" . $row);
            if (sizeof($fields) == 30) {
                $content .= $i . ") " . $row . "<br>";
                $content .= "Length: " . sizeof($fields) . ", ";
                $content .= "<span style='font-weight:bold;'>Title: '" . $fields[2] . "'</span>, ";
                $groups = str_replace(" ", "", $fields[3]);
                $groups = str_replace(";", ",", $groups);
                $groupArray = explode(",", $groups);
                if (sizeof($groupArray) > 0) {
                    $group = $groupArray[0];
                } else {
                    $group = null;
                }
                $content .= "Groups: '" . $fields[3] . "' Searching: " . $group . "<br>";

                $departmentWorkItem = $this->findDepartmentWorkItem($yearpart, $teacherWorkSet->getDepartment(), $fields[2], $group);
                if ($departmentWorkItem) {
                    if (sizeof($groupArray) > 1) {
                        $groupCourse = $departmentWorkItem->getStudentGroups();
                    } else {
                        $groupCourse = $this->getSingleGroupCourseFromArray($departmentWorkItem->getStudentGroups(), $group);
                    }
                    $content .= "Department Work Item: <span style='color:green; font-weight:bold;'>FOUND " . $departmentWorkItem->getId() . ", "
                            . $departmentWorkItem->getTitle() . ", "
                            . $departmentWorkItem->getData()[WorkColumnEnum::GROUPNAMES] . ", "
                            . " GROUP: " . $groupCourse . ", "
                            . "</span><br>";
                    if ($commit == 1) {
                        $teacherWorkItem = new TeacherWorkItem();
                        $teacherWorkItem->setTeacher($teacherWorkSet->getTeacher());
                        $teacherWorkItem->setTeacherWorkset($teacherWorkSet);
                        $teacherWorkItem->setDepartment($departmentWorkItem->getDepartment());
                        $teacherWorkItem->setDepartmentWorkItem($departmentWorkItem);
                        $teacherWorkItem->setYear($departmentWorkItem->getYear());
                        $teacherWorkItem->setSemester($departmentWorkItem->getSemester());
                        $teacherWorkItem->setType($departmentWorkItem->getType());
                        $teacherWorkItem->setWorkload($teacherWorkSet->getWorkload());
                        $teacherWorkItem->setViewOrder($i * 10);
                        $teacherWorkItem->setStatus(1);
                        $teacherWorkItem->setDateUpdated(new \DateTime());

                        $teacherWorkItem->setTitle($departmentWorkItem->getTitle());
                        $teacherWorkItem->setDataField(WorkColumnEnum::TITLE, $departmentWorkItem->getTitle());
                        $teacherWorkItem->setDataField(WorkColumnEnum::GROUPNAMES, $fields[3]);
                        $teacherWorkItem->setStudentGroups($groupCourse);
                        $teacherWorkItem->setDataField(WorkColumnEnum::STUDYYEAR, $fields[4]);
                        $teacherWorkItem->setStudentCount(intval($fields[5]));
                        $teacherWorkItem->setDataField(WorkColumnEnum::STUDENTCOUNT, $fields[5]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::COHORT, $fields[6]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::GROUPS, $fields[7]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::LECTUREPLAN, $fields[9]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::LECTUREACTUAL, $fields[10]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::PRACTICEPLAN, $fields[11]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::PRACTICEACTUAL, $fields[12]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::LABPLAN, $fields[13]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::LABACTUAL, $fields[14]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::CONSULTATION, $fields[15]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::NONCREDIT, $fields[16]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::MIDTERMEXAM, $fields[17]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::FINALEXAM, $fields[18]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::STATEEXAM, $fields[19]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::SIWSI, $fields[20]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::CUSW, $fields[21]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::INTERNSHIP, $fields[22]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::THESISADVISING, $fields[23]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::THESISEXAM, $fields[24]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::COMPETITION, $fields[25]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::CLASSOBSERVATION, $fields[26]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::ADMINISTRATION, $fields[27]);
                        $teacherWorkItem->setDataField(WorkColumnEnum::THESISREVIEW, $fields[28]);
                        $totalSum = intval($fields[10]) + intval($fields[12]) + intval($fields[14]);
                        for ($t = 15; $t < 29; $t++) {
                            $totalSum += intval($fields[$t]);
                        }
                        $teacherWorkItem->setDataField(WorkColumnEnum::TOTAL, $totalSum);
                        try {
                            $teacherWorkItemRepository->save($teacherWorkItem);
                            $content .= "DATA COMMIT: <span style='color:green; font-weight:bold;font-size:14px;'>YES</span><br>";
                        } catch (Exception $e) {
                            $content .= "ERROR: <span style='color:green; font-weight:bold;font-size:14px;'>" . $e->getMessage() . "</span><br>";
                        }
                    }
                } else {
                    $content .= "Department Work Item: <span style='color:red; font-weight:bold;'>NOT FOUND</span> <br>";
                }
            }
            $i++;
        }

        return new Response($content);
    }

    private function findDepartmentWorkItem($yearpart, $department, $title, $group) {

        $firstBracketPosition = strpos($title, "(");
        $secondBracketPosition = strpos($title, ")");
//        echo "RESULT for bracket position: " . strpos($group, "(") . "<br>";
        if ($firstBracketPosition > 0) {
            // if course name is in format Course title (U) then remove (U)
            // if course name is in format Course Title (Digital Economy) no remove
            if ($secondBracketPosition - $firstBracketPosition > 5) {
                $courseTitle = trim($title);
            } else {
                $courseTitle = trim(substr($title, 0, $firstBracketPosition));
            }
//            echo "RESULT for bracket position: " . $courseTitle . "<br>";
        } else {
            $courseTitle = trim($title);
        }

        $bracketPosition = strpos($group, "(");
//        echo "RESULT for bracket position: " . strpos($group, "(") . "<br>";
        if ($bracketPosition > 0) {
            $groupName = substr($group, 0, $bracketPosition);
//            echo "RESULT for bracket position: " . $groupName . "<br>";
        } else {
            $groupName = $group;
        }

        $departmentWorkItemRepository = $this->getDoctrine()->getRepository(DepartmentWorkItem::class);
        $departmentWorkItems = $departmentWorkItemRepository->findBy([
            "semester" => $yearpart,
            "title" => $courseTitle,
            "department" => $department,
        ]);


        foreach ($departmentWorkItems as $departmentWorkItem) {
//            echo "RESULT ITEMS: " . $departmentWorkItem->getTitle() . ", Department:" . $departmentWorkItem->getDepartment()->getNameEnglish() . "<br>";
            if ($groupName) {
                if (strpos($group, "mugallym") === false) {
//                    echo "RESULT for Title: '" . $title . "' Group: " . $groupName . " in " . $departmentWorkItem->getData()[WorkColumnEnum::GROUPNAMES] . ":  " . strpos($departmentWorkItem->getData()[WorkColumnEnum::GROUPNAMES], $groupName) . "<br>";

                    if (strpos($departmentWorkItem->getData()[WorkColumnEnum::GROUPNAMES], $groupName) !== false) {
                        return $departmentWorkItem;
                    }
                } else {
                    return $departmentWorkItem;
                }
            } else {
                return $departmentWorkItem;
            }
        }

        return null;
    }

    private function getSingleGroupCourseFromArray($groupCourses, $groupLetterCode) {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $group = $groupRepository->findOneBy([
            "letterCode" => $groupLetterCode,
        ]);
        if ($group) {
            $groupCoursesArray = explode(",", $groupCourses);
            foreach ($groupCoursesArray as $groupCourse) {
                echo "GROUP COURSE: " . $group->getSystemId() . " IN " . $groupCourse . " RESULT: " . strpos($groupCourse, strval($group->getSystemId())) . " <br>";
                if (strpos($groupCourse, strval($group->getSystemId())) !== false) {
                    return $groupCourse;
                }
            }
        } else {
            return $groupCourses;
        }
    }

    /**
     * @Route("/faculty/teacherworkitem/savevieworder/{orderdata?}", name="faculty_teacherworkitem_savevieworder")
     */
    public function saveViewOrder(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $orderdata = $request->attributes->get('orderdata');
        $arrPairs = explode("|", $orderdata);
        $content = "Saving order: ";
        foreach ($arrPairs as $pair) {
            $arrValues = explode(":", $pair);
            if (sizeof($arrValues) == 2) {
                $teacherWorkItem = $repository->find(intval($arrValues[0]));
                if ($teacherWorkItem) {
                    $teacherWorkItem->setViewOrder(intval($arrValues[1]));
                    $repository->save($teacherWorkItem);
                    $content .= $arrValues[0] . ":" . $arrValues[1] . ", ";
                }
            }
        }

        return new Response($content);
    }

}
