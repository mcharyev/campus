<?php

namespace App\Controller\Teacher;

use App\Entity\Teacher;
use App\Entity\Department;
use App\Form\TeacherFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class TeacherController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/teacher", name="faculty_teacher")
     * @Route("/faculty/teacher/search/{searchField?}/{searchValue?}", name="faculty_teacher_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('teacher/index.html.twig', [
                    'controller_name' => 'TeacherController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'systemInfo' => $this->systemInfoManager,
        ]);
    }

    /**
     * @Route("/faculty/teacher/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_teacher_list")
     * @Route("/faculty/teacher/list/{offset?0}/{pageSize?20}/{sorting?teacher.id}/{searchField?}/{searchValue?}", name="faculty_teacher_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'teacher',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Teacher::class);
            $recordCount = $repository->getRecordCount($params);
            $results = $repository->getRecords($params);            //Return result
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
     * @Route("/faculty/teacher/customlist/general", name="faculty_teacher_customlist_general")
     */
    public function listGeneral(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $content = "";
        try {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            $departments = $departmentRepository->findAll();
            $content .= "<table class='table'>";
            foreach ($departments as $department) {
                $teachers = $department->getTeachers();
                $content .= "<tr><td colspan='3' style='font-weight:bold'>" . $department->getNameEnglish() . "</td></tr>";
                foreach ($teachers as $teacher) {
                    $content .= "<tr><td>" . $teacher->getFullname() . "</td><td>" . $teacher->getId() . "</td><td>" . $teacher->getSystemId() . "</td></tr>";
                }
            }
            $content .= "</table>";
        } catch (\Exception $e) {
            //Return error message
            $content .= $e->getMessage();
        }

        return $this->render('teacher/teacher.html.twig', [
                    'controller_name' => 'TeacherController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/faculty/teacher/department/{departmentId?}", name="faculty_teacher_department")
     */
    public function departmentTeachers(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_DEPARTMENTHEAD");
        $departmentId = $request->attributes->get('departmentId');
        $content = "";
        try {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            $department = $departmentRepository->find($departmentId);
            $content .= "<table class='table'>";
            $teachers = [];
            $teacherWorkSets = $department->getTeacherWorkSets();
            //echo "count:".sizeof($teacherWorkItems);
            foreach ($teacherWorkSets as $teacherWorkSet) {
                //echo $teacherWorkItem->getTeacher()->getId()."<br>";
                if (!in_array($teacherWorkSet->getTeacher(), $teachers)) {
                    $teachers[] = $teacherWorkSet->getTeacher();
                }
            }
            $content .= "<tr><td colspan='3' style='font-weight:bold'>" . $department->getNameEnglish() . "</td></tr>";
            //echo sizeof($teachers);
            foreach ($teachers as $teacher) {
                $content .= "<tr><td>" . $teacher->getSystemId() . "</td>";
                $content .= "<td>" . $teacher->getFullname() . "</td>";
                $content .= "<td><a href='/faculty/scheduledisplay/teacher/" . $teacher->getId() . "'>Schedule</a></li></td>";
                $content .= "<td><a href='/faculty/taughtcourse/teacher/" . $teacher->getId() . "'>Courses</a></td>";
                $content .= "<td><a href='/faculty/teacherjournalnew/" . $teacher->getId() . "/0/2020/1'>Department Register</a></td>";
                $content .= "<td><a href='/faculty/teacherworkset/view/" . $teacher->getId() . "/0/2020'>Personal Workload</a></td>";
                $content .= "</tr>";
            }
            $content .= "</table>";
        } catch (\Exception $e) {
            //Return error message
            $content .= $e->getMessage();
        }

        return $this->render('teacher/teacher.html.twig', [
                    'controller_name' => 'TeacherController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/faculty/teacher/delete", name="faculty_teacher_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $teacher = $entityManager->getRepository(Teacher::class)->find($id);
            $entityManager->remove($teacher);
            $entityManager->flush();
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
     * @Route("/faculty/teacher/new", name="faculty_teacher_new")
     * @Route("/faculty/teacher/edit/{id?0}", name="faculty_teacher_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(Teacher::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $teacher = $repository->find($id);
        } else {
            $teacher = new Teacher();
        }
        $form = $this->createForm(TeacherFormType::class, $teacher, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $teacher = $form->getData();

            $repository->save($teacher);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_teacher');
        }
        return $this->render('teacher/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/teacher/jsonlist/{term?}", name="faculty_teacher_jsonlist")
     * @Route("/faculty/teacher/jsonlistitems/{ids?}", name="faculty_teacher_jsonlistitems")
     */
    public function jsonlist(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_TEACHER");
        $result_array = array();
        $firstname = $request->attributes->get('term');
        $ids = $request->attributes->get('ids');
        try {
            $repository = $this->getDoctrine()->getRepository(Teacher::class);
            if (!empty($ids)) {
                $items = explode(",", $ids);
                foreach ($items as $item) {
                    $teacher = $repository->findOneBy(['systemId' => $item]);
                    if ($teacher) {
                        $result_array[] = array(
                            "id" => $teacher->getSystemId(),
                            "teacher_position" => $teacher->getDepartment()->getNameTurkmen() . " kafedrasynyň mugallymy",
                            "name" => $teacher->getThreeNames()
                        );
                    }
                }
            } else {
                //if ($letterCode) {
                $repository = $this->getDoctrine()->getRepository(Teacher::class);
                $teachers = $repository->findByName($firstname);
                foreach ($teachers as $teacher) {
                    $result_array[] = array(
                        "id" => $teacher->getSystemId(),
                        "teacher_position" => $teacher->getDepartment()->getNameTurkmen() . " kafedrasynyň mugallymy",
                        "name" => $teacher->getThreeNames()
                    );
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

}
