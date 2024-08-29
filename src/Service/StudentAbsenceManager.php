<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use App\Entity\StudentAbsence;
use App\Entity\EnrolledStudent;
use App\Entity\TaughtCourse;
use App\Entity\Teacher;
use App\Entity\ClassType;

/**
 * Description of StudentAbsenceManager
 *
 * @author nazar
 */
class StudentAbsenceManager {

    //put your code here
    public function createFromRequest(Request $request, EnrolledStudent $student, TaughtCourse $course, Teacher $teacher, ClassType $type): StudentAbsence {
        $studentAbsence = new StudentAbsence();
        $studentAbsence->setStudent($student)
                ->setCourse($course)
                ->setAuthor($teacher)
                ->setStudentGroup($student->getStudentGroup())
                //->setDepartment($student->getDepartment())
                ->setDepartment($student->getStudentGroup()->getDepartment())
                ->setFaculty($student->getStudentGroup()->getDepartment()->getFaculty())
                ->setClassType($type)
                ->setDate(new \DateTime($request->request->get('absence_date')))
                ->setSession(intval($request->request->get('absence_session')))
                ->setNote($request->request->get('absence_note'))
                ->setStatus(intval($request->request->get('absence_status')))
                ->setDateCreated(new \DateTime())
                ->setDateUpdated(new \DateTime())
        ;
        return $studentAbsence;
    }

    public function updateFromRequest(Request $request, StudentAbsence $studentAbsence, EnrolledStudent $student, TaughtCourse $course, Teacher $teacher, ClassType $type): StudentAbsence {
        $studentAbsence->setStudent($student)
                ->setCourse($course)
                ->setAuthor($teacher)
                ->setClassType($type)
                ->setStudentGroup($student->getStudentGroup())
                ->setDepartment($student->getStudentGroup()->getDepartment())
                ->setFaculty($student->getStudentGroup()->getDepartment()->getFaculty())
                ->setDate(new \DateTime($request->request->get('absence_date')))
                ->setSession(intval($request->request->get('absence_session')))
                ->setNote($request->request->get('absence_note'))
                ->setStatus(intval($request->request->get('absence_status')))
                ->setDateUpdated(new \DateTime())
        ;
        if (intval($request->request->get('absence_status')) == 1) {
            $student->setAuthorApprovalDate(new \DateTime());
        }
        if (intval($request->request->get('absence_status')) == 2) {
            $student->setDeanApprovalDate(new \DateTime());
        }
        return $studentAbsence;
    }

}
