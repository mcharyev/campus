<?php

namespace App\Controller\Building;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\HostelRoom;
use App\Service\HostelRoomManager;
use App\Form\HostelRoomFormType;
use App\Entity\Hostel;
use App\Entity\Teacher;

class HostelRoomController extends AbstractController
{
    private $manager;
    function __construct(HostelRoomManager $manager) {
        $this->manager = $manager;
    }
    
    /**
     * @Route("/faculty/hostelroom", name="faculty_hostelroom")
     * @Route("/faculty/hostelroom/search/{searchField?}/{searchValue?}", name="faculty_hostelroom_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('hostel_room/index.html.twig', [
                    'controller_name' => 'HostelRoomController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/hostelroom/list", name="faculty_hostelroom_list")
     * @Route("/faculty/hostelroom/list/{offset?0}/{pageSize?20}/{sorting?hostelroom.id}/{searchField?}/{searchValue?}", name="faculty_hostelroom_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'hostel_room',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(HostelRoom::class);
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
     * @Route("/faculty/hostelroom/create", name="faculty_hostelroom_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $hostel_id = $request->request->get('hostel_id');
            $instructor_id = $request->request->get('instructor_id');
            $hostel = $this->getDoctrine()->getRepository(Hostel::class)->find($hostel_id);
            $instructor = $this->getDoctrine()->getRepository(Teacher::class)->find($instructor_id);
            
            $hostelroom = $this->manager->createFromRequest($request, $instructor, $hostel);
            $repository = $this->getDoctrine()->getRepository(HostelRoom::class);
            $repository->save($hostelroom);
            //*/
            $result = $repository->getLastInserted(['table' => 'hostel_room']);

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
     * @Route("/faculty/hostelroom/update", name="faculty_hostelroom_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $hostel_id = $request->request->get('hostel_id');
            $instructor_id = $request->request->get('instructor_id');
            $hostel = $this->getDoctrine()->getRepository(Hostel::class)->find($hostel_id);
            $instructor = $this->getDoctrine()->getRepository(Teacher::class)->find($instructor_id);
            
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(HostelRoom::class);
            $hostelroom = $repository->find($id);
            
            $updatedHostelroom = $this->manager->updateFromRequest($request, $hostelroom, $instructor, $hostel);
            
            $repository->update($updatedHostelroom);

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
     * @Route("/faculty/hostelroom/delete", name="faculty_hostelroom_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(HostelRoom::class);
            $hostelroom = $repository->find($id);
            $repository->remove($hostelroom);

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
     * @Route("/faculty/hostelroom/new", name="faculty_hostelroom_new")
     * @Route("/faculty/hostelroom/edit/{id?0}", name="faculty_hostelroom_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(HostelRoom::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $hostelroom = $repository->find($id);
        } else {
            $hostelroom = new HostelRoom();
        }
        $form = $this->createForm(HostelRoomFormType::class, $hostelroom);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $hostelroom = $form->getData();

            $repository->save($hostelroom);

            return $this->redirectToRoute('faculty_hostelroom');
        }
        return $this->render('hostel_room/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }
}
