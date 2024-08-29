<?php

namespace App\Controller\Schedule;

use App\Entity\Schedule;
use App\Form\ScheduleFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class ScheduleController extends AbstractController {

    /**
     * @Route("/faculty/schedule", name="faculty_schedule")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('schedule/index.html.twig', [
                    'controller_name' => 'ScheduleController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/schedule/list/{startIndex?}/{pageSize?}/{sorting?}", name="faculty_schedule_list")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $repository = $this->getDoctrine()->getRepository(Schedule::class);
            $recordCount = $repository->getRecordCount();

            //Get records from database
            $results = $repository->getRecords(['startIndex' => $startIndex, 'pageSize' => $pageSize, 'sorting' => $sorting]);

            //Return result
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
     * @Route("/faculty/schedule/delete", name="faculty_schedule_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $schedule = $entityManager->getRepository(Schedule::class)->find($id);
            $entityManager->remove($schedule);
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
     * @Route("/faculty/schedule/new", name="faculty_schedule_new")
     * @Route("/faculty/schedule/edit/{id?0}", name="faculty_schedule_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(Schedule::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $schedule = $repository->find($id);
        } else {
            $schedule = new Schedule();
        }
        $form = $this->createForm(ScheduleFormType::class, $schedule);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $schedule = $form->getData();

            $repository->save($schedule);

            return $this->redirectToRoute('faculty_schedule');
        }
        return $this->render('schedule/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
