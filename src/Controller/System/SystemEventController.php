<?php

namespace App\Controller\System;

use App\Entity\SystemEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SystemEventFormType;

class SystemEventController extends AbstractController {

    //private $manager;

    function __construct() {
        
    }

    /**
     * @Route("/system/systemevent", name="system_systemevent")
     * @Route("/system/systemevent/search/{searchField?}/{searchValue?}", name="system_systemevent_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('system_event/index.html.twig', [
                    'controller_name' => 'SystemEventController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/system/systemevent/list", name="system_systemevent_list")
     * @Route("/system/systemevent/list/{offset?0}/{pageSize?20}/{sorting?system_event.id}/{searchField?}/{searchValue?}", name="system_systemevent_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $params = [
                'table' => 'system_event',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(SystemEvent::class);
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
     * @Route("/system/systemevent/delete", name="system_systemevent_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(SystemEvent::class);
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
     * @Route("/system/systemevent/new", name="system_systemevent_new")
     * @Route("/system/systemevent/edit/{id?0}", name="system_systemevent_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $repository = $this->getDoctrine()->getRepository(SystemEvent::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $systemEvent = $repository->find($id);
        } else {
            $systemEvent = new SystemEvent();
        }
        $form = $this->createForm(SystemEventFormType::class, $systemEvent, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $systemEvent = $form->getData();

            $repository->save($systemEvent);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('system_systemevent');
        }
        return $this->render('system_event/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
