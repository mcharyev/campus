<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Doctrine\DBAL\Driver\Connection;
use App\Enum\ClassroomTypeEnum;
use App\Enum\UserTypeEnum;
use App\Entity\Teacher;
use App\Entity\EnrolledStudent;
use App\Service\SystemEventManager;
use App\Service\CourseDaysManager;
use App\Service\SystemInfoManager;

/**
 * Controller used to manage index page.
 * See https://symfony.com/doc/current/cookbook/security/form_login_setup.html.
 *
 */
class MainController extends AbstractController {

    use TargetPathTrait;

    private $systemEventManager;
    private $courseDaysManager;
    private $systemInfoManager;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager, CourseDaysManager $courseDaysManager) {
        $this->systemEventManager = $systemEventManager;
        $this->courseDaysManager = $courseDaysManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/", name="app_index")
     */
    public function index(Request $request, Security $security, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response {
        $user = $this->getUser();
        if ($user == null) {
            $userfullname = "";
        } else {
            $userfullname = $user->getFullname();
        }
        $body = "";
        if ($user->getType() == UserTypeEnum::USER_TEACHER) {
            $body = $this->buildTeacherContent();
        } elseif ($user->getType() == UserTypeEnum::USER_STUDENT) {
            $body = $this->buildStudentContent();
            return $this->render('studentstart.html.twig', [
                        // last username entered by the user (if any)
                        'page_title' => 'Welcome!',
                        'page_content' => $body,
            ]);
        } elseif ($user->getType() == UserTypeEnum::USER_DISABLED) {
            $body = "Your account is suspended";
        } elseif ($user->getType() == UserTypeEnum::USER_FACILITIES) {
            return new RedirectResponse($router->generate('hr_movement_record'));
        } elseif ($user->getType() == UserTypeEnum::USER_HR) {
            return new RedirectResponse($router->generate('hr_employee'));
        } elseif ($user->getType() == UserTypeEnum::USER_LIBRARY) {
            return new RedirectResponse($router->generate('library_libraryitem'));
        } elseif ($user->getType() == UserTypeEnum::USER_ADMINISTRATION) {
            return new RedirectResponse($router->generate('electronic_document'));
        }

        return $this->render('start.html.twig', [
                    // last username entered by the user (if any)
                    'page_title' => 'Welcome!',
                    'page_content' => $body,
        ]);
    }

    private function buildTeacherContent(): ?string {
        $result = "";
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        if ($teacher) {
            $year = $this->systemInfoManager->getCurrentCommencementYear();
            $semester = $this->systemInfoManager->getCurrentSemester();
            $faculty = $teacherRepository->getManagedFaculty(['teacher_id' => $teacher->getId()]);
            $department = $teacherRepository->getManagedDepartment(['teacher_id' => $teacher->getId()]);
            $result .= "<h5>My links</h5><ul>";
            $result .= "<li><a href='/faculty/scheduledisplay/teacher/" . $teacher->getId() . "'>My schedule</a></li>";
            $result .= "<li><a href='/faculty/attendance/teacher/" . $teacher->getId() . "'>My absences</a></li>";
            $result .= "<li>My workload register: ";
            $result .= "<a href='/faculty/teacherjournalnew/" . $teacher->getId() . "/0/" . $year . "/1'>Semester 1</a> | ";
            $result .= "<a href='/faculty/teacherjournalnew/" . $teacher->getId() . "/0/" . $year . "/2'>Semester 2</a> | ";
            $result .= "<a href='/faculty/teacherjournalnew/" . $teacher->getId() . "/0/" . $year . "/3'>Semester 3</a> | ";
            $result .= "<a href='/faculty/teacherworkreport/" . $teacher->getId() . "/0/" . $year . "/" . $semester . "'>Monthly report</a></li>";
            $result .= "<li><a href='/faculty/taughtcourse/teacher/" . $teacher->getId() . "'>My courses</a> - Exam covers, Exam attendance sheets</li>";
            if ($this->isGranted("ROLE_DEAN")) {
                if ($faculty) {
                    $result .= "<li><a href='/faculty/attendance/faculty/" . $faculty->getId() . "'>Faculty student absences</a></li>";
                    $result .= "<li><a href='/faculty/group/links/'>Faculty group links</a></li>";
                    $result .= "<li><a href='/faculty/attendance/totalabsences/faculty/" . $faculty->getId() . "'>Faculty total absences report</a> | ";
                    $result .= "<a href='/faculty/attendance/facultyreport/" . $faculty->getId() . "'>Monthly consolidated absence report</a> | ";
                    $result .= "<a href='/faculty/attendance/facultydailyreport/" . $faculty->getId() . "'>Daily absence report</a></li>";
                    $result .= "<li><a href='/faculty/taughtcourse/faculty/" . $faculty->getId() . "'>Faculty courses</a></li>";
                    $result .= "<li><a href='/grading/transcriptcourse/report/" . $year . "/" . $semester . "/" . $faculty->getSystemId() . "'>Faculty grades report</a>";
                    $result .= "<li><a href='/hr/report/dailyreport/'>Employee report</a></li>";
                }
            }
            if ($this->isGranted("ROLE_DEPARTMENTHEAD")) {
                if ($department) {
                    if ($department->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                        $semester = $this->systemInfoManager->getCurrentTrimester();
                    }
                    $result .= "<li><a href='/faculty/attendance/department/" . $department->getId() . "'>Department student absences</a></li>";
//                    $result .= "<li><a href='/faculty/attendance/totalabsences/department/" . $department->getId() . "'>Department total absences report</a></li>";
                    $result .= "<li><a href='/faculty/taughtcourse/department/" . $department->getId() . "'>Department courses</a></li>";
                    $result .= "<li><a href='/faculty/teacher/department/" . $department->getId() . "'>Department teachers</a></li>";
                    $result .= "<li><a href='/faculty/departmentworksets/" . $department->getSystemId() . "/" . $year . "'>Department worksets</a></li>";
                    $result .= "<li>Department work report: ";
                    $result .= "<a href='/faculty/departmentworkreport/" . $department->getId() . "/" . $year . "/1'>Semester 1</a> | ";
                    $result .= "<a href='/faculty/departmentworkreport/" . $department->getId() . "/" . $year . "/2'>Semester 2</a> | ";
                    $result .= "<a href='/faculty/departmentworkreport/" . $department->getId() . "/" . $year . "/3'>Semester 3</a>";
                    $result .= "<li><a href='/hr/report/dailyreport/'>Employee report</a></li>";
                }
            }
            if ($this->isGranted("ROLE_SPECIALIST")) {
                $result .= "<li><a href='/faculty/attendance/faculty/1'>SHS faculty absences</a></li>";
                $result .= "<li><a href='/faculty/attendance/faculty/2'>ILIR faculty absences</a></li>";
                $result .= "<li><a href='/faculty/attendance/faculty/3'>IEM faculty absences</a></li>";
                $result .= "<li><a href='/faculty/attendance/faculty/4'>LLD faculty absences</a></li>";
                $result .= "<li><a href='/faculty/attendance/faculty/5'>IT faculty absences</a></li>";
                $result .= "<li><a href='/faculty/attendance/todayclasses/'>Todays teacher attendances</a></li>";
            }
            if ($this->isGranted("ROLE_LIBRARY")) {
                $result .= "<li><a href='/library/libraryitem'>Library</a></li>";
            }
            $result .= "</ul>";
            $result .= "<h4>Your courses</h4><ul>";
            $courses = $teacher->getTaughtCourses();
            //$result .= $teacher->getSystemId() . ":" . $teacher->getId();
            foreach ($courses as $course) {
                if ($course->getSemester() == $this->systemInfoManager->getCurrentSemester()) {
                    $groupLetterCodes = $this->courseDaysManager->getGroupNamesFromIds($course->getDataField("groups"));
                    $result .= "<li><a href='/faculty/attendancejournal/course/" . $course->getId() . "'>" . $course->getTeacherName() . " - " . $course->getNameEnglish() . " - " . $groupLetterCodes .
                            "</a> </li>";
                }
            }

            $result .= "</ul>";
        } else {
            $result .= "Teacher not found";
        }
        return $result;
    }

    private function buildStudentContent(): ?string {
        $result = "";
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $student = $studentRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
        if ($student) {
            $result .= "<h5>My links</h5><ul>";
            $result .= "<li><a href='/faculty/scheduledisplay/group/" . $student->getStudentGroup()->getId() . "'>My schedule</a></li>";
            $result .= "</ul>";
            $result .= "<h4>Your courses</h4><ul>";
        } else {
            $result .= "Student not found";
        }
        return $result;
    }

    /**
     * @Route("/valuepairs/{table}/{valuefield}/{displayfield}/{orderfield}", name="app_valuepairs")
     */
    public function valuepairs(Request $request, Connection $connection) {
        try {
            $table = $request->attributes->get('table');
            $value_field = $request->attributes->get('valuefield');
            $display_field = $request->attributes->get('displayfield');
            $order_field = $request->attributes->get('orderfield');
            if ($table == "teacher")
                $sql = "SELECT id AS Value, CONCAT(lastname,' ',firstname) AS DisplayText FROM teacher ORDER BY lastname ASC";
            elseif ($table == "enrolled_student")
                $sql = "SELECT id AS Value, CONCAT(lastname_turkmen,' ',firstname_turkmen) AS DisplayText FROM $table ORDER BY $order_field";
            elseif ($table == "study_program")
                $sql = "SELECT id AS Value, CONCAT(name_english,' ',approval_year,' ',letter_code) AS DisplayText FROM $table ORDER BY $order_field";
            elseif ($table == "group")
                $sql = "SELECT id AS Value, CONCAT(letter_code,' - ',system_id) AS DisplayText FROM `group` ORDER BY $order_field";
            elseif ($table == "employee")
                $sql = "SELECT system_id AS Value, CONCAT(lastname,' ',firstname) AS DisplayText FROM employee ORDER BY lastname ASC";
            elseif ($table == "user")
                $sql = "SELECT id AS Value, CONCAT(lastname,' ',firstname) AS DisplayText FROM user ORDER BY lastname ASC";
            elseif ($table == "userwithid")
                $sql = "SELECT id AS Value, CONCAT(lastname,' ',firstname,' - ',system_id) AS DisplayText FROM user ORDER BY lastname ASC";
            elseif ($table == "teacher_work_item")
                $sql = "SELECT id AS Value, CONCAT(id,' - ',title) AS DisplayText FROM teacher_work_item ORDER BY id ASC";
            else
                $sql = "SELECT $value_field AS Value, $display_field AS DisplayText FROM $table ORDER BY $order_field";
            //$sql = "SELECT system_id AS Value, name_english AS DisplayText FROM faculty ORDER BY faculty.id ASC";

            $results = $connection->fetchAll($sql);

            //Add all records to an array
            $rows = array();
            $blank_array = array('Value' => '0', 'DisplayText' => '');
            $rows[] = $blank_array;
            foreach ($results as $result) {
                $rows[] = $result;
            }
            //Return result
            $result_array = [
                'Result' => "OK",
                'Options' => $rows
            ];
        } catch (Exception $ex) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $ex->getMessage()
            ];
        }
        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/enumerator/{enumerator}", name="app_enumerator")
     */
    public function enumerate(Request $request, Connection $connection) {
        try {
            $enumerator = $request->attributes->get('enumerator');

//            $class_name = $enumerator . "Enum";
//            $r = new \ReflectionClass($class_name);
//            $instance = $r->newInstanceWithoutConstructor();
            $types = ClassroomTypeEnum::getNameValuePairs();
//            $types = call_user_func("ClassroomTypeEnum::getNameValuePairs");
            //Add all records to an array

            $blank_array = array('Value' => '0', 'DisplayText' => '');
            $rows[] = $blank_array;
            foreach ($types as $key => $value) {
                $rows[] = array('Value' => $key, 'DisplayText' => $value);
            }
            //Return result
            $result_array = [
                'Result' => "OK",
                'Options' => $rows
            ];
        } catch (Exception $ex) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $ex->getMessage()
            ];
        }
        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
