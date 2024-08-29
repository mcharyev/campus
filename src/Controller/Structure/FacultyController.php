<?php

namespace App\Controller\Structure;

use App\Entity\Faculty;
use App\Form\FacultyFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class FacultyController extends AbstractController {

    /**
     * @Route("/faculty/faculty", name="faculty_faculty")
     */
    public function index() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $values = [
            'controller_name' => 'FacultyController',
        ];
        return $this->render('/faculty/index.html.twig', $values);
    }

    /**
     * @Route("/faculty/faculty/list/{startIndex}/{pageSize}/{sorting}", name="faculty_faculty_list")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $repository = $this->getDoctrine()->getRepository(Faculty::class);
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
     * @Route("/faculty/faculty/create", name="faculty_faculty_create")
     */
    public function create(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();

            $faculty = new Faculty();
            $faculty->setNameEnglish($request->request->get('name_english'));
            $faculty->setNameTurkmen($request->request->get('name_turkmen'));
            $faculty->setSystemId($request->request->get('system_id'));
            $faculty->setLetterCode($request->request->get('letter_code'));
            $faculty->setDeanId($request->request->get('dean_id'));
            $faculty->setFirstViceDeanId($request->request->get('first_vice_dean_id'));
            $faculty->setSecondViceDeanId($request->request->get('second_vice_dean_id'));
            $faculty->setThirdViceDeanId($request->request->get('third_vice_dean_id'));

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($faculty);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
            //*/
            $repository = $this->getDoctrine()->getRepository(Faculty::class);
            $result = $repository->getLastInserted();

            //Return result
            $result_array = [
                'Result' => "OK",
                'Record' => $result
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
     * @Route("/faculty/faculty/update", name="faculty_faculty_update")
     */
    public function update(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            $faculty = $entityManager->getRepository(Faculty::class)->find($id);
            $faculty->setNameEnglish($request->request->get('name_english'));
            $faculty->setNameTurkmen($request->request->get('name_turkmen'));
            $faculty->setSystemId($request->request->get('system_id'));
            $faculty->setLetterCode($request->request->get('letter_code'));
            $faculty->setDeanId($request->request->get('dean_id'));
            $faculty->setFirstViceDeanId($request->request->get('first_deputy_dean_id'));
            $faculty->setSecondViceDeanId($request->request->get('second_deputy_dean_id'));
            $faculty->setThirdViceDeanId($request->request->get('third_deputy_dean_id'));

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            //$entityManager->persist($faculty);
            // actually executes the queries (i.e. the INSERT query)
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
     * @Route("/faculty/faculty/delete", name="faculty_faculty_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            $faculty = $entityManager->getRepository(Faculty::class)->find($id);
            $entityManager->remove($faculty);
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
     * @Route("/faculty/faculty/new", name="faculty_faculty_new")
     * @Route("/faculty/faculty/edit/{id?0}", name="faculty_faculty_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(Faculty::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $faculty = $repository->find($id);
        } else {
            $faculty = new Faculty();
        }
        $form = $this->createForm(FacultyFormType::class, $faculty);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $faculty = $form->getData();

            $repository->save($faculty);

            return $this->redirectToRoute('faculty_faculty');
        }
        return $this->render('faculty/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
