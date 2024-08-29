<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Hr\Service;

use App\Hr\Entity\Employee;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use App\Hr\Form\EmployeeFormType;
use App\Entity\Nationality;

/**
 * Description of EmployeeManager
 *
 * @author nazar
 */
class EmployeeManager {

//    //put your code here
//    public function createFromRequest(Request $request): Employee {
//        $employee = new Employee();
//        $employee->setSystemId($request->request->get('system_id'))
//                ->setFirstnameEnglish($request->request->get('firstname_english'))
//                ->setLastnameEnglish($request->request->get('lastname_english'))
//                ->setPatronymEnglish($request->request->get('patronym_english'))
//                ->setPreviousLastnameEnglish($request->request->get('previous_lastname_english'))
//                ->setFirstnameTurkmen($request->request->get('firstname_turkmen'))
//                ->setLastnameTurkmen($request->request->get('lastname_turkmen'))
//                ->setPatronymTurkmen($request->request->get('patronym_turkmen'))
//                ->setPreviousLastnameTurkmen($request->request->get('previous_lastname_turkmen'));
//        return $employee;
//    }

    public function updateFromRequest(Request $request, Employee $employee, Nationality $nationality): Employee {
        $data = json_decode($request->request->get('data'), true);
        $employee->setSystemId($request->request->get('system_id'))
                ->setFirstname($request->request->get('firstname'))
                ->setLastname($request->request->get('lastname'))
                ->setPatronym($request->request->get('patronym'))
                ->setBirthdate(new \DateTime($request->request->get('birthdate')))
                ->setBirthplace($request->request->get('birthplace'))
                ->setGender($request->request->get('gender'))
                ->setStatus($request->request->get('status'))
                ->setWorktimeCategory($request->request->get('worktime_category'))
                ->setDepartmentCode($request->request->get('department_code'))
                ->setNationality($nationality)
                ->setData($data)
                ->setPosition($request->request->get('position'));
        return $employee;
    }

    public function updateFromForm(EmployeeFormType $form) {

        return $employee;
    }

}
