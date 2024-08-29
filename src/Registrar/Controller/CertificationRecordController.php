<?php

namespace App\Registrar\Controller;

use App\Registrar\Entity\CertificationRecord;
use App\Entity\Teacher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Registrar\Form\CertificationRecordFormType;

class CertificationRecordController extends AbstractController {

    //private $manager;

    function __construct() {
        
    }

    /**
     * @Route("/registrar/certificationrecord", name="registrar_certificationrecord")
     * @Route("/registrar/certificationrecord/search/{searchField?}/{searchValue?}", name="registrar_certificationrecord_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('certification_record/index.html.twig', [
                    'controller_name' => 'CertificationRecordController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/registrar/certificationrecord/list", name="registrar_certificationrecord_list")
     * @Route("/registrar/certificationrecord/list/{offset?0}/{pageSize?20}/{sorting?certification_record.id}/{searchField?}/{searchValue?}", name="registrar_certificationrecord_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'certification_record',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(CertificationRecord::class);
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
     * @Route("/registrar/certificationrecord/datalist", name="registrar_certificationrecord_datalist")
     */
    public function datalist(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $params = [
                'table' => 'certification_record',
//                'offset' => $request->attributes->get('offset'),
//                'pageSize' => $request->attributes->get('pageSize'),
//                'sorting' => $request->attributes->get('sorting'),
//                'searchField' => $request->attributes->get('searchField'),
//                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(CertificationRecord::class);
            $recordCount = $repository->getRecordCount($params);
            $rows = $repository->getRecords($params);
            $datarows = [];
            foreach ($rows as $row) {
                $datarows[] = array_values($row);
            }

            $result_array = $datarows;
        } catch (\Exception $e) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $e->getMessage()
            ];
        }

        $response = new Response("{\"data\":".json_encode($result_array)."}");
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/registrar/certificationrecord/delete", name="registrar_certificationrecord_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(CertificationRecord::class);
            $certificationRecord = $repository->find($id);
            $repository->remove($certificationRecord);

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
     * @Route("/registrar/certificationrecord/new", name="registrar_certificationrecord_new")
     * @Route("/registrar/certificationrecord/edit/{id?0}", name="registrar_certificationrecord_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(CertificationRecord::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $certificationRecord = $repository->find($id);
        } else {
            $certificationRecord = new CertificationRecord();
        }
        $form = $this->createForm(CertificationRecordFormType::class, $certificationRecord, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $certificationRecord = $form->getData();
            $fields = [
                'phone',
                'university',
                'university_original_name',
                'study_period',
                'field',
                'field_original',
                'qualification',
                'diploma_number',
                'degree',
                'degree_original',
                'grade1',
                'grade2',
                'field_local',
                'diploma_number_local',
                'address',
                'work',
                'note'
            ];
            $newData = [];
            foreach ($fields as $field) {
                //$newData[] = array($field => $form->get($field)->getData());
                $certificationRecord->setDataField($field, $form->get($field)->getData());
            }


            $repository->save($certificationRecord);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('registrar_certificationrecord');
        }
        return $this->render('certification_record/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
