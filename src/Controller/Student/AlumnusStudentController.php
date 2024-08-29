<?php

namespace App\Controller\Student;

use App\Entity\AlumnusStudent;
use App\Service\AlumnusStudentManager;
use App\Entity\EnrolledStudent;
use App\Entity\Region;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\StudentAbsence;
use App\Entity\DisciplineAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AlumnusStudentFormType;
use App\Service\PersonalInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Repository\SettingRepository;

class AlumnusStudentController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $settingsRepository;

    function __construct(AlumnusStudentManager $manager, SystemEventManager $systemEventManager, SettingRepository $settingRepository) {
        $this->manager = $manager;
        $this->systemEventManager = $systemEventManager;
        $this->settingsRepository = $settingRepository;
    }

    /**
     * @Route("/faculty/alumnusstudent", name="faculty_alumnusstudent")
     * @Route("/faculty/alumnusstudent/search/{searchField?}/{searchValue?}", name="faculty_alumnusstudent_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupRepository->findAll();
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('alumnus_student/index.html.twig', [
                    'controller_name' => 'AlumnusStudentController',
                    'sorting' => $request->attributes->get('sorting'),
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'groups' => $groups
        ]);
    }

    /**
     * @Route("/faculty/alumnusstudent/list", name="faculty_alumnusstudent_list")
     * @Route("/faculty/alumnusstudent/list/{offset?0}/{pageSize?20}/{sorting?alumnus_student.id}/{searchField?}/{searchValue?}", name="faculty_alumnusstudent_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'alumnus_student',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
            $recordCount = $repository->getRecordCount($params);
            $results = $repository->getRecords($params);
            $results2 = [];
            foreach ($results as $result) {
                $data = json_decode($result['data']);
                $new_array = [
                    'employment_place' => '',
                    'employment_position' => '',
                ];

                if (!empty($data->{'employment_place'})) {
                    $new_array = [
                        'employment_place' => json_decode($data->{'employment_place'}),
                        'employment_position' => json_decode($data->{'employment_position'}),
                    ];
                }
                $results2[] = $result + $new_array;
            }

            $result_array = [
                'Result' => "OK",
                'TotalRecordCount' => $recordCount,
                'Records' => $results2
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
     * @Route("/faculty/alumnusstudent/create", name="faculty_alumnusstudent_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $campusBuilding = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
            $repository->save($campusBuilding);
            //*/
            $result = $repository->getLastInserted(['table' => 'alumnus_student']);

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
     * @Route("/faculty/alumnusstudent/update", name="faculty_alumnusstudent_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $region_id = $request->request->get('region');
            $employment_place = $request->request->get('employment_place');
            $employment_position = $request->request->get('employment_position');

            $repository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
            $alumnusStudent = $repository->find($id);
            $updatedAlumnusStudent = $this->manager->updateFromRequest($request, $alumnusStudent);

            $regionRepository = $this->getDoctrine()->getRepository(Region::class);
            $region = $regionRepository->find($region_id);
            $updatedAlumnusStudent->setRegion($region);

            $updatedAlumnusStudent->setDataField('employment_position', $employment_position);
            $updatedAlumnusStudent->setDataField('employment_place', $employment_place);

            $repository->update($updatedAlumnusStudent);
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
     * @Route("/faculty/alumnusstudent/delete", name="faculty_alumnusstudent_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
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
     * @Route("/faculty/alumnusstudent/new", name="faculty_alumnusstudent_new")
     * @Route("/faculty/alumnusstudent/edit/{id?0}/{source_path?}", name="faculty_alumnusstudent_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
        $id = $request->attributes->get('id');
        //$sourcePath = $request->attributes->get('source_path');
        if (!empty($id)) {
            $alumnusStudent = $repository->find($id);
        } else {
            $alumnusStudent = new AlumnusStudent();
        }
        $form = $this->createForm(AlumnusStudentFormType::class, $alumnusStudent, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $alumnusStudent = $form->getData();
//            $diploma_registration_number = $form->get('diploma_registration_number')->getData();
//            $diploma_type = $form->get('diploma_type')->getData();
//            $diploma_number = $form->get('diplomat_number')->getData();
//            $diploma_order_date = $form->get('diploma_order_date')->getData();
//            $diploma_receive_date = $form->get('diploma_receive_date')->getData();
//            $diploma_note = $form->get('position')->getData();
//            $internship_place = $form->get('position')->getData();
//            $employment_place = $form->get('position')->getData();
//            $employment_position = $form->get('position')->getData();
//            $employment_note = $form->get('position')->getData();
//
//            $position = $form->get('position')->getData();
//            $positions = $form->get('positions')->getData();
//            $relatives = $form->get('relatives')->getData();
            $fields = [
                'position',
                'positions',
                'relatives',
                'diploma_registration_number',
                'diploma_type',
                'diploma_number',
                'diploma_order_date',
                'diploma_receive_date',
                'diploma_note',
                'diploma_chairman',
                'internship_place',
                'employment_place',
                'employment_position',
                'employment_note'
            ];

            foreach ($fields as $field) {
                //$newData[] = array($field => $form->get($field)->getData());
                $alumnusStudent->setDataField($field, $form->get($field)->getData());
            }

//            $fieldValues = [];
//            foreach ($fields as $field) {
//                $fieldValues[$field] = $relatives = $form->get($field)->getData();
//            }
//            $alumnusStudent->setAdditionalData($fieldValues);

            $repository->save($alumnusStudent);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_alumnusstudent');
        }
        return $this->render('alumnus_student/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/alumnusstudent/graduate/{systemId?0}", name="faculty_alumnusstudent_graduate")
     */
    public function graduateStudent(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_STUDENTEDITOR");
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $alumnusStudentRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
        $studentAbsenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
        $disciplineActionRepository = $this->getDoctrine()->getRepository(DisciplineAction::class);
        $systemId = $request->attributes->get('systemId');
        //$sourcePath = $request->attributes->get('source_path');
        $title = '';
        if (!empty($systemId)) {
            $enrolledStudent = $enrolledStudentRepository->findOneBy(['systemId' => $systemId]);
            $graduationMode = $this->settingsRepository->findOneBy(['name' => 'graduation_mode'])->getValue();
            if ($graduationMode == '0') {
                $errors = 0;
                $title .= '<br>Graduation mode is CLOSED. Go to System - Settings and change graduation_mode to 1.';
            } else {
                if ($enrolledStudent) {
                    $errors = 0;
                    $studentAbsences = $enrolledStudent->getStudentAbsences();
                    $disciplineActions = $enrolledStudent->getDisciplineActions();
                    if (sizeof($studentAbsences) > 0) {
                        $errors = 0;
                        $title .= '<br>Student cannot be graduated because there are Student absence records which need to be deleted first.';
                        foreach ($studentAbsences as $absence) {
                            $studentAbsenceRepository->remove($absence);
                        }
                    }

                    if (sizeof($disciplineActions) > 0) {
                        $errors = 0;
                        $title .= '<br>Student cannot be graduated because there are Discipline action records which need to be deleted first.';
                        foreach ($disciplineActions as $disciplineAction) {
                            $disciplineActionRepository->remove($disciplineAction);
                        }
                    }

                    if ($errors == 0) {
                        $alumnusStudent = new AlumnusStudent();
                        $alumnusStudent->setSystemId($enrolledStudent->getSystemId());
                        $alumnusStudent->setFirstnameEnglish($enrolledStudent->getFirstnameEnglish());
                        $alumnusStudent->setLastnameEnglish($enrolledStudent->getLastnameEnglish());
                        $alumnusStudent->setPatronymEnglish($enrolledStudent->getPatronymEnglish());

                        $alumnusStudent->setFirstnameTurkmen($enrolledStudent->getFirstnameTurkmen());
                        $alumnusStudent->setLastnameTurkmen($enrolledStudent->getLastnameTurkmen());
                        $alumnusStudent->setPatronymTurkmen($enrolledStudent->getPatronymTurkmen());
                        $alumnusStudent->setPreviousLastnameTurkmen($enrolledStudent->getPreviousLastnameTurkmen());
                        $alumnusStudent->setGender($enrolledStudent->getGender());
                        $alumnusStudent->setMaritalStatus($enrolledStudent->getMaritalStatus());
                        $alumnusStudent->setTags($enrolledStudent->getTags());

                        $alumnusStudent->setData($enrolledStudent->getData());
                        if ($enrolledStudent->getDateUpdated()) {
                            $alumnusStudent->setDateUpdated($enrolledStudent->getDateUpdated());
                        }
                        $alumnusStudent->setRegion($enrolledStudent->getRegion());
                        $alumnusStudent->setCountry($enrolledStudent->getCountry());
                        $alumnusStudent->setNationality($enrolledStudent->getNationality());
                        $alumnusStudent->setStudentGroup($enrolledStudent->getStudentGroup());
                        $alumnusStudent->setBirthDate($enrolledStudent->getBirthDate());
                        $alumnusStudent->setGroupCode($enrolledStudent->getGroupCode());

                        $alumnusStudent->setMatriculationDate($enrolledStudent->getMatriculationDate());
                        $alumnusStudent->setGraduationDate($enrolledStudent->getGraduationDate());

                        $alumnusStudentRepository->save($alumnusStudent);

                        $enrolledStudent->getStudentGroup()->setGroupLeader(null);

                        $enrolledStudentRepository->remove($enrolledStudent);

                        $title = 'OK. Student Graduated: ' . $systemId;
                    }
                } else {
                    $title = 'Error. No student with Id: ' . $systemId;
                }
            }
        } else {
            $title = 'Error. Student Id Blank: ' . $systemId;
        }

        return $this->render('start.html.twig', [
                    'page_title' => 'Graduation Action',
                    'page_content' => $title
        ]);
    }

    /**
     * @Route("/faculty/alumnusstudent/info/{id?}", name="faculty_alumnusstudent_info")
     */
    public function studentInfo(Request $request, PersonalInfoManager $personalInfoManager) {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_DEAN', 'ROLE_DEPARTMENTHEAD', 'ROLE_STUDENT']);
        $studentRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
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
                        $content .= "<a href='/interop/exporter/personalinfo/alumnus/" . $student->getSystemId() . "'>Download</a>";
                        $access = true;
                    }
                    if ($this->isGranted("ROLE_SPECIALIST")) {
                        $content .= " | <a href='/faculty/alumnusstudent/edit/" . $student->getId() . "'>Edit</a>";
                    }
                    if ($this->isGranted("ROLE_DEAN")) {
                        
                    }
                }
                if ($access) {
                    $info = $student->getData();
                    $content .= $personalInfoManager->viewAlumnusStudentInfo($student, $info);
                } else {
                    $content = 'You are not authorized to view this page.';
                }
            }
        }

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(),
                'Alumnus personal info');
        return $this->render('alumnus_student/info.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'pageContent' => $content,
                    'pageTitle' => 'Personal Information'
        ]);
    }

}
