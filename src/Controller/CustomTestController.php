<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\StudentAbsenceManager;
use App\Service\TeacherAttendanceManager;
use App\Entity\ProgramCourse;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\EnrolledStudent;
use App\Entity\ClassType;
use App\Entity\StudentAbsence;
use App\Entity\Faculty;
use App\Entity\Department;
use App\Entity\TeacherAttendance;
use App\Entity\TeacherWorkItem;
use App\Entity\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CustomTestController extends AbstractController {

    /**
     * @Route("/customtest/index", name="customtest_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/customtest/postvieworder", name="customtest_postvieworder")
     */
    public function postViewOrder() {
        $result = '';
        $fieldValuePairsArray = [];
        $fieldValuePairsArray[] = "action=viewOrderUp";
        $fieldValuePairsArray[] = "teacherWorkSetId=11";
        $fieldValuePairsArray[] = "value=5";
        $fieldValuePairs = join("&", $fieldValuePairsArray);

        $result .= "--POST DATA--<br>";
        $result .= $fieldValuePairs;
        $result .= "<br>--END--<br>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://campus3/faculty/teacherworkset/updatefield");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldValuePairs);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);

        $result .= "--OUTPUT--<br>";
        $result .= $out;
        $result .= "<br>--END--<br>";
        return new Response($result);
    }
    
    /**
     * @Route("/customtest/editorupdate", name="customtest_editorupdate")
     */
    public function editorUpdate() {
        $result = '';
        $url = "http://campus3/custom/tableeditor/update";
        $fieldValuePairsArray = [];
        $fieldValuePairsArray[] = "table=enrolled_student";
        $fieldValuePairsArray[] = "fields=firstname_turkmen,lastname_turkmen,region";
        $fieldValuePairsArray[] = "id[]=0";
        $fieldValuePairsArray[] = "id[]=0";
        $fieldValuePairsArray[] = "id[]=0";
        $fieldValuePairs = join("&", $fieldValuePairsArray);

//        $result .= "--POST DATA--<br>";
        $result .= $fieldValuePairs;
        $result .= "<hr>";
//        $result .= "<br>--END--<br><hr>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldValuePairs);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);

//        $result .= "--OUTPUT--<br>";
        $result .= $out;
//        $result .= "<br>--END--<br>";
        return new Response($result);
    }

}
