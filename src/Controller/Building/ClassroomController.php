<?php

namespace App\Controller\Building;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


use App\Entity\Classroom;
use App\Service\ClassroomManager;
use App\Form\ClassroomFormType;

class ClassroomController extends AbstractController
{
    private $manager;
    function __construct(ClassroomManager $manager) {
        $this->manager = $manager;
    }
    
    /**
     * @Route("/faculty/classroom", name="faculty_classroom")
     * @Route("/faculty/classroom/search/{searchField?}/{searchValue?}", name="faculty_classroom_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('classroom/index.html.twig', [
                    'controller_name' => 'ClassroomController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/classroom/list", name="faculty_classroom_list")
     * @Route("/faculty/classroom/list/{offset?0}/{pageSize?20}/{sorting?classroom.id}/{searchField?}/{searchValue?}", name="faculty_classroom_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'classroom',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Classroom::class);
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
     * @Route("/faculty/classroom/create", name="faculty_classroom_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $classroom = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(Classroom::class);
            $repository->save($classroom);
            //*/
            $result = $repository->getLastInserted(['table' => 'classroom']);

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
     * @Route("/faculty/classroom/update", name="faculty_classroom_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Classroom::class);
            $classroom = $repository->find($id);
            
            $updatedClassroom = $this->manager->updateFromRequest($request, $classroom);
            
            $repository->update($updatedClassroom);

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
     * @Route("/faculty/classroom/delete", name="faculty_classroom_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Classroom::class);
            $classroom = $repository->find($id);
            $repository->remove($classroom);

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
     * @Route("/faculty/classroom/new", name="faculty_classroom_new")
     * @Route("/faculty/classroom/edit/{id?0}", name="faculty_classroom_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(Classroom::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $classroom = $repository->find($id);
        } else {
            $classroom = new Classroom();
        }
        $form = $this->createForm(ClassroomFormType::class, $classroom);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $classroom = $form->getData();

            $repository->save($classroom);

            return $this->redirectToRoute('faculty_classroom');
        }
        return $this->render('classroom/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }
}
