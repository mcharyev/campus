<?php

namespace App\Controller\Schedule;

use App\Entity\ScheduleChange;
use App\Entity\Teacher;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Form\ScheduleChangeFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Enum\ScheduleChangeStatusEnum;

class ScheduleChangeController extends AbstractController {

    /**
     * @Route("/faculty/schedulechange/", name="faculty_schedulechange")
     * @Route("/faculty/schedulechange/search/{searchField?}/{searchValue?}", name="faculty_schedulechange_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('schedule_change/index.html.twig', [
                    'controller_name' => 'ScheduleChangeController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/schedulechange/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_schedulechange_list")
     * @Route("/faculty/schedulechange/list/{offset?0}/{pageSize?20}/{sorting?competition.id}/{searchField?}/{searchValue?}", name="faculty_schedulechange_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'schedule_change',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'exactMatch' => false,
            ];
            if ($params['searchField'] == 'schedule_item_id' || $params['searchField'] == 'new_teacher_id') {
                $params['exactMatch'] = true;
            
                
            }
            $repository = $this->getDoctrine()->getRepository(ScheduleChange::class);
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
     * @Route("/faculty/schedulechange/delete", name="faculty_schedulechange_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $scheduleChange = $entityManager->getRepository(ScheduleChange::class)->find($id);
            if ($scheduleChange->getStatus() == ScheduleChangeStatusEnum::UNLOCKED) {
                $entityManager->remove($scheduleChange);
                $entityManager->flush();
                //Return result
                $result_array = [
                    'Result' => "OK"
                ];
            } else {
                //Return result
                $result_array = [
                    'Result' => "Schedule change cannot be deleted because it is locked."
                ];
            }
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
     * @Route("/faculty/schedulechange/new", name="faculty_schedulechange_new")
     * @Route("/faculty/schedulechange/edit/{id?0}", name="faculty_schedulechange_edit")
     * @Route("/faculty/schedulechange/editinjournal/{id?0}/{scheduleItem?0}/{year?2020}/{semester?1}/{date?}", name="faculty_schedulechange_editinjournal")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $repository = $this->getDoctrine()->getRepository(ScheduleChange::class);
        $id = $request->attributes->get('id');
        $scheduleItemId = $request->attributes->get('scheduleItem');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $date = $request->attributes->get('date');
        if (!empty($id)) {
            $scheduleChange = $repository->find($id);
        } else {
            $scheduleChange = new ScheduleChange();
        }
        if (!empty($scheduleItemId)) {
            $scheduleItem = $this->getDoctrine()->getRepository(ScheduleItem::class)->find($scheduleItemId);
        } else {
            $scheduleItem = null;
        }
        $form = $this->createForm(ScheduleChangeFormType::class, $scheduleChange, [
            'scheduleItem' => $scheduleItem,
            'year' => $year,
            'semester' => $semester,
            'date' => $date,
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $scheduleChange = $form->getData();
            $fields = [
                'note'
            ];
            foreach ($fields as $field) {
                $scheduleChange->setDataField($field, $form->get($field)->getData());
            }
            $scheduleChange->setAuthor($this->getUser());
            $scheduleChange->setDateUpdated(new \DateTime());

            $repository->save($scheduleChange);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_schedulechange');
        }
        return $this->render('schedule_change/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/schedulechange/updateitem/{scheduleChangeId}/{field}/{value}", name="faculty_schedulechange_updateitem")
     */
    public function updateItem(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $repository = $this->getDoctrine()->getRepository(ScheduleChange::class);
        $id = $request->attributes->get('scheduleChangeId');
        $field = $request->attributes->get('field');
        $value = $request->attributes->get('value');
        $result = '';

        $scheduleChange = $repository->find($id);
        if ($scheduleChange) {
            switch ($field) {
                case 'status':
                    $scheduleChange->setStatus($value);
                    break;
            }
            $repository->save($scheduleChange);
            $result = "<span style='color:green'>OK " . $field . "->" . $value . "</span>";
        } else {
            $result = 'Not found.';
        }

        return new Response($result);
    }

}
