<?php

namespace App\Controller\Student;

use App\Entity\DisciplineAction;
use App\Entity\EnrolledStudent;
use App\Entity\User;
use App\Form\DisciplineActionFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class DisciplineActionController extends AbstractController {

    /**
     * @Route("/faculty/disciplineaction/", name="faculty_disciplineaction")
     * @Route("/faculty/disciplineaction/search/{searchField?}/{searchValue?}", name="faculty_disciplineaction_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('discipline_action/index.html.twig', [
                    'controller_name' => 'DisciplineActionController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/disciplineaction/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_disciplineaction_list")
     * @Route("/faculty/disciplineaction/list/{offset?0}/{pageSize?20}/{sorting?competition.id}/{searchField?}/{searchValue?}", name="faculty_disciplineaction_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'discipline_action',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(DisciplineAction::class);
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
     * @Route("/faculty/disciplineaction/delete", name="faculty_disciplineaction_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $disciplineAction = $entityManager->getRepository(DisciplineAction::class)->find($id);
            $entityManager->remove($disciplineAction);
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
     * @Route("/faculty/disciplineaction/new", name="faculty_disciplineaction_new")
     * @Route("/faculty/disciplineaction/edit/{id?0}", name="faculty_disciplineaction_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(DisciplineAction::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $disciplineAction = $repository->find($id);
        } else {
            $disciplineAction = new DisciplineAction();
        }
        $form = $this->createForm(DisciplineActionFormType::class, $disciplineAction, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $disciplineAction = $form->getData();
            $fields = [
                'note'
            ];
            foreach ($fields as $field) {
                $disciplineAction->setDataField($field, $form->get($field)->getData());
            }
            $disciplineAction->setAuthor($this->getUser());
            $disciplineAction->setDateUpdated(new \DateTime());

            $repository->save($disciplineAction);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_disciplineaction');
        }
        return $this->render('discipline_action/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
