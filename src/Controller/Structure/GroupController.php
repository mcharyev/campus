<?php

namespace App\Controller\Structure;

use App\Entity\Group;
use App\Service\GroupManager;
use App\Entity\Teacher;
use App\Entity\StudyProgram;
use App\Entity\EnrolledStudent;
use App\Entity\ProgramCourse;
use App\Entity\Department;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends AbstractController {

    private $manager;

    function __construct(GroupManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @Route("/faculty/group", name="faculty_group")
     * @Route("/faculty/group/search/{searchField?}/{searchValue?}", name="faculty_group_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        //$repository = $this->getDoctrine()->getRepository(Group::class);
        //$group = $repository->find(3);

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('group/index.html.twig', [
                    'controller_name' => 'GroupController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'sample_text' => '',
        ]);
    }

    /**
     * @Route("/faculty/group/list", name="faculty_group_list")
     * @Route("/faculty/group/list/{offset?0}/{pageSize?20}/{sorting?group.id}/{searchField?}/{searchValue?}", name="faculty_group_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'group',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Group::class);
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
     * @Route("/faculty/group/jsonlist/{term?}", name="faculty_group_jsonlist")
     * @Route("/faculty/group/jsonlist/items/{ids?}", name="faculty_group_jsonlist_items")
     */
    public function jsonList(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $result_array = array();
        $letterCode = $request->attributes->get('term');
        $ids = $request->attributes->get('ids');
        try {
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            if (!empty($ids)) {
                $items = explode(",", $ids);
                foreach ($items as $item) {
                    $group = $groupRepository->findOneBy(['systemId' => $item]);
                    if ($group != null) {
                        $result_array[] = array(
                            "id" => $group->getSystemId(),
                            "value" => $group->getLetterCode() . " - " . $group->getSystemId()
                        );
                    }
                }
            } else {
                $groups = $groupRepository->findLetterCode($letterCode);
                foreach ($groups as $group) {
                    $result_array[] = array(
                        "id" => $group['system_id'],
                        "value" => $group['letter_code'] . " - " . $group['system_id']
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

    /**
     * @Route("/faculty/group/create", name="faculty_group_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            $department = $departmentRepository->find(intval($request->request->get('department_id')));
            
            $studyProgramRepository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $studyProgram = $studyProgramRepository->find(intval($request->request->get('study_program_id')));

            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $groupAdvisor = $teacherRepository->find(intval($request->request->get('advisor_id')));

            $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $groupLeader = $studentRepository->find(intval($request->request->get('group_leader_id')));

            //$this->manager->createFromRequest($request, $studyProgram, $groupAdvisor, $groupLeader);
            $group = new Group();
            $group->setSystemId($request->request->get('system_id'))
                    ->setLetterCode($request->request->get('letter_code'))
                    ->setGraduationYear($request->request->get('graduation_year'))
                    ->setStudyProgram($studyProgram)
                    ->setDepartment($department)
                    ->setAdvisor($groupAdvisor)
                    ->setGroupLeader($groupLeader)
                    ->setScheduleName($request->request->get('schedule_name'))
                    ->setStatus($request->request->get('status'));

            $repository = $this->getDoctrine()->getRepository(Group::class);
            $repository->save($group);
            //*/
            $result = $repository->getLastInserted(['table' => 'group']);

            //Return result
            $result_array = [
                'Result' => "OK",
                'Record' => $result
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
     * @Route("/faculty/group/update", name="faculty_group_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            $department = $departmentRepository->find(intval($request->request->get('department_id')));

            $studyProgramRepository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $studyProgram = $studyProgramRepository->find(intval($request->request->get('study_program_id')));

            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $groupAdvisor = $teacherRepository->find(intval($request->request->get('advisor_id')));

            $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $groupLeader = $studentRepository->find(intval($request->request->get('group_leader_id')));

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Group::class);
            $group = $repository->find($id);

            //$updated_group = $this->manager->updateFromRequest($request, $group, $studyProgram, $groupAdvisor, $groupLeader);
            $group->setSystemId($request->request->get('system_id'))
                    ->setLetterCode($request->request->get('letter_code'))
                    ->setGraduationYear($request->request->get('graduation_year'))
                    ->setDepartment($department)
                    ->setStudyProgram($studyProgram)
                    ->setAdvisor($groupAdvisor)
                    ->setGroupLeader($groupLeader)
                    ->setScheduleName($request->request->get('schedule_name'))
                    ->setStatus($request->request->get('status'));

            $repository->update($group);

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
     * @Route("/faculty/group/delete", name="faculty_group_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Group::class);
            $group = $repository->find($id);
            $repository->remove($group);

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
     * @Route("/faculty/group/new", name="faculty_group_new")
     * @Route("/faculty/group/edit/{id?0}", name="faculty_group_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(Group::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $group = $repository->find($id);
        } else {
            $group = new Group();
        }
        $form = $this->createForm(\App\Form\GroupFormType::class, $group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            if ($form->getClickedButton() === $form->get('save')) {
                $group = $form->getData();
                $group->setDepartmentCode($group->getDepartment()->getSystemId());
                $repository->save($group);
            }

            return $this->redirectToRoute('faculty_group');
        }
        return $this->render('group/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/groupcourse/jsonlist/{year?}/{semester?}/{term?}", name="faculty_groupcourse_jsonlist")
     * @Route("/faculty/groupcourse/jsonlistitems/{ids?}", name="faculty_groupcourse_jsonlistitems")
     */
    public function groupCourse(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_TEACHER");
        $result_array = array();
        $letterCode = $request->attributes->get('term');
        $ids = $request->attributes->get('ids');
        $semester = $request->attributes->get('semester');
        $year = $request->attributes->get('year');
        //$odd = intval($semester) % 2;
        //return new Response('ok'.$ids.":".$year.":".$semester);
        try {
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
            $studyProgramRepository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $lldProgram = $studyProgramRepository->find(37);
            if (!empty($ids)) {
                $items = explode(",", $ids);
                foreach ($items as $item) {
                    $pair = explode("-", $item);
                    $group = $groupRepository->findOneBy(['systemId' => $pair[0]]);
                    $programCourse = $programCourseRepository->find($pair[1]);
                    if ($group != null) {
                        $result_array[] = array(
                            "id" => $group->getSystemId() . "-" . $programCourse->getId(),
                            "value" => $group->getLetterCode() . " - " . $programCourse->getNameTurkmen() . " [" . $programCourse->getId() . "] - " . $programCourse->getSemester()
                        );
                    }
                }
            } else {
                //if ($letterCode) {
                //echo "here";
                $groups = $groupRepository->findLetterCode($letterCode);
                foreach ($groups as $group) {
//                    echo $group['system_id'] . "<br>";
                    $groupObject = $groupRepository->findOneBy(['systemId' => $group['system_id']]);
                    $programCourses = $groupObject->getStudyProgram()->getProgramCourses();
                    if ($groupObject->getGraduationYear() == 2026) {
                        $programCourses = $lldProgram->getProgramCourses();
                    }
                    foreach ($programCourses as $programCourse) {
//                        echo $programCourse->getSemester() . "=" . $groupObject->getGroupSemester($year, $semester) . "<br>";
                        if ($groupObject->getGraduationYear() < 2026) {
                            if ($programCourse->getSemester() == $groupObject->getGroupSemester($year, $semester)) {
                                $result_array[] = array(
                                    "id" => $group['system_id'] . "-" . $programCourse->getId(),
                                    "value" => $group['letter_code'] . " - " . $programCourse->getNameTurkmen() . " [" . $programCourse->getId() . "] - " . $programCourse->getSemester()
                                );
                            }
                        } else {
                            $result_array[] = array(
                                "id" => $group['system_id'] . "-" . $programCourse->getId(),
                                "value" => $group['letter_code'] . " - " . $programCourse->getNameTurkmen() . " [" . $programCourse->getId() . "] - " . $programCourse->getSemester()
                            );
                        }
                    }
                }
                //}
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
