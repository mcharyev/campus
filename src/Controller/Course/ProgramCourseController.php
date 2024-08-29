<?php

namespace App\Controller\Course;

use App\Entity\ProgramCourse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Form\ProgramCourseFormType;
use App\Entity\StudyProgram;

class ProgramCourseController extends AbstractController {

    use TargetPathTrait;

    function __construct() {
        
    }

    /**
     * @Route("/faculty/programcourse", name="faculty_programcourse")
     * @Route("/faculty/programcourse/search/{searchField?}/{searchValue?}", name="faculty_programcourse_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('program_course/index.html.twig', [
                    'controller_name' => 'ProgramCourseController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/programcourse/list", name="faculty_programcourse_list")
     * @Route("/faculty/programcourse/list/{offset?0}/{pageSize?20}/{sorting?program_course.id}/{searchField?}/{searchValue?}", name="faculty_programcourse_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'program_course',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(ProgramCourse::class);
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
     * @Route("/faculty/programcourse/delete", name="faculty_programcourse_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(ProgramCourse::class);
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
     * @Route("/faculty/programcourse/new", name="faculty_programcourse_new")
     * @Route("/faculty/programcourse/edit/{id?0}", name="faculty_programcourse_edit")
     * @Route("/faculty/programcourse/editinprogram/{id?0}/{program_id?0}", name="faculty_programcourse_editinprogram")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $id = $request->attributes->get('id');
        $programId = $request->attributes->get('program_id');
        if (!empty($id) && $id != '0') {
            $programCourse = $repository->find($id);
        } else {
            $programCourse = new ProgramCourse();
        }
        if (!empty($programId)) {
            $studyProgramRepository = $this->getDoctrine()->getRepository(StudyProgram::class);
            $studyProgram = $studyProgramRepository->find($programId);
            $sourcePath = '/faculty/studyprogram/fullview/' . $studyProgram->getId();
        } else {
            $sourcePath = '';
            $studyProgram = null;
        }
        $form = $this->createForm(ProgramCourseFormType::class, $programCourse,
                [
                    'source_path' => $sourcePath,
                    'studyProgram' => $studyProgram
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $programCourse = $form->getData();
            $note = $form->get('note')->getData();
            $counts = json_decode("[" . $form->get('counts')->getData() . "]");

            $programCourse->setAdditionalData([
                'note' => $note,
                'counts' => $counts
            ]);
            $programCourse->setDateUpdated(new \DateTime());
            $repository->save($programCourse);
            $sourcePath = $form->get('source_path')->getData();
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_programcourse');
        }
        return $this->render('program_course/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
