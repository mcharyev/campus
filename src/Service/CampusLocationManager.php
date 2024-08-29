<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\CampusLocation;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of CampusLocationManager
 *
 * @author nazar
 */
class CampusLocationManager {

    //put your code here
    public function createFromRequest(Request $request): CampusLocation {
        $campusLocation = new CampusLocation();
        $campusLocation->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setAddress($request->request->get('address'))
                ->setStatus($request->request->get('status'));
        return $campusLocation;
    }

    public function updateFromRequest(Request $request, CampusLocation $campusLocation): CampusLocation {
        $campusLocation->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setAddress($request->request->get('address'))
                ->setStatus($request->request->get('status'));
        return $campusLocation;
    }

}
