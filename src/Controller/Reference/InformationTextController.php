<?php

namespace App\Controller\Reference;

use App\Entity\InformationText;
use App\Form\InformationTextFormType;
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

class InformationTextController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/information_text", name="information_text")
     * @Route("/information_text/search/{searchField?}/{searchValue?}", name="information_text_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('information_text/index.html.twig', [
                    'controller_name' => 'InformationTextController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/information_text/list/{offset?}/{pageSize?}/{sorting?}", name="information_text_list")
     * @Route("/information_text/list/{offset?0}/{pageSize?20}/{sorting?id}/{searchField?}/{searchValue?}", name="information_text_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'information_text',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(InformationText::class);
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
     * @Route("/information_text/delete", name="information_text_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $informationText = $entityManager->getRepository(InformationText::class)->find($id);
            $entityManager->remove($informationText);
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
     * @Route("/information_text/new", name="information_text_new")
     * @Route("/information_text/edit/{id?0}", name="information_text_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(InformationText::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $informationText = $repository->find($id);
        } else {
            $informationText = new InformationText();
        }
        $form = $this->createForm(InformationTextFormType::class, $informationText, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $informationText = $form->getData();
            $informationText->setDateUpdated(new \DateTime());
            $repository->save($informationText);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('information_text');
        }
        return $this->render('information_text/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/information_text/view/{letterCode?0}", name="information_text_view")
     */
    public function view(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $repository = $this->getDoctrine()->getRepository(InformationText::class);
        $letterCode = $request->attributes->get('letterCode');
        $informationText = $repository->findOneBy(['letterCode' => $letterCode]);
        if ($informationText) {
            //echo $informationText->getTitle();
            return $this->render('information_text/view.html.twig', [
                        'controller_name' => 'InformationTextController',
                        'informationText' => $informationText
            ]);
        } else {
            return new Response('Data not found!');
        }
    }

}
