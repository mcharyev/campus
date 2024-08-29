<?php

namespace App\Controller\Event;

use App\Entity\Competition;
use App\Entity\Department;
use App\Form\CompetitionFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class CompetitionController extends AbstractController {

    /**
     * @Route("/faculty/competition", name="faculty_competition")
     * @Route("/faculty/competition/search/{searchField?}/{searchValue?}", name="faculty_competition_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('competition/index.html.twig', [
                    'controller_name' => 'CompetitionController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/competition/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_competition_list")
     * @Route("/faculty/competition/list/{offset?0}/{pageSize?20}/{sorting?competition.id}/{searchField?}/{searchValue?}", name="faculty_competition_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'competition',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Competition::class);
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
     * @Route("/faculty/competition/delete", name="faculty_competition_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $competition = $entityManager->getRepository(Competition::class)->find($id);
            $entityManager->remove($competition);
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
     * @Route("/faculty/competition/new", name="faculty_competition_new")
     * @Route("/faculty/competition/edit/{id?0}", name="faculty_competition_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(Competition::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $competition = $repository->find($id);
        } else {
            $competition = new Competition();
        }
        $form = $this->createForm(CompetitionFormType::class, $competition, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $competition = $form->getData();

            $repository->save($competition);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_competition');
        }
        return $this->render('competition/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/faculty/competition/fullview/{id?0}", name="faculty_competition_fullview")
     */
    public function fullview(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(Competition::class);
        $id = $request->attributes->get('id');
        $competition = $repository->find($id);

        return $this->render('competition/fullview.html.twig', [
                    'competition' => $competition
        ]);
    }

}
