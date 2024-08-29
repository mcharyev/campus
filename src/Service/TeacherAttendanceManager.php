<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use App\Entity\TeacherAttendance;
use App\Entity\EnrolledStudent;
use App\Entity\TaughtCourse;
use App\Entity\Teacher;
use App\Entity\ClassType;

/**
 * Description of TeacherAttendanceManager
 *
 * @author nazar
 */
class TeacherAttendanceManager {

    //put your code here
    public function createFromRequest(Request $request, TaughtCourse $course, Teacher $teacher, ClassType $type): TeacherAttendance {
        $teacherAttendance = new TeacherAttendance();
        $teacherAttendance->setTeacher($teacher)
                ->setCourse($course)
                ->setDepartment($teacher->getDepartment())
                ->setFaculty($teacher->getFaculty())
                ->setClassType($type)
                ->setDate(new \DateTime($request->request->get('absence_date')))
                ->setSession(intval($request->request->get('absence_session')))
                ->setNote($request->request->get('absence_note'))
                ->setStatus(intval($request->request->get('absence_status')))
                ->setDateUpdated(new \DateTime())
        ;
        return $teacherAttendance;
    }

    public function updateFromRequest(Request $request, TeacherAttendance $teacherAttendance, TaughtCourse $course, Teacher $teacher, ClassType $type): TeacherAttendance {
        $teacherAttendance->setTeacher($teacher)
                ->setCourse($course)
                ->setDepartment($teacher->getDepartment())
                ->setFaculty($teacher->getFaculty())
                ->setClassType($type)
                ->setDate(new \DateTime($request->request->get('absence_date')))
                ->setSession(intval($request->request->get('absence_session')))
                ->setNote($request->request->get('absence_note'))
                ->setStatus(intval($request->request->get('absence_status')))
                ->setDateUpdated(new \DateTime())
        ;
       
        return $teacherAttendance;
    }

}
