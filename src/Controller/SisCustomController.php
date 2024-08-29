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
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class SisCustomController extends AbstractController {
    
    private $systemEventManager;
    private $systemInfoManager;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/siscustom/index", name="siscustom_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/siscustom/listgradesbygroup/{letterCode?}", name="siscustom_listgradesbygroup")
     */
    public function listGradesByGroup(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $letterCode = $request->attributes->get('letterCode');
        $semesters = [2];
        $content = '';
        $repository = $this->getDoctrine()->getRepository(Group::class);
        if ($letterCode) {
            $groups = $repository->findBy(['letterCode' => $letterCode]);
        } else {
            $groups = $repository->findAll();
        }
        //$group = $repository->findOneBy(['letterCode' => $letterCode]);

        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);

        $content = "<table id='mainTable' class='table table-bordered table-striped'>";
        $content .= "<thead><tr><th>Topar</th><th>Ýyl</th><th>ID</th><th>Student</th>";
        $content .= "<th>Course Code</th><th>Course Title</th>";
        $content .= "<th>Midterm</th><th>SIWSI</th><th>FINAL</th><th>MAKE-UP</th><th>AVERAGE</th>";
        $content .= "</tr></thead>";
        $content .= "<tbody>";
        $beginYear = $this->systemInfoManager->getCurrentCommencementYear();
        $endYear = $beginYear + 4;
        foreach ($groups as $group) {
            if ($group->getGraduationYear() > $beginYear && $group->getGraduationYear() < $endYear && $group->getStatus() == 1) {
//                echo $group->getLetterCode()."<br>";
                $i = 1;
                $students = $group->getStudents();
                foreach ($students as $student) {
                    if ($i < 40) {
                        //echo $taughtCourse->getFullName()."- "<br>";
                        $courses = $this->getGrades($student->getSystemId());
                        foreach ($courses as $course) {
                            if ($course['sisid'] != '') {
                                $content .= "<tr>";
                                $content .= "<td>" . $group->getLetterCode() . "</td>";
                                $content .= "<td>" . $group->getStudyYear() . "</td>";
                                $content .= "<td>" . $student->getSystemId() . "</td>";
                                $content .= "<td>" . $student->getFullname() . "</td>";
                                $content .= "<td>" . $course['sisid'] . "</td>";
                                $content .= "<td>" . $course['name'] . "</td>";
                                $content .= "<td>" . $course['midterm'] . "</td>";
                                $content .= "<td>" . $course['siwsi'] . "</td>";
                                $content .= "<td>" . $course['final'] . "</td>";
                                $content .= "<td>" . $course['mfinal'] . "</td>";
                                $content .= "<td>[" . $course['average'] . "]</td>";
                                $content .= "</tr>";
                            }
                        }
                        $i++;
                    }
                }
            }
        }
        $content .= "</tbody>";
        $content .= "</table>";

        return $this->render('custom/index.html.twig', [
                    'controller_name' => 'CustomController',
                    'content' => $content,
        ]);
    }

    private function getGrades($studentId) {
        $result = '';
        $result .= "<h3>SEMESTER GRADES</h3>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sis/campus.asp?action=semestergrades&sisid=" . $studentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        curl_close($ch);
//        echo "--ANALYSIS--";
//        echo $out;
//        echo "--END--";
        $begin = strpos($out, "<tbody>");
        $end = strpos($out, "</tbody>");
        $str2 = substr($out, $begin + 7, $end - $begin - 8);
        $str2 = str_replace("\t", "", $str2);
        $str2 = str_replace("    ", "", $str2);
        $str2 = str_replace("  ", "", $str2);
        $str2 = str_replace("\n\r", "", $str2);
        $str2 = str_replace("\r\n", "", $str2);
        $str2 = str_replace("\n", "", $str2);
//        $str2 = str_replace("style=\"color: #000000; text-align: Center\"", "", $str2);
//        $str2 = str_replace("style=\"color: #009933; text-align: Center\"", "", $str2);
//        $str2 = str_replace("style=\"color: #00bb00\"", "", $str2);
//        $str2 = str_replace("style=\"color: #000000\"", "", $str2);
//        $str2 = str_replace("style=\"color: #00bb00; text-align: Center\"", "", $str2);
//        $str2 = str_replace("width=\"9%\" ", "", $str2);
//        $str2 = str_replace("width=\"42%\" ", "", $str2);
//        $str2 = str_replace("width=\"8%\" ", "", $str2);
//        $str2 = str_replace("width=\"6%\" ", "", $str2);
//        $str2 = str_replace("width=\"10%\" ", "", $str2);
        $str2 = str_replace("<td >", "<td>", $str2);
//        echo "--CLEANED--".$str2."--CLEANED--";
        $rows = explode("</tr>", $str2);
        $courses = array();
        foreach ($rows as $row) {
            //echo $row."<br>";
            $cells = explode("</td>", $row);
            $i = 0;
            $course_info = array('sisid' => 0, 'name' => '', 'credit' => '', 'midterm' => '', 'final' => '', 'siwsi' => '', 'mfinal' => '', 'average' => '');
            foreach ($cells as $cell) {
                $cell2 = str_replace("<tr>", "", $cell);
                $cell2 = str_replace("<td>", "", $cell2);
                //echo "[".htmlentities($cell2)."]";
                if ($i == 0) {
                    $course_info['sisid'] = $cell2;
                } elseif ($i == 1) {
                    $course_info['name'] = $cell2;
                } elseif ($i == 2) {
                    $course_info['credit'] = $cell2;
                } elseif ($i == 3) {
                    $course_info['midterm'] = $cell2;
                } elseif ($i == 4) {
                    $course_info['final'] = $cell2;
                } elseif ($i == 5) {
                    $course_info['siwsi'] = $cell2;
                } elseif ($i == 6) {
                    $course_info['mfinal'] = $cell2;
                } elseif ($i == 7) {
                    $course_info['average'] = $cell2;
                }
                $i++;
            }

            $courses[] = $course_info;

            //echo "<br>";
        }
        //$result .= $out;
        return $courses;
    }

}
