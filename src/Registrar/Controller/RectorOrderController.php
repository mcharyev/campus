<?php

namespace App\Registrar\Controller;

use App\Registrar\Entity\RectorOrder;
use App\Registrar\Form\RectorOrderFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class RectorOrderController extends AbstractController {

    /**
     * @Route("/registrar/rectororder", name="registrar_rectororder")
     * @Route("/registrar/rectororder/search/{searchField?}/{searchValue?}", name="registrar_rectororder_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('registrar/rectororder/index.html.twig', [
                    'controller_name' => 'RectorOrderController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/registrar/rectororder/list/{offset?}/{pageSize?}/{sorting?}", name="registrar_rectororder_list")
     * @Route("/registrar/rectororder/list/{offset?0}/{pageSize?20}/{sorting?rectororder.id}/{searchField?}/{searchValue?}", name="registrar_rectororder_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'rector_order',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(RectorOrder::class);
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
     * @Route("/registrar/rectororder/delete", name="registrar_rectororder_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $rectorOrder = $entityManager->getRepository(RectorOrder::class)->find($id);
            $entityManager->remove($rectorOrder);
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
     * @Route("/registrar/rectororder/new", name="registrar_rectororder_new")
     * @Route("/registrar/rectororder/edit/{id?0}", name="registrar_rectororder_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(RectorOrder::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $rectorOrder = $repository->find($id);
        } else {
            $rectorOrder = new RectorOrder();
        }
        $form = $this->createForm(RectorOrderFormType::class, $rectorOrder, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $rectorOrder = $form->getData();
            $rectorOrder->setUser($this->getUser());
            $rectorOrder->setDateUpdated(new \DateTime());

            $repository->save($rectorOrder);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('registrar_rectororder');
        }
        return $this->render('registrar/rectororder/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
