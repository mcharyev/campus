<?php

namespace App\Controller\Workload;

use App\Entity\ReportedWork;
use App\Entity\Teacher;
use App\Entity\DepartmentWorkItem;
use App\Entity\TeacherWorkItem;
use App\Form\ReportedWorkFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Enum\WorkColumnEnum;

class ReportedWorkController extends AbstractController {

    private $systemEventManager;

    function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/faculty/reportedwork/", name="faculty_reportedwork")
     * @Route("/faculty/reportedwork/search/{searchField?}/{searchValue?}", name="faculty_reportedwork_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('reported_work/index.html.twig', [
                    'controller_name' => 'ReportedWorkController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/reportedwork/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_reportedwork_list")
     * @Route("/faculty/reportedwork/list/{offset?0}/{pageSize?20}/{sorting?competition.id}/{searchField?}/{searchValue?}", name="faculty_reportedwork_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'reported_work',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'exactMatch' => false,
            ];
            $exactMatchableFields = [
                'teacher_id',
                'id',
                'workitem_id',
            ];
            if (in_array($params['searchField'], $exactMatchableFields)) {
                $params['exactMatch'] = true;
            }
            $repository = $this->getDoctrine()->getRepository(ReportedWork::class);
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
     * @Route("/faculty/reportedwork/delete/{id?}", name="faculty_reportedwork_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $reportedWork = $entityManager->getRepository(ReportedWork::class)->find($id);
            $entityManager->remove($reportedWork);
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
     * @Route("/faculty/reportedwork/new", name="faculty_reportedwork_new")
     * @Route("/faculty/reportedwork/edit/{id?0}", name="faculty_reportedwork_edit")
     * @Route("/faculty/reportedwork/editinjournal/{id?0}/{teacherWorkItem?0}/{year?2020}/{semester?1}", name="faculty_reportedwork_editinjournal")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEPARTMENTHEAD");
        $repository = $this->getDoctrine()->getRepository(ReportedWork::class);
        $id = $request->attributes->get('id');
        $teacherWorkItemId = $request->attributes->get('teacherWorkItem');
        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        if (!empty($id)) {
            $reportedWork = $repository->find($id);
        } else {
            $reportedWork = new ReportedWork();
        }
        if (!empty($teacherWorkItemId)) {
            $teacherWorkItem = $this->getDoctrine()->getRepository(TeacherWorkItem::class)->find($teacherWorkItemId);
            if (!$this->isGranted("ROLE_SPECIALIST")) {
                //echo $viewingTeacher->getLastname()."=".$teacherWorkItem
                if ($teacherWorkItem->getDepartment()->getDepartmentHead() != $viewingTeacher && $teacherWorkItem->getTeacher() != $viewingTeacher) {
                    return $this->render('accessdenied.html.twig');
                }
            }
        } else {
            $teacherWorkItem = null;
        }

        if ($this->isGranted("ROLE_SPECIALIST")) {
            $teacherEnabled = true;
            $workItemEnabled = true;
        } else {
            $teacherEnabled = false;
            $workItemEnabled = false;
        }


        $form = $this->createForm(ReportedWorkFormType::class, $reportedWork, [
            'teacherWorkItem' => $teacherWorkItem,
            'teacherEnabled' => $teacherEnabled,
            'workItemEnabled' => $workItemEnabled,
            'year' => $year,
            'semester' => $semester,
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reportedWork = $form->getData();
            $fields = [
                'note'
            ];
            foreach ($fields as $field) {
                $reportedWork->setDataField($field, $form->get($field)->getData());
            }
            $reportedWork->setAuthor($this->getUser());
            $reportedWork->setDateUpdated(new \DateTime());

            $repository->save($reportedWork);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_REPORTEDWORK, $reportedWork->getId(), "Edited reported work:" . $reportedWork->getAmount());
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_reportedwork');
        }
        return $this->render('reported_work/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/reportedwork/addexpress", name="faculty_reportedwork_addexpress")
     */
    public function addExpress(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $repository = $this->getDoctrine()->getRepository(ReportedWork::class);
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $teacherWorkItemRepository = $this->getDoctrine()->getRepository(TeacherWorkItem::class);
        $action = $request->request->get('reported_work_form_action');
        $id = $request->request->get('reported_work_form_id');
        $teacherId = $request->request->get('reported_work_form_teacher');
        $teacherWorkItemId = $request->request->get('reported_work_form_workitem');
        $reportedWorkType = $request->request->get('reported_work_form_type');
        $reportedWorkAmount = $request->request->get('reported_work_form_amount');
        $reportedWorkDate = $request->request->get('reported_work_form_date');
        $reportedWorkNote = $request->request->get('reported_work_form_note');
        $reportedWork = new ReportedWork();
        if ($action == "update") {
            $reportedWork = $repository->find($id);
        }

        $reportedWork->setTeacher($teacherRepository->find($teacherId));
        $reportedWork->setAuthor($this->getUser());
        $reportedWork->setWorkitem($teacherWorkItemRepository->find($teacherWorkItemId));
        $reportedWork->setType($reportedWorkType);
        $reportedWork->setAmount($reportedWorkAmount);
        $reportedWork->setDate(new \DateTime($reportedWorkDate));
        $reportedWork->setStatus(1);
        $reportedWork->setDataField("note", $reportedWorkNote);
        $repository->save($reportedWork);

        $reportedWorkName = WorkColumnEnum::getTypeName($reportedWorkType);
        $result = $reportedWork->getId() . "|" . $reportedWorkName . "|" . $reportedWorkDate . "|" . $reportedWorkAmount;
        return new Response($result);
    }

}
