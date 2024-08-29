<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\Classroom;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of ClassroomManager
 *
 * @author nazar
 */
class ClassroomManager {

    //put your code here
    public function createFromRequest(Request $request): Classroom {
        $classroom = new Classroom();
        $classroom->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setCapacity($request->request->get('capacity'))
                ->setType($request->request->get('type'))
                ->setFloor($request->request->get('floor'))
                ->setData($request->request->get('data'))
                ->setBuildingId($request->request->get('building_id'))
                ->setStatus($request->request->get('status'));
        return $classroom;
    }

    public function updateFromRequest(Request $request, Classroom $classroom): Classroom {
        $classroom->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setCapacity($request->request->get('capacity'))
                ->setType($request->request->get('type'))
                ->setFloor($request->request->get('floor'))
                ->setData($request->request->get('data'))
                ->setBuildingId($request->request->get('building_id'))
                ->setStatus($request->request->get('status'));
        return $classroom;
    }

}
