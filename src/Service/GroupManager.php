<?php

namespace App\Service;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Entity\Group;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use App\Entity\Teacher;
use App\Entity\StudyProgram;
use App\Entity\EnrolledStudent;

/**
 * Description of GroupManager
 *
 * @author nazar
 */
final class GroupManager {

//    public function createFromRequest(Request $request, StudyProgram $studyProgram, Teacher $groupAdvisor, EnrolledStudent $groupLeader): Group {
//        $group = new Group();
//        $group->setSystemId($request->request->get('system_id'))
//                ->setLetterCode($request->request->get('letter_code'))
//                ->setGraduationYear($request->request->get('graduation_year'))
//                ->setStudyProgram($studyProgram)
//                ->setAdvisor($groupAdvisor)
//                ->setGroupLeader($groupLeader)
//                ->setStatus($request->request->get('status'));
//        return $group;
//    }

    public function updateFromRequest(Request $request, Group $group, StudyProgram $studyProgram, Teacher $groupAdvisor, Student $groupLeader): Group {
        return $group;
//        try {
////            $group->setSystemId($request->request->get('system_id'))
////                    ->setLetterCode($request->request->get('letter_code'))
////                    ->setGraduationYear($request->request->get('graduation_year'))
////                    ->setStudyProgram($studyProgram)
////                    ->setAdvisor($groupAdvisor)
////                    ->setGroupLeader($groupLeader)
////                    ->setStatus($request->request->get('status'));
//            return $group;
//        } catch (\Exception $e) {
////            echo $e->getMessage();
//        }
    }

}
