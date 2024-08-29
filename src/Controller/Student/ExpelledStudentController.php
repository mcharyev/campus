<?php

namespace App\Controller\Student;

use App\Entity\ExpelledStudent;
use App\Service\ExpelledStudentManager;
use App\Entity\EnrolledStudent;
use App\Entity\StudentAbsence;
use App\Entity\Group;
use App\Entity\Teacher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ExpelledStudentFormType;
use App\Service\SystemInfoManager;
use App\Service\PersonalInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class ExpelledStudentController extends AbstractController {

    private $manager;
    private $systemInfoManager;
    private $systemEventManager;

    function __construct(ExpelledStudentManager $manager, SystemInfoManager $systemInfoManager, SystemEventManager $systemEventManager) {
        $this->manager = $manager;
        $this->systemInfoManager = $systemInfoManager;
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/faculty/expelledstudent", name="faculty_expelledstudent")
     * @Route("/faculty/expelledstudent/search/{searchField?}/{searchValue?}", name="faculty_expelledstudent_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupRepository->findAll();
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('expelled_student/index.html.twig', [
                    'controller_name' => 'ExpelledStudentController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'groups' => $groups
        ]);

//        // Creates a simple grid based on your entity (ORM)
//		$source = new Entity('App:ExpelledStudent');
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
     * @Route("/faculty/expelledstudent/list", name="faculty_expelledstudent_list")
     * @Route("/faculty/expelledstudent/list/{offset?0}/{pageSize?20}/{sorting?expelled_student.id}/{searchField?}/{searchValue?}", name="faculty_expelledstudent_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'expelled_student',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
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
     * @Route("/faculty/expelledstudent/create", name="faculty_expelledstudent_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $campusBuilding = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
            $repository->save($campusBuilding);
//*/
            $result = $repository->getLastInserted(['table' => 'expelled_student']);

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
     * @Route("/faculty/expelledstudent/update", name="faculty_expelledstudent_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
            $campusBuilding = $repository->find($id);

            $updatedExpelledStudent = $this->manager->updateFromRequest($request, $campusBuilding);

            $repository->update($updatedExpelledStudent);

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
     * @Route("/faculty/expelledstudent/delete", name="faculty_expelledstudent_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
            $expelledStudent = $repository->find($id);
            $repository->remove($expelledStudent);

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
     * @Route("/faculty/expelledstudent/new", name="faculty_expelledstudent_new")
     * @Route("/faculty/expelledstudent/edit/{id?0}/{source_path?}", name="faculty_expelledstudent_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
        $id = $request->attributes->get('id');
//$sourcePath = $request->attributes->get('source_path');
        if (!empty($id)) {
            $expelledStudent = $repository->find($id);
        } else {
            $expelledStudent = new ExpelledStudent();
        }
        $form = $this->createForm(ExpelledStudentFormType::class, $expelledStudent, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
// $form->getData() holds the submitted values
// but, the original `$task` variable has also been updated
            $expelledStudent = $form->getData();
            $position = $form->get('position')->getData();
            $positions = $form->get('positions')->getData();
            $relatives = $form->get('relatives')->getData();
            $expulsionReason = $form->get('expulsion_reason')->getData();
            $expulsionOrder = $form->get('expulsion_order')->getData();
            $expulsionDate = $form->get('expulsion_date')->getData()->format('Y-m-d');
//$shortExpulsionDate = $expulsionDate->format('Y-m-d');

            $expelledStudent->setAdditionalData([
                'position' => $position,
                'positions' => $positions,
                'relatives' => $relatives,
                'expulsion_reason' => $expulsionReason,
                'expulsion_date' => $expulsionDate,
                'expulsion_order' => $expulsionOrder,
            ]);

            $repository->save($expelledStudent);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_expelledstudent');
        }
        return $this->render('expelled_student/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/expelledstudent/expel/{systemId?0}", name="faculty_expelledstudent_expel")
     */
    public function expelStudent(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_STUDENTEDITOR");
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $expelledStudentRepository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
        $studentAbsenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
        $systemId = $request->attributes->get('systemId');
//$sourcePath = $request->attributes->get('source_path');
        $content = '';
        $title = 'Expelling Student';
        if (!empty($systemId)) {
            $enrolledStudent = $enrolledStudentRepository->findOneBy(['systemId' => $systemId]);

            if ($enrolledStudent) {
                $originalFile = $this->systemInfoManager->getPublicBuildPath() . "/photos/" . $enrolledStudent->getStudentGroup()->getSystemId() . "/" . $enrolledStudent->getSystemId() . ".jpg";
                $newFile = $this->systemInfoManager->getPublicBuildPath() . "/photos/expelled/" . $enrolledStudent->getSystemId() . ".jpg";
                try {
                    rename($originalFile, $newFile);
                    $content .= "<br>Photo file moved OK.<br>";
                    $content .= "From: " . $originalFile;
                    $content .= "To: " . $newFile;
                } catch (\Exception $ex) {
                    $content .= "File cannot be moved: " . $ex->getMessage() . "<br>";
                }


                $expelledStudent = new ExpelledStudent();
                $expelledStudent->setSystemId($enrolledStudent->getSystemId());
                $expelledStudent->setFirstnameEnglish($enrolledStudent->getFirstnameEnglish());
                $expelledStudent->setLastnameEnglish($enrolledStudent->getLastnameEnglish());
                $expelledStudent->setPatronymEnglish($enrolledStudent->getPatronymEnglish());

                $expelledStudent->setFirstnameTurkmen($enrolledStudent->getFirstnameTurkmen());
                $expelledStudent->setLastnameTurkmen($enrolledStudent->getLastnameTurkmen());
                $expelledStudent->setPatronymTurkmen($enrolledStudent->getPatronymTurkmen());
                $expelledStudent->setPreviousLastnameTurkmen($enrolledStudent->getPreviousLastnameTurkmen());
                $expelledStudent->setGender($enrolledStudent->getGender());
                $expelledStudent->setMaritalStatus($enrolledStudent->getMaritalStatus());
                $expelledStudent->setTags($enrolledStudent->getTags());

                $expelledStudent->setData($enrolledStudent->getData());
                if ($enrolledStudent->getDateUpdated()) {
                    $expelledStudent->setDateUpdated($enrolledStudent->getDateUpdated());
                }
                $expelledStudent->setRegion($enrolledStudent->getRegion());
                $expelledStudent->setCountry($enrolledStudent->getCountry());
                $expelledStudent->setNationality($enrolledStudent->getNationality());
                $expelledStudent->setStudentGroup($enrolledStudent->getStudentGroup());
                $expelledStudent->setBirthDate($enrolledStudent->getBirthDate());
                $expelledStudent->setGroupCode($enrolledStudent->getGroupCode());

                $expelledStudent->setMatriculationDate($enrolledStudent->getMatriculationDate());
                $expelledStudent->setGraduationDate($enrolledStudent->getGraduationDate());

                $expelledStudentRepository->save($expelledStudent);

                //remove student absences
                $studentAbsences = $enrolledStudent->getStudentAbsences();
                foreach ($studentAbsences as $studentAbsence) {
                    $studentAbsenceRepository->remove($studentAbsence);
                }

                $enrolledStudent->getStudentGroup()->setGroupLeader(null);

                $enrolledStudentRepository->remove($enrolledStudent);

                $content .= 'OK. Student Expelled: ' . $systemId;
            } else {
                $content .= 'Error. No student with Id: ' . $systemId;
            }
        } else {
            $content .= 'Error. Student Id Blank: ' . $systemId;
        }

        return $this->render('start.html.twig', [
                    'page_title' => $title,
                    'page_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/expelledstudent/info/{id?}", name="faculty_expelledstudent_info")
     */
    public function studentInfo(Request $request, PersonalInfoManager $personalInfoManager) {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_DEAN', 'ROLE_DEPARTMENTHEAD', 'ROLE_STUDENT']);
        $studentRepository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
//$id = 1;
        $access = true;
        $content = '';
        $id = $request->attributes->get('id');
        if (empty($id)) {
            $student = $studentRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($student) {
                $id = $student->getSystemId();
                $access = true;
            }
        }

        if (!empty($id)) {
            $authorizedTeachers = [];

//$id = 1;
            $student = $studentRepository->findOneBy(['systemId' => $id]);
            if ($student) {
                $authorizedTeachers[] = $student->getFaculty()->getDean();
                $authorizedTeachers[] = $student->getFaculty()->getFirstDeputyDean();
                $authorizedTeachers[] = $student->getFaculty()->getSecondDeputyDean();
                $authorizedTeachers[] = $student->getFaculty()->getThirdDeputyDean();
                $authorizedTeachers[] = $student->getDepartment()->getDepartmentHead();
                $authorizedTeachers[] = $student->getStudentGroup()->getAdvisor();
                $access = false;
                if ($this->getUser()->getSystemId() == $student->getSystemId()) {
                    $access = true;
                } else {
                    $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                    $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                    if (in_array($teacher, $authorizedTeachers) || $this->isGranted("ROLE_SPECIALIST")) {
                        $content .= "<a href='/interop/exporter/personalinfo/expelled/" . $student->getSystemId() . "'>Download</a>";
                        $access = true;
                    }
                    if ($this->isGranted("ROLE_SPECIALIST")) {
                        $content .= " | <a href='/faculty/expelledstudent/edit/" . $student->getId() . "'>Edit</a>";
                    }
                    if ($this->isGranted("ROLE_DEAN")) {
                        
                    }
                }
                if ($access) {
                    $info = $student->getData();
                    $content .= $personalInfoManager->viewExpelledStudentInfo($student, $info);
                } else {
                    $content = 'You are not authorized to view this page.';
                }
            }
        }

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(),
                'Expelled student info');
        return $this->render('expelled_student/info.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'pageContent' => $content,
                    'pageTitle' => 'Personal Information'
        ]);
    }

    /**
     * @Route("/faculty/expelledstudent/reinstate/{systemId?0}/{group_id?0}", name="faculty_expelledstudent_reinstate")
     */
    public function reinstateStudent(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $expelledStudentRepository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
        $studentGroupRepository = $this->getDoctrine()->getRepository(Group::class);
        $systemId = $request->attributes->get('systemId');
        $groupSystemId = $request->attributes->get('group_id');
//$sourcePath = $request->attributes->get('source_path');
        $content = '';
        $title = 'Reinstating Student';
        if (!empty($systemId)) {
            $expelledStudent = $expelledStudentRepository->findOneBy(['systemId' => $systemId]);

            if ($expelledStudent) {
                $studentGroup = $studentGroupRepository->findOneBy(['systemId' => $groupSystemId]);
                if ($studentGroup) {
                    $originalFile = $this->systemInfoManager->getPublicBuildPath() . "/photos/expelled/" . $expelledStudent->getSystemId() . ".jpg";
                    $newFile = $this->systemInfoManager->getPublicBuildPath() . "/photos/" . $studentGroup->getSystemId() . "/" . $expelledStudent->getSystemId() . ".jpg";
                    try {
                        rename($originalFile, $newFile);
                        $content .= "<br>Photo file moved OK.<br>";
                        $content .= "From: " . $originalFile;
                        $content .= "To: " . $newFile;
                    } catch (\Exception $ex) {
                        $content .= "File cannot be moved: " . $ex->getMessage() . "<br>";
                    }


                    $enrolledStudent = new EnrolledStudent();
                    $enrolledStudent->setSystemId($expelledStudent->getSystemId());
                    $enrolledStudent->setFirstnameEnglish($expelledStudent->getFirstnameEnglish());
                    $enrolledStudent->setLastnameEnglish($expelledStudent->getLastnameEnglish());
                    $enrolledStudent->setPatronymEnglish($expelledStudent->getPatronymEnglish());

                    $enrolledStudent->setFirstnameTurkmen($expelledStudent->getFirstnameTurkmen());
                    $enrolledStudent->setLastnameTurkmen($expelledStudent->getLastnameTurkmen());
                    $enrolledStudent->setPatronymTurkmen($expelledStudent->getPatronymTurkmen());
                    $enrolledStudent->setPreviousLastnameTurkmen($expelledStudent->getPreviousLastnameTurkmen());
                    $enrolledStudent->setGender($expelledStudent->getGender());
                    $enrolledStudent->setMaritalStatus($expelledStudent->getMaritalStatus());
                    $enrolledStudent->setTags($expelledStudent->getTags());

                    $enrolledStudent->setData($expelledStudent->getData());
                    if ($expelledStudent->getDateUpdated()) {
                        $enrolledStudent->setDateUpdated($expelledStudent->getDateUpdated());
                    }
                    $enrolledStudent->setRegion($expelledStudent->getRegion());
                    $enrolledStudent->setCountry($expelledStudent->getCountry());
                    $enrolledStudent->setNationality($expelledStudent->getNationality());
                    $enrolledStudent->setStudentGroup($expelledStudent->getStudentGroup());
                    $enrolledStudent->setBirthDate($expelledStudent->getBirthDate());
                    $enrolledStudent->setGroupCode($expelledStudent->getGroupCode());

                    $enrolledStudent->setMatriculationDate($expelledStudent->getMatriculationDate());
                    $enrolledStudent->setGraduationDate($expelledStudent->getGraduationDate());
                    $enrolledStudent->setSubgroup(1);

                    $enrolledStudentRepository->save($enrolledStudent);

                    //$expelledStudentRepository->remove($expelledStudent);

                    $content .= 'OK. Student with system id ' . $systemId . ' was reinstated to student group ' . $studentGroup->getLetterCode() . "<br>";
                } else {
                    $content .= 'Error. No student group found with system id: ' . $groupSystemId . "<br>";
                }
            } else {
                $content .= 'Error. No expelled student with id: ' . $systemId . "<br>";
            }
        } else {
            $content .= 'Error. Expelled student id is blank: ' . $systemId . "<br>";
        }

        return $this->render('start.html.twig', [
                    'page_title' => $title,
                    'page_content' => $content
        ]);
    }

}
