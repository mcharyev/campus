<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\TranscriptCourse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TranscriptCourseManager
 *
 * @author nazar
 */
final class TranscriptCourseManager {
    //put your code here
    public function createFromRequest(Request $request): TranscriptCourse {
        $transcriptcourse = new TranscriptCourse();
        $transcriptcourse
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setStatus($request->request->get('status'));
        return $transcriptcourse;
    }
    
    public function updateFromRequest(Request $request, TranscriptCourse $transcriptcourse): TranscriptCourse {
        $transcriptcourse
                ->setLetterCode($request->request->get('letter_code'))
                ->setNameEnglish($request->request->get('name_english'))
                ->setNameTurkmen($request->request->get('name_turkmen'))
                ->setStatus($request->request->get('status'));
        return $transcriptcourse;
    }
}
