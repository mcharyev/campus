<?php

namespace App\Controller\Student;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\EnrolledStudent;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Controller\Student\StudentSisGrade;

class StudentDisplayController extends AbstractController {

    private $systemEventManager;

    public function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/student/index", name="student_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_STUDENT");
        $body = '';
        return $this->render('studentstart.html.twig', [
                    'page_title' => 'Welcome!',
                    'page_content' => $body,
        ]);
    }

    /**
     * @Route("/student/grades/transcript/{systemId?}", name="student_grades_transcript")
     */
    public function displayStudentTranscript(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_STUDENT");
        $body = '';
        $id = $request->attributes->get('systemId');
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        if ($id)
            $student = $studentRepository->findOneBy(['systemId' => $id]);
        else
            $student = $studentRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        //$body .= $id;
        if ($student) {
            //$body .= $this->getTranscript($student->getSystemId());

            if ($student->getSystemId() == $this->getUser()->getSystemId()) {
                $body = $this->getTranscript($student->getSystemId());
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Viewed transcript');
            } else {
                if ($this->isGranted("ROLE_SPECIALIST")) {
                    $body = $this->getTranscript($student->getSystemId());
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Viewed transcript');
                } else {
                    $body = 'You are not authorized to view this page';
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Unauthorized view attempt');
                }
            }
        }
        //$body = '';
        return $this->render('studentstart.html.twig', [
                    'page_title' => 'Student Transcript',
                    'page_content' => $body,
        ]);
    }

    private function getTranscript($studentId): ?string {
        $result = '';
        $result .= "<h3>STUDENT TRANSCRIPT</h3>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sis/campus.asp?action=transcript&sisid=" . $studentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);
        $result .= $out;
        return $result;
    }

    /**
     * @Route("/student/grades/grades/{systemId?}", name="student_grades_grades")
     */
    public function displayStudentGrades(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_STUDENT");
        $body = '';
        $id = $request->attributes->get('systemId');
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        if ($id)
            $student = $studentRepository->findOneBy(['systemId' => $id]);
        else
            $student = $studentRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        //$body .= $id;
        if ($student) {
            //$body .= $this->getGrades($student->getSystemId());

            if ($student->getSystemId() == $this->getUser()->getSystemId()) {
                $body = $this->getGrades($student->getSystemId());
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Viewed grades');
            } else {
                if ($this->isGranted("ROLE_SPECIALIST")) {
                    $body = $this->getGrades($student->getSystemId());
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Viewed grades');
                } else {
                    $body = 'You are not authorized to view this page';
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Unauthorized view attempt');
                }
            }
        }
        //$body = '';
        return $this->render('studentstart.html.twig', [
                    'page_title' => 'Semester Grades',
                    'page_content' => $body,
        ]);
    }

    private function getGrades($studentId): ?string {
        $result = '';
        $result .= "<h3>SEMESTER GRADES</h3>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sis/campus.asp?action=semestergrades&sisid=" . $studentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);
        $result .= $out;
        return $result;
    }

    /**
     * @Route("/student/sisinfo", name="student_sisinfo")
     */
    public function displaySisInfo(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_STUDENT");
        $body = '';
        $id = $request->attributes->get('systemId');
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        if ($id)
            $student = $studentRepository->findOneBy(['systemId' => $id]);
        else
            $student = $studentRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        //$body .= $id;
        if ($student) {
            //$body .= $this->getGrades($student->getSystemId());

            if ($student->getSystemId() == $this->getUser()->getSystemId()) {
                $body = $this->getSisInfo($student->getSystemId());
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Viewed SIS info');
            } else {
                if ($this->isGranted("ROLE_SPECIALIST")) {
                    $body = $this->getSisInfo($student->getSystemId());
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Viewed SIS info');
                } else {
                    $body = 'You are not authorized to view this page';
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(), 'Unauthorized SIS view attempt');
                }
            }
        }
        //$body = '';
        return $this->render('studentstart.html.twig', [
                    'page_title' => 'SIS Info',
                    'page_content' => $body,
        ]);
    }

    private function getSisInfo($studentId): ?string {
        $result = '';
        $result .= "<h3>Student SIS Info</h3>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sis/campus.asp?action=showstudentinfo&sisid=" . $studentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);
        $result .= $out;
        return $result;
    }

    private function sis($id) {
        $result = '';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://sis/stn.asp");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "u_s=" . $id . "&action=login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);

        //echo $_GET['ssn'];
        //$result .= "/" . $out . "/";
        $result .= "---ANALYSIS---<br>";
        $begin = strpos($out, "</thead><tbody>");
        $end = strpos($out, "</tbody");
        $str2 = substr($out, $begin + 25, $end - $begin - 166);
        $str2 = str_replace("\t", "", $str2);
        $str2 = str_replace("    ", "", $str2);
        $str2 = str_replace("  ", "", $str2);
        $str2 = str_replace("\n\r", "", $str2);
        $str2 = str_replace("\r\n", "", $str2);
        $str2 = str_replace("\n", "", $str2);
        $str2 = str_replace("style=\"color: #000000; text-align: Center\"", "", $str2);
        $str2 = str_replace("style=\"color: #009933; text-align: Center\"", "", $str2);
        $str2 = str_replace("style=\"color: #00bb00\"", "", $str2);
        $str2 = str_replace("style=\"color: #000000\"", "", $str2);
        $str2 = str_replace("style=\"color: #00bb00; text-align: Center\"", "", $str2);
        $str2 = str_replace("width=\"9%\" ", "", $str2);
        $str2 = str_replace("width=\"42%\" ", "", $str2);
        $str2 = str_replace("width=\"8%\" ", "", $str2);
        $str2 = str_replace("width=\"6%\" ", "", $str2);
        $str2 = str_replace("width=\"10%\" ", "", $str2);
        $str2 = str_replace("<td >", "<td>", $str2);
        $rows = explode("</tr>", $str2);
        foreach ($rows as $row) {
            //echo $row."<br>";
            $cells = explode("</td>", $row);
            $i = 0;
            $course_info = array('sisid' => 0, 'name' => '', 'midterm' => '', 'final' => '', 'siwsi' => '', 'mfinal' => '');
            foreach ($cells as $cell) {
                $cell2 = str_replace("<tr>", "", $cell);
                $cell2 = str_replace("<td>", "", $cell2);
                $result .= "[" . htmlentities($cell2) . "]";
                if ($i == 0) {
                    $course_info['sisid'] = $cell2;
                } elseif ($i == 1) {
                    $course_info['name'] = $cell2;
                } elseif ($i == 3) {
                    $course_info['midterm'] = $cell2;
                } elseif ($i == 4) {
                    $course_info['final'] = $cell2;
                } elseif ($i == 5) {
                    $course_info['siwsi'] = $cell2;
                } elseif ($i == 6) {
                    $course_info['mfinal'] = $cell2;
                }
                $i++;
            }
            $result .= "<br>";
        }

        $result .= "Begin: " . $begin . "<br>";
        $result .= "substr: " . $str2;
        return $result;
    }

    /**
     * @Route("/student/grades/current/{systemId?}", name="student_grades_current")
     */
    public function displayStudentCurrentGrades(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $body = '';
        $id = $request->attributes->get('systemId');
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $student = $studentRepository->findOneBy(['systemId' => $id]);
        if ($student) {
            $body .= $this->sis($id);
        }
        //$body = '';
        return $this->render('studentstart.html.twig', [
                    'page_title' => 'Current Semester Grades',
                    'page_content' => $body,
        ]);
    }

    /**
     * @Route("/student/grades/course/{facultyCode?1}", name="student_grades_course")
     */
    public function displayCourseGrades(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $result = '';
        $facultyCode = $request->attributes->get('facultyCode');
        $courseSummaries = [];
        $codes = $this->getCodes();
        foreach ($codes as $code) {
            if ($code[0] == $facultyCode) {
                $courseSummaries[] = $this->groupGrades($code[1], $code[2]);
            }
        }

        $courseSummaries = $this->array_orderby($courseSummaries, 'programCode', SORT_ASC, 'studyYear', SORT_ASC);

        $header = "<table border='1' cellspacing=0 cellpadding=5>";
        $header .= "<thead>";
        $header .= "<th>Program Code</th>";
        $header .= "<th>Study Year</th>";
        $header .= "<th>Group</th>";
        $header .= "<th>Course code</th>";
        $header .= "<th>Course name</th>";
        $header .= "<th>Total</th>";
        $header .= "<th>5-lik</th>";
        $header .= "<th>4-lik</th>";
        $header .= "<th>3-lik</th>";
        $header .= "<th>2-lik</th>";
        $header .= "</thead>";
        $header .= "<tbody>";
        $class = '';

        foreach ($courseSummaries as $courseSummary) {
            if ($courseSummary['2'] == $courseSummary['count']) {
                $class = "class='red'";
            } elseif ($courseSummary['2'] != $courseSummary['count'] && $courseSummary['2'] > 0) {
                $class = "class='orange'";
            } else {
                $class = "";
            }
            $result .= "<tr $class>";
            $result .= "<td>" . $courseSummary['programCode'] . "</td>";
            $result .= "<td>" . $courseSummary['studyYear'] . "</td>";
            $result .= "<td>" . $courseSummary['groupName'] . "</td>";
            $result .= "<td>" . $courseSummary['courseCode'] . "</td>";
            $result .= "<td>" . $courseSummary['courseName'] . "</td>";
            $result .= "<td>" . $courseSummary['count'] . "</td>";
            $result .= "<td>" . $courseSummary['5'] . "</td>";
            $result .= "<td>" . $courseSummary['4'] . "</td>";
            $result .= "<td>" . $courseSummary['3'] . "</td>";
            $result .= "<td>" . $courseSummary['2'] . "</td>";
            $result .= "</tr>";
        }

        $result = $header . $result;
        $result .= "</tbody></table><br><br>&nbsp;&nbsp;";

        //return new Response($result);
        return $this->render('start.html.twig', [
                    'page_title' => 'Current Semester Grades',
                    'page_content' => $result,
        ]);
    }

    public function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    private function groupGrades($majorCode, $courseCode) {
        //http://sis/pd.asp?u_bolum_kod=34&u_akademik_yil=2020-2021&u_donem=G&u_ders_kodu=ECON10019&u_pers=5035
        $result = '';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://sis/pd.asp");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "u_bolum_kod=" . $majorCode . "&u_akademik_yil=2020-2021&u_donem=G&u_ders_kodu=" . $courseCode . "&u_pers=5035");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);

        $marker1 = "ID/Department:</span></b></td>";
        $marker2 = "<table border=\"0\" style=\"font-family: Verdana; font-size: 8pt\" width=\"759\">";
        $begin = strpos($out, $marker1);
        $end = strpos($out, $marker2);
        $str1 = substr($out, $begin + 117, $end - $begin - 147);
//        echo $str1;
        //$str1 = strip_tags($str1);
//        $str1 = str_replace("&nbsp;", " ", $str1);
//        $str1 = str_replace("\t", "", $str1);
//        $str1 = str_replace("    ", "", $str1);
//        $str1 = str_replace("  ", " ", $str1);
        $courseName = $str1;
        //$result .= $courseName;

        $marker1 = "<td valign=middle height=\"1\" width=\"45\" bgcolor=#bbffbb align=\"center\">1</td>";
        $marker2 = "</table>";
        $begin = strpos($out, $marker1);
        $end = strrpos($out, $marker2);
        $str2 = substr($out, $begin - 10, $end - $begin + 6);
        //echo $str2;
        $str2 = strip_tags($str2);
        $str2 = str_replace("&nbsp;", " ", $str2);
        $str2 = str_replace("\t", "", $str2);
        $str2 = str_replace("    ", "", $str2);
        $str2 = str_replace("  ", " ", $str2);

        $str2 = str_replace("\n\r", "#", $str2);
        //$str2 = str_replace("\r\n", "", $str2);
        $str2 = str_replace("\n", "|", $str2);
        $str2 = substr($str2, 0, strlen($str2) - 3);
        //$pattern = '/\s[\w-]+=["]*[#]*\w+["]*/i';
        //$str2 = preg_replace($pattern, "", $str2);
        //$result .= $str2;
        $rows = explode("####", $str2);
        //$result .= "<br><br>";
        $students = [];
        foreach ($rows as $row) {
            $cols = explode("|", $row);
            if (sizeof($cols) > 7) {
                $students[] = new StudentSisGrade([
                    'ordernumber' => trim($cols[1]),
                    'systemid' => substr($cols[2], 0, 6),
                    'fullname' => trim(substr($cols[2], 7)),
                    'midterm' => trim($cols[3]),
                    'final' => trim($cols[4]),
                    'makeup' => trim($cols[5]),
                    'siwsi' => trim($cols[6]),
                    'average' => trim($cols[7]),
                ]);
            }
        }

        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        if (sizeof($students) > 0) {
            $enrolledStudent = $studentRepository->findOneBy(['systemId' => $students[0]->getSystemId()]);
        } else {
            $enrolledStudent = null;
        }
        if ($enrolledStudent) {
            $groupName = $enrolledStudent->getStudentGroup()->getLetterCode();
            $programCode = $enrolledStudent->getStudentGroup()->getStudyProgram()->getSystemId();
            $studyYear = $enrolledStudent->getStudentGroup()->getStudyYear();
        } else {
            $groupName = "";
            $programCode = "";
            $studyYear = "";
        }



        $courseSummary = [
            'programCode' => $programCode,
            'studyYear' => $studyYear,
            'groupName' => $groupName,
            'courseCode' => $courseCode,
            'courseName' => $courseName,
            'count' => sizeof($students),
            '5' => 0,
            '4' => 0,
            '3' => 0,
            '2' => 0
        ];

        foreach ($students as $student) {
            $courseSummary[strval($student->getFivePointSystemGrade())]++;
            //$result .= $student->getFullname() . "," . $student->getAverage() . "<br>";
        }
        return $courseSummary;
    }

    private function getCodes() {
        $codes = [];

        $codes[] = [4, 41, 'CTS10019'];
        $codes[] = [4, 41, 'CTS10039'];
        $codes[] = [4, 42, 'CTS10039'];
        $codes[] = [4, 43, 'CTS10039'];
        $codes[] = [4, 42, 'CTS10059'];
        $codes[] = [4, 41, 'CTS30016'];
        $codes[] = [4, 42, 'CTS30016'];
        $codes[] = [4, 43, 'CTS30016'];
        $codes[] = [4, 42, 'CTS30036'];
        $codes[] = [4, 41, 'CTS30056'];
        $codes[] = [4, 42, 'CTS30056'];
        $codes[] = [4, 43, 'CTS30056'];
        $codes[] = [4, 41, 'CTS40016'];
        $codes[] = [4, 41, 'CTS40036'];
        $codes[] = [4, 42, 'CTS40036'];
        $codes[] = [4, 43, 'CTS40036'];
        $codes[] = [4, 41, 'CTS40056'];
        $codes[] = [4, 42, 'CTS40056'];
        $codes[] = [4, 43, 'CTS40056'];
        $codes[] = [4, 41, 'CTS40076'];
        $codes[] = [4, 42, 'CTS40076'];
        $codes[] = [4, 41, 'CTS48026'];
        $codes[] = [4, 42, 'CTS48026'];
        $codes[] = [3, 34, 'ECON10019'];
        $codes[] = [3, 33, 'ECON10019'];
        $codes[] = [3, 35, 'ECON10019'];
        $codes[] = [3, 32, 'ECON10019'];
        $codes[] = [3, 31, 'ECON10019'];
        $codes[] = [3, 34, 'ECON20019'];
        $codes[] = [3, 33, 'ECON20019'];
        $codes[] = [3, 32, 'ECON20019'];
        $codes[] = [3, 31, 'ECON20019'];
        $codes[] = [3, 35, 'ECON20039'];
        $codes[] = [1, 11, 'ECON29019'];
        $codes[] = [1, 12, 'ECON29019'];
        $codes[] = [2, 22, 'ECON29039'];
        $codes[] = [2, 21, 'ECON29039'];
        $codes[] = [2, 23, 'ECON29059'];
        $codes[] = [3, 34, 'ECON30016'];
        $codes[] = [3, 33, 'ECON30016'];
        $codes[] = [3, 35, 'ECON30016'];
        $codes[] = [3, 32, 'ECON30016'];
        $codes[] = [3, 31, 'ECON30016'];
        $codes[] = [3, 31, 'ECON30036'];
        $codes[] = [3, 32, 'ECON38036'];
        $codes[] = [3, 31, 'ECON38056'];
        $codes[] = [1, 11, 'ECON39016'];
        $codes[] = [1, 12, 'ECON39016'];
        $codes[] = [2, 22, 'ECON39036'];
        $codes[] = [2, 21, 'ECON39036'];
        $codes[] = [2, 23, 'ECON39056'];
        $codes[] = [3, 31, 'ECON40016'];
        $codes[] = [3, 31, 'ECON40036'];
        $codes[] = [3, 32, 'ECON40056'];
        $codes[] = [3, 31, 'ECON40056'];
        $codes[] = [3, 32, 'ECON40076'];
        $codes[] = [1, 11, 'ENS19019'];
        $codes[] = [1, 12, 'ENS19019'];
        $codes[] = [3, 34, 'ENS19039'];
        $codes[] = [3, 33, 'ENS19039'];
        $codes[] = [3, 35, 'ENS19039'];
        $codes[] = [3, 32, 'ENS19039'];
        $codes[] = [3, 31, 'ENS19039'];
        $codes[] = [4, 41, 'ENS19039'];
        $codes[] = [4, 42, 'ENS19039'];
        $codes[] = [4, 43, 'ENS19039'];
        $codes[] = [4, 41, 'ENS19059'];
        $codes[] = [4, 42, 'ENS19059'];
        $codes[] = [4, 43, 'ENS19059'];
        $codes[] = [3, 34, 'ENS29019'];
        $codes[] = [3, 33, 'ENS29019'];
        $codes[] = [3, 35, 'ENS29019'];
        $codes[] = [3, 32, 'ENS29019'];
        $codes[] = [3, 31, 'ENS29019'];
        $codes[] = [1, 13, 'ENS29019'];
        $codes[] = [3, 34, 'ENS29039'];
        $codes[] = [3, 33, 'ENS29039'];
        $codes[] = [3, 32, 'ENS29039'];
        $codes[] = [3, 31, 'ENS29039'];
        $codes[] = [3, 35, 'ENS29059'];
        $codes[] = [4, 41, 'ENS29079'];
        $codes[] = [4, 42, 'ENS29079'];
        $codes[] = [4, 43, 'ENS29079'];
        $codes[] = [4, 41, 'ENS29099'];
        $codes[] = [4, 42, 'ENS29099'];
        $codes[] = [4, 43, 'ENS29099'];
        $codes[] = [4, 41, 'ENS29119'];
        $codes[] = [3, 34, 'ENS29139'];
        $codes[] = [3, 33, 'ENS29139'];
        $codes[] = [3, 32, 'ENS29139'];
        $codes[] = [3, 31, 'ENS29139'];
        $codes[] = [4, 41, 'ENS39016'];
        $codes[] = [4, 41, 'ENS39036'];
        $codes[] = [4, 42, 'ENS39036'];
        $codes[] = [4, 43, 'ENS39036'];
        $codes[] = [3, 35, 'ENS49016'];
        $codes[] = [3, 32, 'ENS49016'];
        $codes[] = [3, 31, 'ENS49016'];
        $codes[] = [3, 34, 'FIN10019'];
        $codes[] = [3, 33, 'FIN10019'];
        $codes[] = [3, 32, 'FIN10019'];
        $codes[] = [3, 31, 'FIN10019'];
        $codes[] = [3, 33, 'FIN20019'];
        $codes[] = [3, 35, 'FIN20019'];
        $codes[] = [3, 34, 'FIN20039'];
        $codes[] = [3, 34, 'FIN30016'];
        $codes[] = [3, 33, 'FIN30016'];
        $codes[] = [3, 35, 'FIN30016'];
        $codes[] = [3, 32, 'FIN30016'];
        $codes[] = [3, 31, 'FIN30016'];
        $codes[] = [3, 34, 'FIN30036'];
        $codes[] = [3, 33, 'FIN30036'];
        $codes[] = [3, 34, 'FIN30056'];
        $codes[] = [3, 33, 'FIN30056'];
        $codes[] = [3, 33, 'FIN38056'];
        $codes[] = [3, 34, 'FIN38076'];
        $codes[] = [3, 35, 'FIN39016'];
        $codes[] = [3, 34, 'FIN40016'];
        $codes[] = [3, 33, 'FIN40016'];
        $codes[] = [3, 35, 'FIN40016'];
        $codes[] = [3, 32, 'FIN40016'];
        $codes[] = [3, 31, 'FIN40016'];
        $codes[] = [3, 34, 'FIN40036'];
        $codes[] = [3, 33, 'FIN40036'];
        $codes[] = [3, 34, 'FIN40056'];
        $codes[] = [3, 33, 'FIN40056'];
        $codes[] = [3, 34, 'FIN40076'];
        $codes[] = [3, 33, 'FIN40076'];
        $codes[] = [3, 33, 'FIN40096'];
        $codes[] = [3, 34, 'FIN40116'];
        $codes[] = [3, 34, 'FIN40136'];
        $codes[] = [3, 33, 'FIN40136'];
        $codes[] = [3, 34, 'FIN48016'];
        $codes[] = [3, 33, 'FIN48016'];
        $codes[] = [3, 35, 'FIN48016'];
        $codes[] = [3, 32, 'FIN48016'];
        $codes[] = [3, 31, 'FIN48016'];
        $codes[] = [2, 23, 'IR10019'];
        $codes[] = [2, 23, 'IR10039'];
        $codes[] = [2, 23, 'IR10059'];
        $codes[] = [2, 23, 'IR20019'];
        $codes[] = [2, 23, 'IR20039'];
        $codes[] = [2, 23, 'IR20059'];
        $codes[] = [2, 23, 'IR28019'];
        $codes[] = [2, 23, 'IR30016'];
        $codes[] = [2, 23, 'IR30036'];
        $codes[] = [2, 23, 'IR38036'];
        $codes[] = [2, 23, 'IR40016'];
        $codes[] = [2, 23, 'IR40036'];
        $codes[] = [2, 23, 'IR40056'];
        $codes[] = [2, 23, 'IR40076'];
        $codes[] = [2, 23, 'IR40096'];
        $codes[] = [1, 13, 'JOUR10019'];
        $codes[] = [1, 13, 'JOUR10039'];
        $codes[] = [1, 11, 'JOUR19019'];
        $codes[] = [1, 12, 'JOUR19019'];
        $codes[] = [1, 13, 'JOUR20019'];
        $codes[] = [1, 13, 'JOUR20039'];
        $codes[] = [1, 13, 'JOUR20059'];
        $codes[] = [1, 13, 'JOUR30016'];
        $codes[] = [1, 13, 'JOUR30036'];
        $codes[] = [1, 13, 'JOUR30056'];
        $codes[] = [1, 13, 'JOUR30076'];
        $codes[] = [1, 13, 'JOUR38016'];
        $codes[] = [2, 23, 'JOUR39016'];
        $codes[] = [1, 13, 'JOUR40016'];
        $codes[] = [1, 13, 'JOUR40036'];
        $codes[] = [1, 13, 'JOUR40056'];
        $codes[] = [1, 13, 'JOUR40076'];
        $codes[] = [1, 13, 'JOUR40096'];
        $codes[] = [1, 13, 'JOUR40116'];
        $codes[] = [1, 13, 'JOUR48036'];
        $codes[] = [2, 23, 'JOUR49016'];
        $codes[] = [3, 34, 'LANG19019'];
        $codes[] = [1, 11, 'LANG19019'];
        $codes[] = [2, 23, 'LANG19019'];
        $codes[] = [2, 22, 'LANG19019'];
        $codes[] = [2, 21, 'LANG19019'];
        $codes[] = [3, 33, 'LANG19019'];
        $codes[] = [3, 35, 'LANG19019'];
        $codes[] = [3, 32, 'LANG19019'];
        $codes[] = [3, 31, 'LANG19019'];
        $codes[] = [4, 41, 'LANG19019'];
        $codes[] = [4, 42, 'LANG19019'];
        $codes[] = [4, 43, 'LANG19019'];
        $codes[] = [1, 12, 'LANG19019'];
        $codes[] = [1, 13, 'LANG19019'];
        $codes[] = [1, 11, 'LANG29019'];
        $codes[] = [1, 12, 'LANG29019'];
        $codes[] = [3, 34, 'LANG29039'];
        $codes[] = [2, 22, 'LANG29039'];
        $codes[] = [2, 21, 'LANG29039'];
        $codes[] = [3, 33, 'LANG29039'];
        $codes[] = [3, 35, 'LANG29039'];
        $codes[] = [3, 32, 'LANG29039'];
        $codes[] = [3, 31, 'LANG29039'];
        $codes[] = [4, 41, 'LANG29039'];
        $codes[] = [4, 42, 'LANG29039'];
        $codes[] = [4, 43, 'LANG29039'];
        $codes[] = [1, 13, 'LANG29039'];
        $codes[] = [2, 22, 'LAW10019'];
        $codes[] = [2, 21, 'LAW10019'];
        $codes[] = [2, 22, 'LAW10039'];
        $codes[] = [2, 21, 'LAW10039'];
        $codes[] = [2, 22, 'LAW10059'];
        $codes[] = [2, 21, 'LAW10059'];
        $codes[] = [2, 22, 'LAW10079'];
        $codes[] = [2, 21, 'LAW10079'];
        $codes[] = [2, 22, 'LAW20019'];
        $codes[] = [2, 21, 'LAW20019'];
        $codes[] = [2, 22, 'LAW20039'];
        $codes[] = [2, 21, 'LAW20039'];
        $codes[] = [2, 22, 'LAW20059'];
        $codes[] = [2, 21, 'LAW20059'];
        $codes[] = [1, 13, 'LAW29019'];
        $codes[] = [2, 23, 'LAW29039'];
        $codes[] = [2, 22, 'LAW30016'];
        $codes[] = [2, 21, 'LAW30016'];
        $codes[] = [2, 22, 'LAW30036'];
        $codes[] = [2, 21, 'LAW30036'];
        $codes[] = [2, 22, 'LAW30056'];
        $codes[] = [2, 21, 'LAW30056'];
        $codes[] = [2, 22, 'LAW30076'];
        $codes[] = [2, 21, 'LAW30076'];
        $codes[] = [2, 21, 'LAW38016'];
        $codes[] = [2, 22, 'LAW38036'];
        $codes[] = [2, 23, 'LAW39016'];
        $codes[] = [2, 22, 'LAW40016'];
        $codes[] = [2, 21, 'LAW40016'];
        $codes[] = [2, 22, 'LAW40036'];
        $codes[] = [2, 21, 'LAW40036'];
        $codes[] = [2, 21, 'LAW40056'];
        $codes[] = [2, 22, 'LAW40076'];
        $codes[] = [2, 21, 'LAW40076'];
        $codes[] = [2, 22, 'LAW40096'];
        $codes[] = [2, 21, 'LAW40096'];
        $codes[] = [2, 22, 'LAW40116'];
        $codes[] = [2, 21, 'LAW48036'];
        $codes[] = [2, 22, 'LAW48056'];
        $codes[] = [2, 23, 'LAW49016'];
        $codes[] = [4, 41, 'LAW49036'];
        $codes[] = [4, 42, 'LAW49036'];
        $codes[] = [4, 43, 'LAW49036'];
        $codes[] = [3, 34, 'MAN10019'];
        $codes[] = [3, 33, 'MAN10019'];
        $codes[] = [3, 35, 'MAN10019'];
        $codes[] = [3, 32, 'MAN10019'];
        $codes[] = [3, 31, 'MAN10019'];
        $codes[] = [3, 35, 'MAN10039'];
        $codes[] = [3, 32, 'MAN20019'];
        $codes[] = [3, 31, 'MAN20019'];
        $codes[] = [3, 35, 'MAN28019'];
        $codes[] = [3, 35, 'MAN30016'];
        $codes[] = [3, 32, 'MAN30016'];
        $codes[] = [3, 31, 'MAN30016'];
        $codes[] = [3, 34, 'MAN30036'];
        $codes[] = [3, 33, 'MAN30036'];
        $codes[] = [3, 35, 'MAN30036'];
        $codes[] = [3, 32, 'MAN30036'];
        $codes[] = [3, 31, 'MAN30036'];
        $codes[] = [3, 32, 'MAN30056'];
        $codes[] = [3, 35, 'MAN38016'];
        $codes[] = [3, 35, 'MAN40016'];
        $codes[] = [3, 31, 'MAN40016'];
        $codes[] = [3, 35, 'MAN40036'];
        $codes[] = [3, 35, 'MAN40056'];
        $codes[] = [3, 35, 'MAN40076'];
        $codes[] = [3, 32, 'MAN49016'];
        $codes[] = [3, 32, 'MAN49036'];
        $codes[] = [4, 43, 'MCT10019'];
        $codes[] = [1, 11, 'MCT19019'];
        $codes[] = [2, 23, 'MCT19019'];
        $codes[] = [2, 22, 'MCT19019'];
        $codes[] = [2, 21, 'MCT19019'];
        $codes[] = [1, 12, 'MCT19019'];
        $codes[] = [1, 13, 'MCT19019'];
        $codes[] = [4, 41, 'MCT20019'];
        $codes[] = [4, 42, 'MCT20019'];
        $codes[] = [4, 43, 'MCT20019'];
        $codes[] = [4, 41, 'MCT20039'];
        $codes[] = [4, 42, 'MCT20039'];
        $codes[] = [4, 42, 'MCT20059'];
        $codes[] = [4, 43, 'MCT20079'];
        $codes[] = [4, 43, 'MCT20099'];
        $codes[] = [4, 41, 'MCT30016'];
        $codes[] = [4, 42, 'MCT30016'];
        $codes[] = [4, 43, 'MCT30016'];
        $codes[] = [4, 43, 'MCT30036'];
        $codes[] = [4, 41, 'MCT30056'];
        $codes[] = [4, 42, 'MCT30056'];
        $codes[] = [4, 43, 'MCT30056'];
        $codes[] = [4, 41, 'MCT40036'];
        $codes[] = [4, 42, 'MCT40036'];
        $codes[] = [4, 43, 'MCT40036'];
        $codes[] = [4, 43, 'MCT40056'];
        $codes[] = [4, 42, 'MCT40076'];
        $codes[] = [4, 43, 'MCT40076'];
        $codes[] = [4, 43, 'MCT48036'];
        $codes[] = [1, 11, 'PHS10019'];
        $codes[] = [1, 12, 'PHS10039'];
        $codes[] = [1, 12, 'PHS10059'];
        $codes[] = [2, 23, 'PHS19019'];
        $codes[] = [1, 13, 'PHS19019'];
        $codes[] = [1, 12, 'PHS20019'];
        $codes[] = [1, 11, 'PHS20039'];
        $codes[] = [1, 11, 'PHS20059'];
        $codes[] = [1, 11, 'PHS20079'];
        $codes[] = [1, 12, 'PHS20099'];
        $codes[] = [3, 34, 'PHS29019'];
        $codes[] = [3, 33, 'PHS29019'];
        $codes[] = [3, 35, 'PHS29019'];
        $codes[] = [3, 32, 'PHS29019'];
        $codes[] = [3, 31, 'PHS29019'];
        $codes[] = [1, 11, 'PHS30016'];
        $codes[] = [1, 11, 'PHS30036'];
        $codes[] = [1, 11, 'PHS30056'];
        $codes[] = [1, 11, 'PHS30076'];
        $codes[] = [1, 12, 'PHS30096'];
        $codes[] = [1, 12, 'PHS30116'];
        $codes[] = [1, 12, 'PHS30136'];
        $codes[] = [1, 12, 'PHS30156'];
        $codes[] = [1, 11, 'PHS38016'];
        $codes[] = [1, 12, 'PHS38036'];
        $codes[] = [1, 13, 'PHS39016'];
        $codes[] = [1, 11, 'PHS40016'];
        $codes[] = [1, 11, 'PHS40036'];
        $codes[] = [1, 11, 'PHS40056'];
        $codes[] = [1, 11, 'PHS40076'];
        $codes[] = [1, 11, 'PHS40096'];
        $codes[] = [1, 12, 'PHS40096'];
        $codes[] = [1, 12, 'PHS40116'];
        $codes[] = [1, 12, 'PHS40136'];
        $codes[] = [1, 12, 'PHS40156'];
        $codes[] = [1, 12, 'PHS40176'];
        $codes[] = [1, 12, 'PHS40196'];
        $codes[] = [1, 12, 'PHS40216'];
        $codes[] = [1, 11, 'PHS48036'];
        $codes[] = [3, 34, 'SSC19019'];
        $codes[] = [1, 11, 'SSC19019'];
        $codes[] = [2, 23, 'SSC19019'];
        $codes[] = [3, 33, 'SSC19019'];
        $codes[] = [3, 32, 'SSC19019'];
        $codes[] = [3, 31, 'SSC19019'];
        $codes[] = [4, 41, 'SSC19019'];
        $codes[] = [4, 42, 'SSC19019'];
        $codes[] = [4, 43, 'SSC19019'];
        $codes[] = [1, 12, 'SSC19019'];
        $codes[] = [1, 13, 'SSC19019'];
        $codes[] = [1, 11, 'SSC19039'];
        $codes[] = [1, 11, 'SSC29019'];
        $codes[] = [1, 12, 'SSC29019'];
        $codes[] = [1, 12, 'SSC29039'];
        $codes[] = [2, 22, 'SSC29059'];
        $codes[] = [2, 21, 'SSC29059'];
        $codes[] = [1, 11, 'SSC49016'];
        $codes[] = [2, 22, 'SSC49036'];
        $codes[] = [2, 21, 'SSC49036'];


        return $codes;
    }

}
