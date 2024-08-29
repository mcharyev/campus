<?php

namespace App\Controller\Course;

use App\Entity\TaughtCourse;
use App\Entity\Teacher;
use App\Entity\Department;
use App\Entity\Faculty;
use App\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TaughtCourseFormType;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class TaughtCourseController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/taughtcourse", name="faculty_taughtcourse")
     * @Route("/faculty/taughtcourse/search/{searchField?}/{searchValue?}", name="faculty_taughtcourse_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('taught_course/index.html.twig', [
                    'controller_name' => 'TaughtCourseController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/taughtcourse/list", name="faculty_taughtcourse_list")
     * @Route("/faculty/taughtcourse/list/{offset?0}/{pageSize?20}/{sorting?taught_course.id}/{searchField?}/{searchValue?}", name="faculty_taughtcourse_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'taught_course',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'exactMatch' => false,
            ];
            if ($request->attributes->get('searchField') == 'teacher_id') {
                $params['exactMatch'] = true;
            }
            if ($request->attributes->get('searchField') == 'id') {
                $params['exactMatch'] = true;
            }
            $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
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
     * @Route("/faculty/taughtcourse/delete", name="faculty_taughtcourse_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
            $taughtCourse = $repository->find($id);
            $repository->remove($taughtCourse);

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
     * @Route("/faculty/taughtcourse/new", name="faculty_taughtcourse_new")
     * @Route("/faculty/taughtcourse/add/{departmentId?}/{courseTitle?}/{teacherId?}/{studentGroups?}", name="faculty_taughtcourse_add")
     * @Route("/faculty/taughtcourse/edit/{id?0}", name="faculty_taughtcourse_edit")
     * @Route("/faculty/taughtcourse/editbyteacher/{id?0}/{teacherId}", name="faculty_taughtcourse_editbyteacher")
     */
    public function new(Request $request) {
        $id = $request->attributes->get('id');
        $departmentId = $request->attributes->get('departmentId');
        $teacherId = $request->attributes->get('teacherId');
        $courseTitle = $request->attributes->get('courseTitle');
        $studentGroups = $request->attributes->get('studentGroups');
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        if (!$studentGroups) {
            $studentGroups = '';
        }

        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
        if ($departmentId) {
            $department = $departmentRepository->find($departmentId);
        } else {
            $department = null;
        }
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);

        $disabledType = 0;
        if (!empty($id)) {
            $taughtCourse = $repository->find($id);
        } else {
            $taughtCourse = new TaughtCourse();
        }
        $teacher = null;
        if (!empty($teacherId)) {
            $teacher = $teacherRepository->find($teacherId);
            if ($viewingTeacher != $teacher) {
                $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
            } else {
                $disabledType = 1;
            }
        } else {
            $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
            //$teacher = null;
        }

        $form = $this->createForm(TaughtCourseFormType::class, $taughtCourse, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER')),
            'disabledType' => $disabledType,
            'teacher' => $teacher,
            'year' => $this->systemInfoManager->getCurrentYear(),
            'semester' => $this->systemInfoManager->getCurrentSemester(),
            'startDate' => $this->systemInfoManager->getSemesterBeginDate($this->systemInfoManager->getCurrentSemester()),
            'endDate' => $this->systemInfoManager->getSemesterEndDate($this->systemInfoManager->getCurrentSemester()),
            'courseTitle' => $courseTitle,
            'studentGroups' => $studentGroups,
            'department' => $department,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $taughtCourse = $form->getData();
            $fields = [
                'note', 'groups', 'teacher', 'lecture_topics', 'practice_topics', 'lab_topics',
                'seminar_combined'
            ];
            foreach ($fields as $field) {
                $taughtCourse->setDataField($field, $form->get("note_" . $field)->getData());
            }

            $taughtCourse->setDataField('course_code', $form->get("courseCode")->getData());
            $taughtCourse->setDataField('course_name', $form->get("nameEnglish")->getData());
            $taughtCourse->setDataField('groupLetterCodes', $this->getGroupNamesFromCodes($taughtCourse->getStudentGroups()));
//            $taughtCourse->setDataField('note', $form->get('note_note')->getData());
//            $taughtCourse->setDataField('groups', $form->get('note_groups')->getData());
//            $taughtCourse->setDataField('teacher', $form->get('note_teacher')->getData());
//            $taughtCourse->setDataField('course_name', $form->get('note_course_name')->getData());

            $repository->save($taughtCourse);
            $sourcePath = urldecode($form->get('source_path')->getData());
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE,
                    EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(),
                    EntityTypeEnum::ENTITY_TAUGHTCOURSE, $taughtCourse->getId(),
                    "Edited course: " . $taughtCourse->getDataField('course_name'));
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_taughtcourse');
        }
        return $this->render('taught_course/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/taughtcourse/jsonlistitems/{teacher?}/{year?}/{semester?}", name="faculty_taughtcourse_jsonlistitems")
     */
    public function taughtCourseJson(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $result_array = array();
        $teacherId = $request->attributes->get('teacher');
        $semester = $request->attributes->get('semester');
        $year = $request->attributes->get('year');
        try {
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            if (!empty($teacherId)) {
                $teacher = $teacherRepository->find($teacherId);
                $taughtCourses = $teacher->getTaughtCourses();
                foreach ($taughtCourses as $taughtCourse) {
                    if ($taughtCourse->getYear() == $year && $taughtCourse->getSemester() == $semester) {
                        $result_array[] = array(
                            "id" => $taughtCourse->getId(),
                            "value" => $taughtCourse->getFullname()
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
     * @Route("/faculty/taughtcourse/department/jsonlistitems/{departmentId?}/{year?}/{semester?}", name="faculty_taughtcourse_department_jsonlistitems")
     */
    public function taughtCourseDepartmentJson(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $result_array = array();
        $departmentId = $request->attributes->get('departmentId');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        try {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            if (!empty($departmentId)) {
                $department = $departmentRepository->find($departmentId);
                $taughtCourses = $department->getTaughtCourses();
                foreach ($taughtCourses as $taughtCourse) {
                    if ($taughtCourse->getYear() == $year && $taughtCourse->getSemester() == $semester) {
                        $result_array[] = array(
                            "id" => $taughtCourse->getId(),
                            "value" => $taughtCourse->getFullname()
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
     * @Route("/faculty/taughtcourse/{unit}/{id}", name="faculty_taughtcourse_unit")
     */
    public function taughtCourseList(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $id = $request->attributes->get('id');
        $unit = $request->attributes->get('unit');
        $faculty = null;
        $department = null;
        $group = null;
        $teacher = null;
        $courses = null;
        if ($unit == "teacher") {
            $repository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $repository->find($id);
            $faculty = $teacher->getFaculty();
            $department = $teacher->getDepartment();
            $courses = $teacher->getTaughtCourses();
        } elseif ($unit == "department") {
            $repository = $this->getDoctrine()->getRepository(Department::class);
            $department = $repository->find($id);
            $faculty = $department->getFaculty();
            $courses = $department->getTaughtCourses();
        } elseif ($unit == "faculty") {
            $repository = $this->getDoctrine()->getRepository(Faculty::class);
            $faculty = $repository->find($id);
            foreach ($faculty->getDepartments() as $department) {
                foreach ($department->getTaughtCourses() as $taughtCourse) {
                    $courses[] = $taughtCourse;
                }
            }
        }
        return $this->render('taught_course/report.html.twig', [
                    'report_title' => "Courses by " . $unit,
                    'faculty' => $faculty,
                    'department' => $department,
                    'teacher' => $teacher,
                    'courses' => $courses
        ]);
    }

    /**
     * @Route("/faculty/taughtcourse/updatecourse/{taughtCourseId}/{field}/{value}", name="faculty_taughtcourse_updatecourse")
     */
    public function updateCourse(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $id = $request->attributes->get('taughtCourseId');
        $field = $request->attributes->get('field');
        $value = $request->attributes->get('value');
        $taughtCourse = $repository->find($id);
        $returnValue = '';
        switch ($field) {
            case 'department':
                $department = $this->getDoctrine()->getRepository(Department::class)->find($value);
                $taughtCourse->setDepartment($department);
                $returnValue = $department->getLetterCode();
                break;
            case 'gradingType':
                $taughtCourse->setGradingType(intval($value));
                $returnValue = ['Not gradable','Gradable'][$value];
                break;
        }
        $repository->save($taughtCourse);

        return new Response("<span style='color:green'>OK " . $field . "->" . $value . " = " . $returnValue . "</span>");
    }

    /**
     * @Route("/faculty/taughtcoursecustom/updategrouplettercodes/{id?0}", name="faculty_taughtcourse_updategrouplettercodes")
     */
    public function updateGroupLetterCodes(Request $request) {
        $id = $request->attributes->get('id');
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourse = $repository->find($id);
        $groupIds = $taughtCourse->getStudentGroups();
        $letterCodes = $this->getGroupNamesFromCodes($groupIds);
        $taughtCourse->setDataField('groupLetterCodes', $letterCodes);
        $repository->save($taughtCourse);
        return new Response("Updated groupLetterCodes: " . $groupIds . " => " . $letterCodes);
    }

    private function getGroupNamesFromCodes(?string $groupIdString) {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupNames = [];
        $groupIds = explode(",", $groupIdString);
        foreach ($groupIds as $groupId) {
            $group = $groupRepository->findOneBy(['systemId' => $groupId]);
            if (!empty($group)) {
                $groupNames[] = $group->getLetterCode();
            }
        }
        return implode(", ", $groupNames);
    }

}
