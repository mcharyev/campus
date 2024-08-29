<?php

namespace App\Controller\Student;

use App\Entity\StudentAbsence;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Form\StudentAbsenceFormType;

class StudentAbsenceController extends AbstractController {

    //private $manager;

    function __construct() {
        
    }

    /**
     * @Route("/faculty/studentabsence", name="faculty_studentabsence")
     * @Route("/faculty/studentabsence/search/{searchField?}/{searchValue?}", name="faculty_studentabsence_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('student_absence/index.html.twig', [
                    'controller_name' => 'StudentAbsenceController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/studentabsence/list", name="faculty_studentabsence_list")
     * @Route("/faculty/studentabsence/list/{offset?0}/{pageSize?20}/{sorting?student_absence.id}/{searchField?}/{searchValue?}", name="faculty_studentabsence_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'student_absence',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(StudentAbsence::class);
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
     * @Route("/faculty/studentabsence/delete", name="faculty_studentabsence_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(StudentAbsence::class);
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
     * @Route("/faculty/studentabsence/new", name="faculty_studentabsence_new")
     * @Route("/faculty/studentabsence/edit/{id?0}", name="faculty_studentabsence_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(StudentAbsence::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $studentAbsence = $repository->find($id);
        } else {
            $studentAbsence = new StudentAbsence();
        }
        $form = $this->createForm(StudentAbsenceFormType::class, $studentAbsence);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $studentAbsence = $form->getData();

            $repository->save($studentAbsence);

            return $this->redirectToRoute('faculty_studentabsence');
        }
        return $this->render('student_absence/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
