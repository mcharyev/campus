<?php

namespace App\Controller\Structure;

use App\Entity\Department;
use App\Entity\Group;
use App\Entity\ProgramCourse;
use App\Form\DepartmentFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\SystemInfoManager;

class DepartmentController extends AbstractController {

    private $systemInfoManager;

    public function __construct(SystemInfoManager $systemInfoManager) {

        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/faculty/department", name="faculty_department")
     */
    public function index() {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $currentYear = $this->systemInfoManager->getCurrentCommencementYear();
        return $this->render('department/index.html.twig', [
                    'controller_name' => 'DepartmentController',
                    'current_year' => $currentYear,
        ]);
    }

    /**
     * @Route("/faculty/department/list/{startIndex?}/{pageSize?}/{sorting?}", name="faculty_department_list")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $repository = $this->getDoctrine()->getRepository(Department::class);
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
     * @Route("/faculty/department/delete", name="faculty_department_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $department = $entityManager->getRepository(Department::class)->find($id);
            $entityManager->remove($department);
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
     * @Route("/faculty/department/new", name="faculty_department_new")
     * @Route("/faculty/department/edit/{id?0}", name="faculty_department_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");

        $repository = $this->getDoctrine()->getRepository(Department::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $department = $repository->find($id);
        } else {
            $department = new Department();
        }
        $form = $this->createForm(DepartmentFormType::class, $department);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $department = $form->getData();

            $repository->save($department);

            return $this->redirectToRoute('faculty_department');
        }
        return $this->render('department/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
