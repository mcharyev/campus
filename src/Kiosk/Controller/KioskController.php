<?php

namespace App\Kiosk\Controller;

use App\Registrar\Entity\RectorOrder;
use App\Registrar\Form\RectorOrderFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

class KioskController extends AbstractController {

    /**
     * @Route("/kiosk/redirect/", name="kiosk_redirect")
     * 
     */
    public function index(Request $request) {
        //$url = $request->attributes->get('url');
        
        return new RedirectResponse('http://campus:8081/kiosk/calendarupdate.php');
    }

}