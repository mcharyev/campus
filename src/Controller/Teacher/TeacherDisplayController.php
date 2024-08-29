<?php

namespace App\Controller\Teacher;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Teacher;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class TeacherDisplayController extends AbstractController {

    private $systemEventManager;

    public function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/student/index", name="student_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_STUDENT");
        $body = '';
        return $this->render('studentstart.html.twig', [
                    'page_title' => 'Welcome!',
                    'page_content' => $body,
        ]);
    }

    /**
     * @Route("/teacher/sisinfo", name="teacher_sisinfo")
     */
    public function displaySisInfo(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $body = '';
        $id = $request->attributes->get('systemId');
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        if ($id)
            $teacher = $teacherRepository->findOneBy(['systemId' => $id]);
        else
            $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        //$body .= $id;
        if ($teacher) {
            //$body .= $this->getGrades($student->getSystemId());
            if ($teacher->getSystemId() == $this->getUser()->getSystemId()) {
                $body = $this->getSisInfo($teacher->getSystemId());
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHER, $teacher->getId(), 'Viewed SIS info');
            } else {
                if ($this->isGranted("ROLE_SPECIALIST")) {
                    $body = $this->getSisInfo($teacher->getSystemId());
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHER, $teacher->getId(), 'Viewed SIS info');
                } else {
                    $body = 'You are not authorized to view this page';
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHER, $teacher->getId(), 'Unauthorized SIS view attempt');
                }
            }
        }
        //$body = '';
        return $this->render('start.html.twig', [
                    'page_title' => 'SIS Info',
                    'page_content' => $body,
        ]);
    }

    private function getSisInfo($id): ?string {
        $result = '';
        $result .= "<h3>Teacher SIS Info</h3>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sis/campus.asp?action=showteacherinfo&sisid=" . $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);
        $result .= $out;
        return $result;
    }

}
