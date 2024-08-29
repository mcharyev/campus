<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\Hostel;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of HostelManager
 *
 * @author nazar
 */
class HostelManager {

    //put your code here
    public function createFromRequest(Request $request): Hostel {
        $hostel= new Hostel();
        $hostel->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'));
        return $hostel;
    }

    public function updateFromRequest(Request $request, Hostel $hostel): Hostel {
        $hostel->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'));
        return $hostel;
    }

}
