<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\StudyProgram;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
/**
 * Description of StudyProgramManager
 *
 * @author nazar
 */
final class StudyProgramManager {
    //put your code here
    public function createFromRequest(Request $request): StudyProgram {
        $studyprogram = new StudyProgram();
        $studyprogram->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setApprovalYear($request->request->get('approval_year'))
                ->setDepartmentId($request->request->get('department_id'))
                ->setStatus($request->request->get('status'));
        return $studyprogram;
    }
    
    public function updateFromRequest(Request $request, StudyProgram $studyprogram): StudyProgram {
        $studyprogram->setSystemId($request->request->get('system_id'))
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setApprovalYear($request->request->get('approval_year'))
                ->setDepartmentId($request->request->get('department_id'))
                ->setStatus($request->request->get('status'));
        return $studyprogram;
    }
}
