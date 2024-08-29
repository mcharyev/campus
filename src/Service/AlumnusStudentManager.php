<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\AlumnusStudent;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use App\Form\AlumnusStudentFormType;

/**
 * Description of AlumnusStudentManager
 *
 * @author nazar
 */
class AlumnusStudentManager {

    //put your code here
    public function createFromRequest(Request $request): AlumnusStudent {
        $alumnusStudent = new AlumnusStudent();
        $alumnusStudent->setSystemId($request->request->get('system_id'))
//                ->setFirstnameEnglish($request->request->get('firstname_english'))
//                ->setLastnameEnglish($request->request->get('lastname_english'))
//                ->setPatronymEnglish($request->request->get('patronym_english'))
//                ->setPreviousLastnameEnglish($request->request->get('previous_lastname_english'))
                ->setTags($request->request->get('tags'))
                ->setFirstnameTurkmen($request->request->get('firstname_turkmen'))
                ->setLastnameTurkmen($request->request->get('lastname_turkmen'))
                ->setPatronymTurkmen($request->request->get('patronym_turkmen'))
                ->setPreviousLastnameTurkmen($request->request->get('previous_lastname_turkmen'));
        return $alumnusStudent;
    }

    public function updateFromRequest(Request $request, AlumnusStudent $alumnusStudent): AlumnusStudent {
        $alumnusStudent->setSystemId($request->request->get('system_id'))
//                ->setFirstnameEnglish($request->request->get('firstname_english'))
//                ->setLastnameEnglish($request->request->get('lastname_english'))
//                ->setPatronymEnglish($request->request->get('patronym_english'))
//                ->setPreviousLastnameEnglish($request->request->get('previous_lastname_english'))
                ->setTags($request->request->get('tags'))
                ->setFirstnameTurkmen($request->request->get('firstname_turkmen'))
                ->setLastnameTurkmen($request->request->get('lastname_turkmen'))
                ->setPatronymTurkmen($request->request->get('patronym_turkmen'))
                ->setPreviousLastnameTurkmen($request->request->get('previous_lastname_turkmen'));
        return $alumnusStudent;
    }

    public function updateFromForm(AlumnusStudentFormType $form) {
        
        return $alumnusStudent;
    }

}
