<?php

namespace App\Controller\Building;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\CampusBuilding;
use App\Service\CampusBuildingManager;
use App\Form\CampusBuildingFormType;

class CampusBuildingController extends AbstractController {

    private $manager;

    function __construct(CampusBuildingManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @Route("/faculty/campusbuilding", name="faculty_campusbuilding")
     * @Route("/faculty/campusbuilding/search/{searchField?}/{searchValue?}", name="faculty_campusbuilding_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('campus_building/index.html.twig', [
                    'controller_name' => 'CampusBuildingController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/campusbuilding/list", name="faculty_campusbuilding_list")
     * @Route("/faculty/campusbuilding/list/{offset?0}/{pageSize?20}/{sorting?campus_building.id}/{searchField?}/{searchValue?}", name="faculty_campusbuilding_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'campus_building',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(CampusBuilding::class);
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
     * @Route("/faculty/campusbuilding/create", name="faculty_campusbuilding_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $campusBuilding = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(CampusBuilding::class);
            $repository->save($campusBuilding);
            //*/
            $result = $repository->getLastInserted(['table' => 'campus_building']);

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
     * @Route("/faculty/campusbuilding/update", name="faculty_campusbuilding_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {

            $id = $request->request->get('id');
            echo "ID is here:" . $id;
            $repository = $this->getDoctrine()->getRepository(CampusBuilding::class);
            $campusBuilding = $repository->find($id);

            $updatedCampusBuilding = $this->manager->updateFromRequest($request, $campusBuilding);

            $repository->update($updatedCampusBuilding);

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
     * @Route("/faculty/campusbuilding/delete", name="faculty_campusbuilding_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(CampusBuilding::class);
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
     * @Route("/faculty/campusbuilding/new", name="faculty_campusbuilding_new")
     * @Route("/faculty/campusbuilding/edit/{id?0}", name="faculty_campusbuilding_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(CampusBuilding::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $campusBuilding = $repository->find($id);
        } else {
            $campusBuilding = new CampusBuilding();
        }
        $form = $this->createForm(CampusBuildingFormType::class, $campusBuilding);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $campusBuilding = $form->getData();

            $repository->save($campusBuilding);

            return $this->redirectToRoute('faculty_campusbuilding');
        }
        return $this->render('campus_building/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
