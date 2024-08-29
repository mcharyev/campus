<?php

namespace App\Controller\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Freeday;
use App\Form\FreedayFormType;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class FreedayController extends AbstractController {

    //private $manager;
    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/freeday", name="faculty_freeday")
     * @Route("/faculty/freeday/search/{searchField?}/{searchValue?}", name="faculty_freeday_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('freeday/index.html.twig', [
                    'controller_name' => 'FreedayController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/freeday/list", name="faculty_freeday_list")
     * @Route("/faculty/freeday/list/{offset?0}/{pageSize?20}/{sorting?freeday.id}/{searchField?}/{searchValue?}", name="faculty_freeday_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'freeday',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'exactMatch' => false,
            ];
            if ($request->attributes->get('searchField') == 'date') {
                $params['exactMatch'] = true;
            }
            
            $repository = $this->getDoctrine()->getRepository(Freeday::class);
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
     * @Route("/faculty/freeday/delete", name="faculty_freeday_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Freeday::class);
            $freeday = $repository->find($id);
            //$this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_DELETE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_SCHEDULEITEM, $scheduleItem->getId(), 'Teacher: ' . $scheduleItem->getTeacher()->getFullname());
            $repository->remove($freeday);

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
     * @Route("/faculty/freeday/new", name="faculty_freeday_new")
     * @Route("/faculty/freeday/edit/{id?0}", name="faculty_freeday_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(Freeday::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $freeday = $repository->find($id);
            $editType = 'edit';
        } else {
            $freeday = new Freeday();
            $editType = 'new';
        }
        $form = $this->createForm(FreedayFormType::class, $freeday, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $freeday = $form->getData();
            $repository->save($freeday);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_freeday');
        }
        return $this->render('freeday/form.html.twig', [
                    'form' => $form->createView(),
                    'edit_type' => $editType
        ]);
    }

}
