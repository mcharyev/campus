<?php

namespace App\Controller\Building;

use App\Entity\CampusLocation;
use App\Service\CampusLocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CampusLocationController extends AbstractController {

    private $manager;

    function __construct(CampusLocationManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @Route("/faculty/campuslocation", name="faculty_campuslocation")
     * @Route("/faculty/campuslocation/search/{searchField?}/{searchValue?}", name="faculty_campuslocation_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('campus_location/index.html.twig', [
                    'controller_name' => 'CampusLocationController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/campuslocation/list", name="faculty_campuslocation_list")
     * @Route("/faculty/campuslocation/list/{offset?0}/{pageSize?20}/{sorting?campus_location.id}/{searchField?}/{searchValue?}", name="faculty_campuslocation_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'campus_location',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(CampusLocation::class);
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
     * @Route("/faculty/campuslocation/create", name="faculty_campuslocation_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $campusLocation = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(CampusLocation::class);
            $repository->save($campusLocation);
            //*/
            $result = $repository->getLastInserted(['table' => 'campus_location']);

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
     * @Route("/faculty/campuslocation/update", name="faculty_campuslocation_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(CampusLocation::class);
            $campusLocation = $repository->find($id);

            $updatedCampusLocation = $this->manager->updateFromRequest($request, $campusLocation);

            $repository->update($updatedCampusLocation);

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
     * @Route("/faculty/campuslocation/delete", name="faculty_campuslocation_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(CampusLocation::class);
            $campusLocation = $repository->find($id);
            $repository->remove($campusLocation);

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

}
