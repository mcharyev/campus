<?php

namespace App\Hr\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Hr\Form\MovementFormType;
use App\Hr\Entity\Movement;
use App\Hr\Entity\Employee;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class MovementController extends AbstractController {

    private $systemEventManager;

    public function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/hr/movement", name="hr_movement")
     * @Route("/hr/movement/search/{searchField?}/{searchValue?}", name="hr_movement_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_MOVEMENT, 0, 'Movement table');
        return $this->render('hr/controller/movement/index.html.twig', [
                    'controller_name' => 'MovementController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/hr/movement/record", name="hr_movement_record")
     */
    public function record(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SECURITY");
        $employeeRepository = $this->getDoctrine()->getRepository(Employee::class);
        $employees = $employeeRepository->findAll();
        $employees_data = "";
        foreach ($employees as $employee) {
            $employees_data .= "employees.push(['" . $employee->getId() . "'," . $employee->getSystemId() . ",'" . $employee->getLastname() . " " . $employee->getFirstname() . "']);\n";
        }

        return $this->render('hr/controller/movement/record.html.twig', [
                    'controller_name' => 'MovementController',
                    'employees_data' => $employees_data,
                    'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/hr/movement/recordmovement", name="hr_movement_recordmovement")
     */
    public function recordMovement(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SECURITY");
        $movement_types = array('GIRIŞ', 'ÇYKYŞ');
        $action = $request->request->get("action");
        $movement_id = $request->request->get("movement_id");
        $movement_employeeid = $request->request->get("movement_employeeid");
        $movement_employeenumber = $request->request->get("movement_employeenumber");
        $movement_type = $request->request->get("movement_type");
        $movement_authorid = $request->request->get("movement_authorid");
        $movement_employeename = $request->request->get("movement_employeename");

        $movementRepository = $this->getDoctrine()->getRepository(Movement::class);
        $movement = new Movement();

        $movement->setEmployeeNumber(intval($movement_employeenumber));
        $movement->setMovementType(intval($movement_type));
        $movement->setMovementDate(new \DateTime());
        $movement->setDateUpdated(new \DateTime());
        $movement->setAuthor($this->getUser());

        $movementRepository->save($movement);
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_MOVEMENT, $movement->getId(), $movement_types[intval($movement_type)] . " " . $movement_employeename);

        $content = $movement_employeenumber . " " . $movement_employeename . " üçin " .
                $movement_types[intval($movement_type)] . " " . date("H:i:s") . " ýazyldy.";

        $response = new Response($content);
        //$response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/hr/movement/list/{offset?}/{pageSize?}/{sorting?}", name="hr_movement_list")
     * @Route("/hr/movement/list/{offset?0}/{pageSize?20}/{sorting?movement.id}/{searchField?}/{searchValue?}", name="hr_movement_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'movement',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Movement::class);
            $recordCount = $repository->getRecordCount($params);
            $results = $repository->getRecords($params);            //Return result
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
     * @Route("/hr/movement/delete", name="hr_movement_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $movement = $entityManager->getRepository(Movement::class)->find($id);
            $entityManager->remove($movement);
            $entityManager->flush();
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
     * @Route("/hr/movement/new", name="hr_movement_new")
     * @Route("/hr/movement/edit/{id?0}", name="hr_movement_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        $repository = $this->getDoctrine()->getRepository(Movement::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $movement = $repository->find($id);
        } else {
            $movement = new Movement();
        }
        $form = $this->createForm(MovementFormType::class, $movement, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $movement = $form->getData();

            $repository->save($movement);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('hr_movement');
        }
        return $this->render('hr/controller/movement/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
