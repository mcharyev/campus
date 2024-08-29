<?php

namespace App\Controller\Administration;

use App\Entity\Setting;
use App\Form\SettingFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SettingController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/setting/index", name="setting_index")
     * @Route("/setting/search/{searchField?}/{searchValue?}", name="setting_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('setting/index.html.twig', [
                    'controller_name' => 'SettingController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/setting/list/{offset?}/{pageSize?}/{sorting?}", name="setting_list")
     * @Route("/setting/list/{offset?0}/{pageSize?20}/{sorting?id}/{searchField?}/{searchValue?}", name="setting_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'setting',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Setting::class);
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
     * @Route("/setting/delete", name="setting_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $setting = $entityManager->getRepository(Setting::class)->find($id);
            $entityManager->remove($setting);
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
     * @Route("/setting/new", name="setting_new")
     * @Route("/setting/edit/{id?0}", name="setting_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $repository = $this->getDoctrine()->getRepository(Setting::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $setting = $repository->find($id);
        } else {
            $setting = new Setting();
        }
        $form = $this->createForm(SettingFormType::class, $setting, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $setting = $form->getData();
            $repository->save($setting);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('setting');
        }
        return $this->render('setting/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/setting/view/{letterCode?0}", name="setting_view")
     */
    public function view(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $repository = $this->getDoctrine()->getRepository(Setting::class);
        $letterCode = $request->attributes->get('letterCode');
        $setting = $repository->findOneBy(['letterCode' => $letterCode]);
        if ($setting) {
            //echo $setting->getTitle();
            return $this->render('setting/view.html.twig', [
                        'controller_name' => 'SettingController',
                        'setting' => $setting
            ]);
        } else {
            return new Response('Data not found!');
        }
    }

}
