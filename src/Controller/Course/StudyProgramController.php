<?php

namespace App\Controller\Course;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\StudyProgram;
use App\Entity\ProgramCourse;
use App\Service\StudyProgramManager;
use App\Form\StudyProgramFormType;
use App\Entity\ProgramModule;
use App\Repository\SettingRepository;

class StudyProgramController extends AbstractController {

    private $manager;
    private $settingsRepository;

    function __construct(StudyProgramManager $manager, SettingRepository $settingRepository) {
        $this->manager = $manager;
        $this->settingsRepository = $settingRepository;
    }

    /**
     * @Route("/faculty/studyprogram", name="faculty_studyprogram")
     * @Route("/faculty/studyprogram/search/{searchField?}/{searchValue?}", name="faculty_studyprogram_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('study_program/index.html.twig', [
                    'controller_name' => 'StudyProgramController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/studyprogram/list", name="faculty_studyprogram_list")
     * @Route("/faculty/studyprogram/list/{offset?0}/{pageSize?20}/{sorting?study_program.id}/{searchField?}/{searchValue?}", name="faculty_studyprogram_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'study_program',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(StudyProgram::class);
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
     * @Route("/faculty/studyprogram/create", name="faculty_studyprogram_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $studyProgram = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $repository->save($studyProgram);
            //*/
            $result = $repository->getLastInserted(['table' => 'study_program']);

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
     * @Route("/faculty/studyprogram/update", name="faculty_studyprogram_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $studyProgram = $repository->find($id);

            $updatedStudyProgram = $this->manager->updateFromRequest($request, $studyProgram);

            $repository->update($updatedStudyProgram);

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
     * @Route("/faculty/studyprogram/delete", name="faculty_studyprogram_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $studyProgram = $repository->find($id);
            $repository->remove($studyProgram);

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
     * @Route("/faculty/studyprogram/new", name="faculty_studyprogram_new")
     * @Route("/faculty/studyprogram/edit/{id?0}", name="faculty_studyprogram_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(StudyProgram::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $studyProgram = $repository->find($id);
        } else {
            $studyProgram = new StudyProgram();
        }
        $form = $this->createForm(StudyProgramFormType::class, $studyProgram);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $studyProgram = $form->getData();
            $data = $studyProgram->getData();
            $newData = array();
            foreach ($data as $key => $value) {
                $newData[$key] = $form->get($key)->getData();
            }

            $studyProgram->setAdditionalData($newData);

            $repository->save($studyProgram);

            return $this->redirectToRoute('faculty_studyprogram');
        }
        return $this->render('study_program/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/studyprogram/fullview/{id?0}", name="faculty_studyprogram_fullview")
     */
    public function fullview(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(StudyProgram::class);
        $id = $request->attributes->get('id');
        $studyProgram = $repository->find($id);

        $repositoryModule = $this->getDoctrine()->getRepository(ProgramModule::class);
        $programModules = $repositoryModule->findAll();
        $modules = array();
        foreach ($programModules as $programModule) {
            $modules[] = [
                'name' => $programModule->getNameEnglish(),
                'id' => $programModule->getId(),
                'counts' => $studyProgram->getModuleSums(intval($programModule->getSystemId()))
            ];
        }

//        $modules = [
//            ['name' => 'I.1 General humanities and socio-economic courses', 'id' => 1, 'counts' => $studyProgram->getModuleSums(1)],
//            ['name' => 'I.2 General mathematical and exact sciences', 'id' => 2, 'counts' => $studyProgram->getModuleSums(2)],
//            ['name' => 'I.3 General courses for major', 'id' => 3, 'counts' => $studyProgram->getModuleSums(3)],
//            ['name' => 'I.4 Special courses for major', 'id' => 4, 'counts' => $studyProgram->getModuleSums(4)],
//            ['name' => 'II. Practices', 'id' => 5, 'counts' => $studyProgram->getModuleSums(5)],
//            ['name' => 'III. State exams', 'id' => 6, 'counts' => $studyProgram->getModuleSums(6)]
//        ];

        return $this->render('study_program/fullview.html.twig', [
                    'studyprogram' => $studyProgram,
                    'modules' => $modules
        ]);
    }

    /**
     * @Route("/faculty/studyprogram/duplicate/{id?0}", name="faculty_studyprogram_duplicate")
     */
    public function duplicate(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $approval_year = $this->settingsRepository->findOneBy(['name' => 'first_semester_year'])->getValue();
            $id = $request->attributes->get('id');
            $repository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
            $studyProgram = $repository->find($id);
            if ($studyProgram) {
                $courses = $studyProgram->getProgramCourses();
//$programCourseRepository->findBy(["studyProgram" => $studyProgram]);
                $newStudyProgram = new StudyProgram();
                $newStudyProgram->setSystemId($studyProgram->getSystemId());
                $newStudyProgram->setLetterCode($studyProgram->getLetterCode());
                $newStudyProgram->setNameEnglish($studyProgram->getNameEnglish() . " COPY");
                $newStudyProgram->setNameTurkmen($studyProgram->getNameTurkmen());
                $newStudyProgram->setProgramLevel($studyProgram->getProgramLevel());
                $newStudyProgram->setDepartment($studyProgram->getDepartment());
                $newStudyProgram->setStatus($studyProgram->getStatus());
                $newStudyProgram->setApprovalYear($approval_year);
                if ($studyProgram->getDepartmentCode()) {
                    $newStudyProgram->setDepartmentCode($studyProgram->getDepartmentCode());
                }
                $newStudyProgram->setData($studyProgram->getData());
                $newStudyProgram->setDateUpdated(new \DateTime());

                $repository->save($newStudyProgram);

                $createdStudyProgram = $repository->getLastInserted(['table' => 'study_program'])[0];
                $courseCount = 0;

                foreach ($courses as $course) {
                    $newCourse = new ProgramCourse();
                    $newCourse->setSystemId($course->getSystemId());
                    $newCourse->setLetterCode($course->getLetterCode());
                    $newCourse->setNameEnglish($course->getNameEnglish());
                    $newCourse->setNameTurkmen($course->getNameTurkmen());
                    $newCourse->setSemester($course->getSemester());
                    $newCourse->setStudyProgram($newStudyProgram);
                    $newCourse->setDepartment($course->getDepartment());
                    $newCourse->setStatus($course->getStatus());
                    $newCourse->setDateUpdated(new \DateTime());
                    $newCourse->setData($course->getData());
                    $newCourse->setStatus($course->getStatus());
                    $newCourse->setLanguage($course->getLanguage());
                    $newCourse->setModule($course->getModule());
                    $newCourse->setType($course->getType());
                    $newCourse->setViewOrder($course->getViewOrder());
                    if ($course->getDepartmentCode()) {
                        $newCourse->setDepartmentCode($course->getDepartmentCode());
                    }
                    $newCourse->setCreditType($course->getCreditType());
                    $programCourseRepository->save($newCourse);
                    $courseCount++;
                }

                //$repository->save($createdStudyProgram);

                $result_array = [
                    'Result' => "OK",
                    'Message' => 'Item duplicated. Courses:' . sizeof($courses) . ' Added:' . $courseCount,
                    'Record' => $createdStudyProgram,
                ];
            } else {
                $result_array = [
                    'Result' => "ERROR",
                    'Message' => "Item with ID=" . $id . " is not found"
                ];
            }
            //Return result
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

}
