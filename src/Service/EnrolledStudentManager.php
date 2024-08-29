<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\EnrolledStudent;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use App\Form\EnrolledStudentFormType;
use App\Entity\Group;
use App\Entity\Region;
use App\Entity\HostelRoom;

/**
 * Description of EnrolledStudentManager
 *
 * @author nazar
 */
class EnrolledStudentManager {

    //put your code here
    public function createFromRequest(Request $request): EnrolledStudent {
        $enrolledStudent = new EnrolledStudent();
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

    public function updateFromRequest(Request $request, EnrolledStudent $enrolledStudent, Group $studentGroup, Region $region, HostelRoom $hostelRoom): EnrolledStudent {
        $enrolledStudent->setSystemId($request->request->get('system_id'))
                ->setFirstnameEnglish($request->request->get('firstname_english'))
                ->setLastnameEnglish($request->request->get('lastname_english'))
                ->setPatronymEnglish($request->request->get('patronym_english'))
                ->setPreviousLastnameEnglish($request->request->get('previous_lastname_english'))
                ->setFirstnameTurkmen($request->request->get('firstname_turkmen'))
                ->setLastnameTurkmen($request->request->get('lastname_turkmen'))
                ->setPatronymTurkmen($request->request->get('patronym_turkmen'))
                ->setPreviousLastnameTurkmen($request->request->get('previous_lastname_turkmen'))
                ->setSubgroup($request->request->get('subgroup'))
                ->setRegion($request->request->get('region'))
                ->setGender($request->request->get('gender'))
                ->setStudentGroup($studentGroup)
                ->setHostelRoom($hostelRoom)
                ->setGroupCode($studentGroup->getSystemId());

        return $enrolledStudent;
    }

    public function updateFromForm(EnrolledStudentFormType $form) {

        return $enrolledStudent;
    }

}
