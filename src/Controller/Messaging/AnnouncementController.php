<?php

namespace App\Controller\Messaging;

use App\Entity\Announcement;
use App\Entity\Department;
use App\Form\AnnouncementFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class AnnouncementController extends AbstractController {

    /**
     * @Route("/faculty/announcement", name="faculty_announcement")
     * @Route("/faculty/announcement/search/{searchField?}/{searchValue?}", name="faculty_announcement_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('announcement/index.html.twig', [
                    'controller_name' => 'AnnouncementController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/announcement/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_announcement_list")
     * @Route("/faculty/announcement/list/{offset?0}/{pageSize?20}/{sorting?announcement.id}/{searchField?}/{searchValue?}", name="faculty_announcement_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'announcement',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Announcement::class);
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
     * @Route("/faculty/announcement/customlist/general", name="faculty_announcement_customlist_general")
     */
    public function listGeneral(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $content = "";
        try {
            $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
            $departments = $departmentRepository->findAll();
            $content .= "<table class='table'>";
            foreach ($departments as $department) {
                $announcements = $department->getAnnouncements();
                $content .= "<tr><td colspan='3' style='font-weight:bold'>" . $department->getNameEnglish() . "</td></tr>";
                foreach ($announcements as $announcement) {
                    $content .= "<tr><td>" . $announcement->getFullname() . "</td><td>" . $announcement->getId() . "</td><td>" . $announcement->getSystemId() . "</td></tr>";
                }
            }
            $content .= "</table>";
        } catch (\Exception $e) {
            //Return error message
            $content .= $e->getMessage();
        }

        return $this->render('announcement/announcement.html.twig', [
                    'controller_name' => 'AnnouncementController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/faculty/announcement/delete", name="faculty_announcement_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $announcement = $entityManager->getRepository(Announcement::class)->find($id);
            $entityManager->remove($announcement);
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
     * @Route("/faculty/announcement/new", name="faculty_announcement_new")
     * @Route("/faculty/announcement/edit/{id?0}", name="faculty_announcement_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(Announcement::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $announcement = $repository->find($id);
        } else {
            $announcement = new Announcement();
        }
        $form = $this->createForm(AnnouncementFormType::class, $announcement, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $announcement = $form->getData();
            
            $repository->save($announcement);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_announcement');
        }
        return $this->render('announcement/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

}
