<?php

namespace App\Controller\Building;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Hostel;
use App\Service\HostelManager;
use App\Form\HostelFormType;

class HostelController extends AbstractController {

    private $manager;

    function __construct(HostelManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @Route("/faculty/hostel", name="faculty_hostel")
     * @Route("/faculty/hostel/search/{searchField?}/{searchValue?}", name="faculty_hostel_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('hostel/index.html.twig', [
                    'controller_name' => 'HostelController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/hostel/list", name="faculty_hostel_list")
     * @Route("/faculty/hostel/list/{offset?0}/{pageSize?20}/{sorting?hostel.id}/{searchField?}/{searchValue?}", name="faculty_hostel_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'hostel',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Hostel::class);
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
     * @Route("/faculty/hostel/create", name="faculty_hostel_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $campusBuilding = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(Hostel::class);
            $repository->save($campusBuilding);
            //*/
            $result = $repository->getLastInserted(['table' => 'hostel']);

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
     * @Route("/faculty/hostel/update", name="faculty_hostel_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {

            $id = $request->request->get('id');
            echo "ID is here:" . $id;
            $repository = $this->getDoctrine()->getRepository(Hostel::class);
            $campusBuilding = $repository->find($id);

            $updatedHostel = $this->manager->updateFromRequest($request, $campusBuilding);

            $repository->update($updatedHostel);

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
     * @Route("/faculty/hostel/delete", name="faculty_hostel_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Hostel::class);
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
     * @Route("/faculty/hostel/new", name="faculty_hostel_new")
     * @Route("/faculty/hostel/edit/{id?0}", name="faculty_hostel_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(Hostel::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $campusBuilding = $repository->find($id);
        } else {
            $campusBuilding = new Hostel();
        }
        $form = $this->createForm(HostelFormType::class, $campusBuilding);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $campusBuilding = $form->getData();

            $repository->save($campusBuilding);

            return $this->redirectToRoute('faculty_hostel');
        }
        return $this->render('hostel/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}