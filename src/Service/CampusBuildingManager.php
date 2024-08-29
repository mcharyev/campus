<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\CampusBuilding;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of CampusBuildingManager
 *
 * @author nazar
 */
class CampusBuildingManager {

    //put your code here
    public function createFromRequest(Request $request): CampusBuilding {
        $campusBuilding = new CampusBuilding();
        $campusBuilding->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setCampusLocation($request->request->get('campus_location_id'));
        return $campusBuilding;
    }

    public function updateFromRequest(Request $request, CampusBuilding $campusBuilding): CampusBuilding {
        $campusBuilding->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setCampusLocation($campusBuilding->$request->request->get('campus_location_id'));
        return $campusBuilding;
    }

}
