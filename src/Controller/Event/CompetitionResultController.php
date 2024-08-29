<?php

namespace App\Controller\Event;

use App\Entity\CompetitionResult;
use App\Entity\Competition;
use App\Form\CompetitionResultFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class CompetitionResultController extends AbstractController {

    /**
     * @Route("/faculty/competitionresult/", name="faculty_competitionresult")
     * @Route("/faculty/competitionresult/search/{searchField?}/{searchValue?}", name="faculty_competitionresult_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('competition_result/index.html.twig', [
                    'controller_name' => 'CompetitionResultController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/competitionresult/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_competitionresult_list")
     * @Route("/faculty/competitionresult/list/{offset?0}/{pageSize?20}/{sorting?competition.id}/{searchField?}/{searchValue?}", name="faculty_competitionresult_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'competition_result',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(CompetitionResult::class);
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
     * @Route("/faculty/competitionresult/delete", name="faculty_competitionresult_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $competitionResult = $entityManager->getRepository(CompetitionResult::class)->find($id);
            $entityManager->remove($competitionResult);
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
     * @Route("/faculty/competitionresult/new", name="faculty_competitionresult_new")
     * @Route("/faculty/competitionresult/edit/{id?0}", name="faculty_competitionresult_edit")
     * @Route("/faculty/competitionresult/editincompetition/{id?0}/{competition?0}", name="faculty_competitionresult_editincompetition")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(CompetitionResult::class);
        $id = $request->attributes->get('id');
        $competitionId = $request->attributes->get('competition');
        if (!empty($id)) {
            $competitionResult = $repository->find($id);
        } else {
            $competitionResult = new CompetitionResult();
        }
        if (!empty($competitionId)) {
            $competition = $this->getDoctrine()->getRepository(Competition::class)->find($competitionId);
        } else {
            $competition = null;
        }
        $form = $this->createForm(CompetitionResultFormType::class, $competitionResult, [
            'competition' => $competition,
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $competitionResult = $form->getData();
            $fields = [
                'systemid', 'year', 'major', 'livingplace', 'note', 'advisor', 'advisorposition'
            ];
            foreach ($fields as $field) {
                $competitionResult->setDataField($field, $form->get($field)->getData());
            }

            $repository->save($competitionResult);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_competitionresult');
        }
        return $this->render('competition_result/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
