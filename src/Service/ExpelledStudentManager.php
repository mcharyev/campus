<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\ExpelledStudent;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use App\Form\ExpelledStudentFormType;

/**
 * Description of ExpelledStudentManager
 *
 * @author nazar
 */
class ExpelledStudentManager {

    //put your code here
    public function createFromRequest(Request $request): ExpelledStudent {
        $enrolledStudent = new ExpelledStudent();
        $enrolledStudent->setSystemId($request->request->get('system_id'))
                ->setFirstnameEnglish($request->request->get('firstname_english'))
                ->setLastnameEnglish($request->request->get('lastname_english'))
                ->setPatronymEnglish($request->request->get('patronym_english'))
                ->setPreviousLastnameEnglish($request->request->get('previous_lastname_english'))
                ->setFirstnameTurkmen($request->request->get('firstname_turkmen'))
                ->setLastnameTurkmen($request->request->get('lastname_turkmen'))
                ->setPatronymTurkmen($request->request->get('patronym_turkmen'))
                ->setPreviousLastnameTurkmen($request->request->get('previous_lastname_turkmen'));
        return $enrolledStudent;
    }

    public function updateFromRequest(Request $request, ExpelledStudent $enrolledStudent): ExpelledStudent {
        $enrolledStudent->setSystemId($request->request->get('system_id'))
                ->setFirstnameEnglish($request->request->get('firstname_english'))
                ->setLastnameEnglish($request->request->get('lastname_english'))
                ->setPatronymEnglish($request->request->get('patronym_english'))
                ->setPreviousLastnameEnglish($request->request->get('previous_lastname_english'))
                ->setFirstnameTurkmen($request->request->get('firstname_turkmen'))
                ->setLastnameTurkmen($request->request->get('lastname_turkmen'))
                ->setPatronymTurkmen($request->request->get('patronym_turkmen'))
                ->setPreviousLastnameTurkmen($request->request->get('previous_lastname_turkmen'));
        return $enrolledStudent;
    }

    public function updateFromForm(ExpelledStudentFormType $form) {
        
        return $enrolledStudent;
    }

}
