<?php

namespace App\Controller\Building;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ClassroomTypeController extends AbstractController
{
    /**
     * @Route("/classroom/type", name="classroom_type")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        return $this->render('classroom_type/index.html.twig', [
            'controller_name' => 'ClassroomTypeController',
        ]);
    }
}
