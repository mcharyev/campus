<?php

namespace App\Controller\LMS;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Entity\TaughtCourse;
use App\Entity\ProgramCourse;
use App\Entity\Department;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

/**
 * Controller used to manage IUHDPlus page.
 *
 */
class IuhdplusController extends AbstractController {

    use TargetPathTrait;

    private $webroot;
    private $systemEventManager;
    private $systemInfoManager;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->webroot = "C:\\campus\\www\\iuhdplus\\";
    }

    /**
     * @Route("/iuhdplus/test", name="iuhdplus_test")
     */
    public function index(Request $request, Security $security, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response {

        $body = "This is content";
//return new Response($body);
        return $this->render('/iuhdplus.html.twig', [
// last username entered by the user (if any)
                    'page_title' => 'Welcome!',
                    'page_content' => $body,
        ]);
    }

    /**
     * @Route("/iuhdplus", name="iuhdplus_index")
     * @Route("/iuhdplus/departments", name="iuhdplus_departments")
     */
    public function listdepartments() {
        $show_instructors = false;
        $body = "<p>Please select a department below to find your course</p>";
        $body .= "<div class='container'>";
        $z = 0;
        $opened = true;
        $body .= "<h4>Departments</h4>";
        $repository = $this->getDoctrine()->getRepository(Department::class);
        $departments = $repository->findAll();
        foreach ($departments as $department) {
            $body .= "<div class='row'>";
//$body .="<ul>";
            $body .= "<div class='col-4'>";
            $body .= "<li><a href='/iuhdplus/department/" . $department->getSystemId() . "' class='alert-link'>" . $department->getNameEnglish() . "</a></li>";
            if ($show_instructors) {
                $teachers = $department->getTeachers();
                $body .= "<ul>";
                foreach ($teachers as $teacher) {
                    $body .= "<li><a href='/iuhdplus/instructor/" . $teacher->getId() . "'>" . $teacher->getFullname() . "</a></li>";
                }
                $body .= "</ul>";
            }
            $body .= "</div>";
//$body .="</ul>";
            $body .= "</div>";
        }
        $body .= "</div>";

//return new Response($body);
        return $this->render('iuhdplus/index.html.twig', [
// last username entered by the user (if any)
                    'page_title' => 'IUHD+',
                    'page_content' => $body,
        ]);
    }

    /**
     * @Route("/iuhdplus/department/{departmentSystemId}", name="iuhdplus_department")
     */
    public function department(Request $request) {
        $departmentSystemId = $request->attributes->get('departmentSystemId');
        $repository = $this->getDoctrine()->getRepository(Department::class);
        $department = $repository->findOneBy(['systemId' => $departmentSystemId]);
        $body = "";
        if ($department) {
            $body .= "<h4>Department: " . $department->getNameEnglish() . "</h4>";
//        if (is_department_chair('', $department_numbercode)) {
//            $body .="<span style='font-size: 17px; color:green;font-weight:bold;'>Logged in as " . $_SESSION['user_username'] . "</span> <br>";
//            $body .="<span style='font-size: 17px; color:red;font-weight:bold;'>xls files are not accepted. Please Save as Course data template files for each course as 'xlsx' file.</span>";
//        }
            $check_year = $this->systemInfoManager->getCurrentGraduationYear() + 4;
            $years = [1, 2, 3, 4];
            $courses = $department->getTaughtCourses();

            foreach ($years as $year) {
                $semester = ($year - 1) * 2 + 1;
                $body .= "<div class=\"container\">";
                $body .= "<div><h4>$year year</h4></div>";
                $body .= "<div class=\"row\">";
                $body .= "<div class='col'>";
                $body .= "<div><b>Fall semester (" . $semester . ")</b></div>";
                $body .= "<ol>";
                foreach ($courses as $course) {
                    $group_exists = true;
                    $departmentLetterCode = $course->getDepartmentLetterCode();
                    if ($group_exists) {
//$body .="result: ".$group_exists." ".$course['course_note']."<br>";
                        if ($course->getCourseCurriculumSemester() == $semester) {
//echo $course['course_code']."<br>";
                            $code = $course->getCourseCode();
                            $id = $course->getId();
                            if (strlen($code) > 2) {
                                if (file_exists($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . $code . '.xlsx'))
                                    $body .= "<li><a href='/iuhdplus/course/" . $id . "/home' class='alert-link'>" . strtoupper($code) . " - " . $course->getNameEnglish() . "</a> - " . " " . " - <a href='instructor/'>" . $course->getTeacher()->getFullname() . "</a>";
                                else {
                                    $body .= "<li>" . strtoupper($code) . " - " . $course->getNameEnglish() . "- " . " " . " - <a href='instructor/'>" . $course->getTeacher()->getFullname() . "</a>";
                                }
                            } else
                                $body .= "<li>" . $course->getNameEnglish() . " - <a href='instructor/'>" . $course->getTeacher()->getFullname() . "</a>";
                            if ($this->isGranted("ROLE_SPECIALIST")) {
                                $body .= " <a href='/faculty/taughtcourse/edit/" . $course->getId() . "' class='alert-link'>" . $this->getButton('edit') . "</a>";
                            }
                            $body .= "</li>";

                            if ($this->isGranted("ROLE_SPECIALIST")) {
                                if (file_exists($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/")) {
                                    if (!file_exists($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . $code . '.xlsx'))
                                        $body .= $this->uploadForm($course);
                                    else
                                        $body .= "<a href='#' onclick=\"perform('" . $course->getCourseCode() . "','" . $course->getId() . "','deletecoursedata');\">" . $this->getButton('delete') . "</a>";
                                    //echo 'course directory exists';								
                                } else {
                                    $body .= "<span style='color:red;'>course directory does not exist</span> <a href=\"javascript:create('" . $course->getId() . "');\">Create directory</a>";
                                }
                                $body .= "<div id='div" . $course->getId() . "' name='div" . $course->getId() . "'></div>";
                            }
                        }
                    }
                }
                $body .= "</ol>";
                $body .= "</div>";

                $semester = ($year - 1) * 2 + 2;
                $body .= "<div class='col'>";
                $body .= "<div><b>Spring semester (" . $semester . ")</b></div>";
                $body .= "<ol>";
                foreach ($courses as $course) {
                    $group_exists = true;
                    $studentGroups = $course->getStudentGroups();
                    $departmentLetterCode = $course->getDepartmentLetterCode();
                    if ($group_exists) {
//$body .="result: ".$group_exists." ".$course['course_note']."<br>";
                        if ($course->getCourseCurriculumSemester() == $semester) {
//echo $course['course_code']."<br>";
                            $code = $course->getCourseCode();
                            $id = $course->getId();
                            if (strlen($code) > 2) {
                                if (file_exists($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . $code . '.xlsx'))
                                    $body .= "<li><a href='/iuhdplus/course/" . $id . "/home' class='alert-link'>" . strtoupper($code) . " - " . $course->getNameEnglish() . "</a> - " . " " . " - <a href='instructor/'>" . $course->getTeacher()->getFullname() . "</a>";
                                else {
                                    $body .= "<li>" . strtoupper($code) . " - " . $course->getNameEnglish() . " - " . " " . " - <a href='instructor/'>" . $course->getTeacher()->getFullname() . "</a>";
                                }
                            } else
                                $body .= "<li>" . $course->getNameEnglish() . " - <a href='instructor/'>" . $course->getTeacher()->getFullname() . "</a>";
                            if ($this->isGranted("ROLE_SPECIALIST")) {
                                $body .= " <a href='/faculty/taughtcourse/edit/" . $course->getId() . "' class='alert-link'>" . $this->getButton('edit') . "</a>";
                            }
                            $body .= "</li>";

                            if ($this->isGranted("ROLE_SPECIALIST")) {
                                if (file_exists($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/")) {
                                    if (!file_exists($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . $code . '.xlsx'))
                                        $body .= $this->uploadForm($course);
                                    else
                                        $body .= "<a href='#' onclick=\"perform('" . $course->getCourseCode() . "','" . $course->getId() . "','deletecoursedata');\">" . $this->getButton('delete') . "</a>";
                                    //echo 'course directory exists';								
                                } else {
                                    $body .= "<span style='color:red;'>course directory does not exist</span> <a href=\"javascript:create('" . $course->getId() . "');\">Create directory</a>";
                                }
                                $body .= "<div id='div" . $course->getId() . "' name='div" . $course->getId() . "'></div>";
                            }
                        }
                    }
                }
                $body .= "</ol>";
                $body .= "</div>";

                $body .= "</div></div>";
            }
        }

//return new Response($body);
        return $this->render('iuhdplus/index.html.twig', [
// last username entered by the user (if any)
                    'page_title' => 'IUHD+',
                    'page_content' => $body,
        ]);
    }

    private function uploadForm(TaughtCourse $course) {
        $fresult = "<div>";
        $fresult .= "<input name='file" . $course->getId() . "' type='file' multiple />";
        $fresult .= "<br><a href='#' onclick=\"perform('" . $course->getCourseCode() . "','" . $course->getId() . "','uploadcoursedata');\">" . $this->getButton('upload') . "</a></div>";
        return $fresult;
    }

    /**
     * @Route("/iuhdplus/course/{programCourseCode}/home", name="iuhdplus_course_home")
     */
    public function courseHome(Request $request) {
        $courseId = $request->attributes->get('programCourseCode');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourse = $taughtCourseRepository->find($courseId);
        $programCourseCode = $taughtCourse->getCourseCode();
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $programCourse = $programCourseRepository->findOneBy(['letterCode' => $programCourseCode]);
        $departmentLetterCode = $taughtCourse->getDepartmentLetterCode();
        $coursePath = $this->webroot . 'courses/' . $departmentLetterCode . "/" . $programCourseCode . "/";
        $spreadsheet = IOFactory::load($coursePath . $programCourseCode . '.xlsx');
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        if (file_exists($coursePath . $programCourseCode . '.jpg')) {
            $image = "<figure class='figure' style='float:right; margin:10px; border:1px solid gray;'><img src='http://iuhdplus/courses/" . $departmentLetterCode . "/" . $programCourseCode . "/" . $programCourseCode . ".jpg' class='figure-img img-fluid rounded'></figure>";
        } else {
            $image = '';
        }
        $editable = false;
        if ($this->isGranted("ROLE_SPECIALIST")) {
            $editable = true;
        }
        $body = '';
        $course = [
            'programCourse' => $programCourse,
            'taughtCourse' => $taughtCourse,
            'code' => $programCourse->getLetterCode(),
            'faculty' => $sheet->getCell('B1')->getValue(),
            'department' => $sheet->getCell('B3')->getValue(),
            'title' => $sheet->getCell('B5')->getValue(),
            'major' => $sheet->getCell('B7')->getValue(),
            'instructor' => $sheet->getCell('B13')->getValue(),
            'year' => $sheet->getCell('B17')->getValue(),
            'semester' => $sheet->getCell('B18')->getValue(),
            'type' => $sheet->getCell('B24')->getValue(),
            'description' => $sheet->getCell('B27')->getValue(),
            'objectives' => str_replace(array("\n", "\r"), "<br>", $sheet->getCell('B28')->getValue()),
            'methods' => $sheet->getCell('B29')->getValue(),
            'assessment' => str_replace(array("\n", "\r"), "<br>", $sheet->getCell('B30')->getValue()),
            'officehours' => $sheet->getCell('B33')->getValue(),
            'room' => $sheet->getCell('B34')->getValue(),
            'email' => $sheet->getCell('B35')->getValue(),
            'language' => $sheet->getCell('B36')->getValue(),
            'image' => $image
        ];

//return new Response($body);
        return $this->render('iuhdplus/course.html.twig', [
                    'action' => 'home',
                    'page_title' => 'IUHD+',
                    'page_content' => $body,
                    'course' => $course,
                    'editable' => $editable,
                    'button' => $this->getButton('edit')
        ]);
    }

    /**
     * @Route("/iuhdplus/course/{programCourseCode}/plan", name="iuhdplus_course_plan")
     */
    public function coursePlan(Request $request) {
        $courseId = $request->attributes->get('programCourseCode');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourse = $taughtCourseRepository->find($courseId);
        $programCourseCode = $taughtCourse->getCourseCode();
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $programCourse = $programCourseRepository->findOneBy(['letterCode' => $programCourseCode]);
        $departmentLetterCode = $taughtCourse->getDepartmentLetterCode();
        $coursePath = $this->webroot . 'courses/' . $departmentLetterCode . "/" . $programCourseCode . "/";
        $spreadsheet = IOFactory::load($coursePath . $programCourseCode . '.xlsx');
//$spreadsheet->setActiveSheetIndex(1);
//$sheet = $spreadsheet->getActiveSheet();
//$spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getSheetByName('Sessions');


//            $body = 'Hello' . $sheet->getCell('A1')->getValue()."::".$sheet->getCellByColumnAndRow(1, 1)->getValue();
        $body = '';
        $course = [
            'title' => $taughtCourse->getNameEnglish(),
            'programCourse' => $programCourse,
            'taughtCourse' => $taughtCourse,
            'code' => $programCourse->getLetterCode(),
            'id' => $taughtCourse->getId()
        ];

        $topic = 1;
        $subtopic = 1;
        $ptopic = 1;
        $source = 1;
        $topicChangeable = false;
        $lectureTopics = "";
        $practiceTopics = "";
        if (strlen($taughtCourse->getDataField('lecture_topics')) < 30) {
            $topicChangeable = true;
        }
        for ($row = 1; $row < 2000; $row++) {
            $type = $sheet->getCellByColumnAndRow(1, $row)->getValue();
//$body .= "type:" . $type;
            if (strlen($type) == 0) {
                break;
            }
            $value = htmlentities($sheet->getCellByColumnAndRow(2, $row)->getValue());
            $value2 = htmlentities($sheet->getCellByColumnAndRow(3, $row)->getValue());
//$value = addslashes($value);
            $cell_address = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1) . $row;
//$file =  $sheet->getCellByColumnAndRow(2, $row)->getValue();
            $params = array('sheet' => 'Sessions', 'course_code' => $programCourseCode, 'cell_address' => $cell_address, 'row' => $row, 'type' => $type, 'value' => $value, 'value2' => $value2);
            if ($type == "P-TOPIC" && $ptopic == 1) {
                $body .= "<tr><td colspan=2><h4>PRACTICAL LESSONS</h4></td></tr>";
                $ptopic++;
            }
            if ($type == "TOPIC" || $type == "P-TOPIC") {
//echo $value."<br>";
                if ($topic == 1) {
                    $body .= "<tr>";
                } else {
                    $body .= "</td></tr><tr>";
                }
                $body .= "<td>" . $topic . "</td><td><strong>" . $value . "</strong>";
//putbuttons($params);
                $topic++;
                $subtopic = 1;
                $source = 1;
                if ($topicChangeable) {
                    if ($type == "TOPIC") {
                        $lectureTopics .= $value . "\r\n";
                    } elseif ($type == "P-TOPIC") {
                        $practiceTopics .= $value . "\r\n";
                    }
                }
            }

            if ($type == "SUBTOPIC" || $type == "P-SUBTOPIC") {
                if ($subtopic == 1) {
                    $body .= "<ul>";
                }
                $body .= "<li>" . $value;
//putbuttons($params);
                $body .= "</li>";
                $subtopic++;
            }

            if ($type == "SOURCE" || $type == "P-SOURCE") {
                if ($source == 1) {
                    $body .= "</ul><br><b><i>Sources:</i></b><ul>";
                }
                if (strlen($value) > 0) {
                    $body .= "<li>";
                    if (strlen($value2) > 0) {
                        $body .= "<a href='http://iuhdplus/courses/" . $departmentLetterCode . "/" . $programCourseCode . "/" . $value2 . "'>" . $value . "</a>";
                    } else {
                        $body .= $value;
                    }
//putbuttons($params);
                    $body .= "</li>";
                } else {
                    $body .= "<li>";
//putbuttons($params);
                    $body .= "</li>";
                }
                $source++;
            }

            if ($type == "EXAM" || $type == "P-EXAM") {
                $body .= "<tr><td></td><td><strong>" . $value . "</strong>";
//putbuttons($params);
                $body .= "</td></tr>";
                if ($value == "FINAL EXAM") {
                    $topic = 1;
                }
            }

            if ($type == "MEDIA") {
                if (strlen($value) != 0) {
                    $body .= "<li>";
                    if (strlen($value2) != 0) {
                        $body .= "<a href='http://iuhdplus/courses/" . $departmentLetterCode . "/" . $programCourseCode . "/" . $value2 . "'>" . $value . "</a>";
                    } else {
//$body .= "NO LINK";
                    }
//putbuttons($params);
                    $body .= "</li>";
                }
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if ($topicChangeable) {
//            $body .= "<h3>UPDATED</h3>";
            $taughtCourse->setDataField('lecture_topics', $lectureTopics);
            $taughtCourse->setDataField('practice_topics', $practiceTopics);
            $taughtCourseRepository->save($taughtCourse);
        }
//        $body .= $lectureTopics;

        return $this->render('iuhdplus/course.html.twig', [
// last username entered by the user (if any)
                    'action' => 'plan',
                    'page_title' => 'IUHD+',
                    'page_content' => $body,
                    'course' => $course
        ]);
    }

    /**
     * @Route("/iuhdplus/course/{programCourseCode}/siwsi", name="iuhdplus_course_siwsi")
     */
    public function courseSiwsi(Request $request) {
        $courseId = $request->attributes->get('programCourseCode');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourse = $taughtCourseRepository->find($courseId);
        $programCourseCode = $taughtCourse->getCourseCode();
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $programCourse = $programCourseRepository->findOneBy(['letterCode' => $programCourseCode]);
        $departmentLetterCode = $taughtCourse->getDepartmentLetterCode();
        $coursePath = $this->webroot . 'courses/' . $departmentLetterCode . "/" . $programCourseCode . "/";
        $spreadsheet = IOFactory::load($coursePath . $programCourseCode . '.xlsx');
//$spreadsheet->setActiveSheetIndex(1);
//$sheet = $spreadsheet->getActiveSheet();
//$spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getSheetByName('SIWSI');
        $body = '';
        $course = [
            'title' => $taughtCourse->getNameEnglish(),
            'programCourse' => $programCourse,
            'taughtCourse' => $taughtCourse,
            'code' => $programCourse->getLetterCode(),
            'id' => $taughtCourse->getId()
        ];

        $instructions = $sheet->getCell('B1')->getValue();
        $body .= "<p>$instructions</p><p><h4>Topics</h4></p><ol>";
        for ($row = 2; $row < 2000; $row++) {
            $value = htmlentities($sheet->getCellByColumnAndRow(2, $row)->getValue());
            $cell_address = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1) . $row;
            if (strlen($value) == 0) {
                break;
            }
            $body .= "<li><em>$value</em>";
            if ($this->isGranted("ROLE_SPECIALIST")) {
                $body .= " <a class='updatelink' href=\"javascript:update('" . $programCourseCode . "','SIWSI','" . $cell_address . "'," . $row . ",'" . $value . "');\">" . $this->getButton('edit') . "</a>";
            }
            $body .= "</li>";
        }
        $body .= "</ol></div></div>";

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->render('iuhdplus/course.html.twig', [
// last username entered by the user (if any)
                    'action' => 'siwsi',
                    'page_title' => 'IUHD+',
                    'page_content' => $body,
                    'course' => $course
        ]);
    }

    /**
     * @Route("/iuhdplus/course/{programCourseCode}/readings", name="iuhdplus_course_readings")
     */
    public function courseReadings(Request $request) {
        $courseId = $request->attributes->get('programCourseCode');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourse = $taughtCourseRepository->find($courseId);
        $programCourseCode = $taughtCourse->getCourseCode();
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $programCourse = $programCourseRepository->findOneBy(['letterCode' => $programCourseCode]);
        $departmentLetterCode = $taughtCourse->getDepartmentLetterCode();
        $coursePath = $this->webroot . 'courses/' . $departmentLetterCode . "/" . $programCourseCode . "/";
        $spreadsheet = IOFactory::load($coursePath . $programCourseCode . '.xlsx');
//$spreadsheet->setActiveSheetIndex(1);
//$sheet = $spreadsheet->getActiveSheet();
//$spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getSheetByName('Literature');
        $body = '';
        $course = [
            'title' => $taughtCourse->getNameEnglish(),
            'programCourse' => $programCourse,
            'taughtCourse' => $taughtCourse,
            'code' => $programCourse->getLetterCode(),
            'id' => $taughtCourse->getId()
        ];

        $instructions = $sheet->getCell('B1')->getValue();
        for ($row = 1; $row < 2000; $row++) {
            $value = htmlentities($sheet->getCellByColumnAndRow(2, $row)->getValue());
            $cell_address1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1) . $row;
            $file = $sheet->getCellByColumnAndRow(3, $row)->getValue();
            $cell_address2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2) . $row;
            if (strlen($value) == 0) {
                break;
            } else {
                $body .= "<li>$value ";
//                if (MANAGER) {
//                    $body .= " <a class='updatelink' href=\"javascript:update('" . $programCourseCode . "','Literature','" . $cell_address1 . "'," . $row . ",'" . $value . "');\">" . getbutton('edit') . "</a>";
//                }
                if (strlen($file) != 0) {

                    $body .= " <a href='courses/" . $departmentLetterCode . "/" . $programCourseCode . "/" . $file . "'>PDF</a>";
//                    if (MANAGER) {
//                        $body .= " <a class='updatelink' href=\"javascript:update('" . $programCourseCode . "','Literature','" . $cell_address2 . "'," . $row . ",'" . $file . "');\">" . getbutton('edit') . "</a>";
//                    }
                }
                $body .= "</li>";
            }
        }

        $body .= "</ol></div></div>";

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->render('iuhdplus/course.html.twig', [
// last username entered by the user (if any)
                    'action' => 'readings',
                    'page_title' => 'IUHD+',
                    'page_content' => $body,
                    'course' => $course
        ]);
    }

    private function getButton($command): ?string {
        if ($command == 'edit') {
            return "<svg alt='Edit text' class='svg-icon' viewBox='0 0 20 20'>
                <path fill='none' d='M19.404,6.65l-5.998-5.996c-0.292-0.292-0.765-0.292-1.056,0l-2.22,2.22l-8.311,8.313l-0.003,0.001v0.003l-0.161,0.161c-0.114,0.112-0.187,0.258-0.21,0.417l-1.059,7.051c-0.035,0.233,0.044,0.47,0.21,0.639c0.143,0.14,0.333,0.219,0.528,0.219c0.038,0,0.073-0.003,0.111-0.009l7.054-1.055c0.158-0.025,0.306-0.098,0.417-0.211l8.478-8.476l2.22-2.22C19.695,7.414,19.695,6.941,19.404,6.65z M8.341,16.656l-0.989-0.99l7.258-7.258l0.989,0.99L8.341,16.656z M2.332,15.919l0.411-2.748l4.143,4.143l-2.748,0.41L2.332,15.919z M13.554,7.351L6.296,14.61l-0.849-0.848l7.259-7.258l0.423,0.424L13.554,7.351zM10.658,4.457l0.992,0.99l-7.259,7.258L3.4,11.715L10.658,4.457z M16.656,8.342l-1.517-1.517V6.823h-0.003l-0.951-0.951l-2.471-2.471l1.164-1.164l4.942,4.94L16.656,8.342z'></path>
						</svg></a> ";
        } elseif ($command == 'delete') {
            return "<svg alt='Delete' class='svg-icon' viewBox='0 0 20 20'>
							<path fill='none' d='M13.774,9.355h-7.36c-0.305,0-0.552,0.247-0.552,0.551s0.247,0.551,0.552,0.551h7.36
								c0.304,0,0.551-0.247,0.551-0.551S14.078,9.355,13.774,9.355z M10.094,0.875c-4.988,0-9.031,4.043-9.031,9.031
								s4.043,9.031,9.031,9.031s9.031-4.043,9.031-9.031S15.082,0.875,10.094,0.875z M10.094,17.809c-4.365,0-7.902-3.538-7.902-7.902
								c0-4.365,3.538-7.902,7.902-7.902c4.364,0,7.902,3.538,7.902,7.902C17.996,14.271,14.458,17.809,10.094,17.809z'></path>
						</svg>";
        } elseif ($command == 'upload') {
            return "<svg class='svg-icon' viewBox='3 3 15 15'><path d='M4.317,16.411c-1.423-1.423-1.423-3.737,0-5.16l8.075-7.984c0.994-0.996,2.613-0.996,3.611,0.001C17,4.264,17,5.884,16.004,6.88l-8.075,7.984c-0.568,0.568-1.493,0.569-2.063-0.001c-0.569-0.569-0.569-1.495,0-2.064L9.93,8.828c0.145-0.141,0.376-0.139,0.517,0.005c0.141,0.144,0.139,0.375-0.006,0.516l-4.062,3.968c-0.282,0.282-0.282,0.745,0.003,1.03c0.285,0.284,0.747,0.284,1.032,0l8.074-7.985c0.711-0.71,0.711-1.868-0.002-2.579c-0.711-0.712-1.867-0.712-2.58,0l-8.074,7.984c-1.137,1.137-1.137,2.988,0.001,4.127c1.14,1.14,2.989,1.14,4.129,0l6.989-6.896c0.143-0.142,0.375-0.14,0.516,0.003c0.143,0.143,0.141,0.374-0.002,0.516l-6.988,6.895C8.054,17.836,5.743,17.836,4.317,16.411'></path></svg>";
        } else {
            return null;
        }
    }

    private function formPanel($course_code): string {
        
    }

    /**
     * @Route("/iuhdplus/course/{programCourseCode}/syllabus", name="iuhdplus_course_syllabus")
     */
    public function downloadSyllabus(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        // Template processor instance creation
        $courseId = $request->attributes->get('programCourseCode');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourse = $taughtCourseRepository->find($courseId);
        $programCourseCode = $taughtCourse->getCourseCode();
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $programCourse = $programCourseRepository->findOneBy(['letterCode' => $programCourseCode]);
        $departmentLetterCode = $taughtCourse->getDepartmentLetterCode();
        $coursePath = $this->webroot . 'courses/' . $departmentLetterCode . "/" . $programCourseCode . "/";
//        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($coursePath . $programCourseCode . '.xlsx');
//        $spreadsheet->setReadDataOnly(true);
//        $spreadsheet->
//        $spreadsheet->load($coursePath . $programCourseCode . '.xlsx');
        $spreadsheet = IOFactory::load($coursePath . $programCourseCode . '.xlsx');
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

//        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
//                $this->getUser()->getId(), EntityTypeEnum::ENTITY_NULL, 0, 'Download syllabus. Code:' . $programCourseCode);
//        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $templateFile = "template_syllabus_en.docx";
        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);

        // Variables on different parts of document
        $templateProcessor->setValue('faculty_name_turkmen', mb_strtoupper($sheet->getCell('B2')->getValue()));
        $templateProcessor->setValue('department_name_turkmen', mb_strtoupper($sheet->getCell('B4')->getValue()));
        $templateProcessor->setValue('course_code_turkmen', $sheet->getCell('B26')->getValue());
        $templateProcessor->setValue('course_type_turkmen', $sheet->getCell('B25')->getValue());
        $templateProcessor->setValue('course_title_turkmen', $sheet->getCell('B6')->getValue());
        $templateProcessor->setValue('course_majors_turkmen', $sheet->getCell('B8')->getValue());
        $templateProcessor->setValue('course_curriculum_year_turkmen', $sheet->getCell('B17')->getValue());
        $templateProcessor->setValue('course_curriculum_semester_turkmen', $sheet->getCell('B18')->getValue());
        $templateProcessor->setValue('lecture_hours_turkmen', $sheet->getCell('B20')->getValue());
        $templateProcessor->setValue('seminar_hours_turkmen', $sheet->getCell('B22')->getValue());
        $templateProcessor->setValue('total_hours_turkmen', $sheet->getCell('B23')->getValue());
        $templateProcessor->setValue('instructor_turkmen', $sheet->getCell('B14')->getValue());
        $templateProcessor->setValue('department_name_turkmen2', $sheet->getCell('B4')->getValue());
        $templateProcessor->setValue('department_name_turkmen3', $sheet->getCell('B4')->getValue());
        $templateProcessor->setValue('department_head_turkmen', $sheet->getCell('B10')->getValue());
        $templateProcessor->setValue('faculty_name_turkmen3', $sheet->getCell('B2')->getValue());
        $templateProcessor->setValue('faculty_dean_turkmen', $sheet->getCell('B12')->getValue());

        $templateProcessor->setValue('faculty_name', mb_strtoupper($sheet->getCell('B1')->getValue()));
        $templateProcessor->setValue('department_name', mb_strtoupper($sheet->getCell('B3')->getValue()));
        $templateProcessor->setValue('course_code', $sheet->getCell('B26')->getValue());
        $templateProcessor->setValue('course_type', $sheet->getCell('B24')->getValue());
        $templateProcessor->setValue('course_title', $sheet->getCell('B5')->getValue());
        $templateProcessor->setValue('course_majors', $sheet->getCell('B7')->getValue());
        $templateProcessor->setValue('course_curriculum_year', $sheet->getCell('B17')->getValue());
        $templateProcessor->setValue('course_curriculum_semester', $sheet->getCell('B18')->getValue());
        $templateProcessor->setValue('lecture_hours', $sheet->getCell('B20')->getValue());
        $templateProcessor->setValue('seminar_hours', $sheet->getCell('B22')->getValue());
        $templateProcessor->setValue('total_hours', $sheet->getCell('B23')->getValue());
        $templateProcessor->setValue('instructor', $sheet->getCell('B13')->getValue());
        $templateProcessor->setValue('department_name2', $sheet->getCell('B3')->getValue());
        $templateProcessor->setValue('department_name3', $sheet->getCell('B3')->getValue());
        $templateProcessor->setValue('department_head', $sheet->getCell('B9')->getValue());
        $templateProcessor->setValue('faculty_name3', $sheet->getCell('B1')->getValue());
        $templateProcessor->setValue('faculty_dean', $sheet->getCell('B11')->getValue());

        $templateProcessor->setValue('course_description', $sheet->getCell('B27')->getValue());
        $strObjectives = $sheet->getCell('B28')->getValue();
        $strObjectives = str_replace("•  ", " - ", $strObjectives);
        $strObjectives = str_replace("\n", "</w:t><w:br/><w:t>", $strObjectives);
        $templateProcessor->setValue('course_objectives', $strObjectives);
        $templateProcessor->setValue('course_methods', $sheet->getCell('B29')->getValue());

        $sheet = $spreadsheet->getSheetByName('Sessions');

        $topics = [];
        $subtopics = [];
        $sources = [];
        $practice_topics = [];
        $practice_subtopics = [];
        $practice_sources = [];
        $topic_index = 0;
        $practice_topic_index = 0;
        for ($row = 1; $row < 2000; $row++) {
            $type = $sheet->getCellByColumnAndRow(1, $row)->getValue();
//$body .= "type:" . $type;
            if (strlen($type) == 0) {
                break;
            }
            $value = htmlentities($sheet->getCellByColumnAndRow(2, $row)->getValue());
            $value2 = htmlentities($sheet->getCellByColumnAndRow(3, $row)->getValue());
            if ($type == "SUBTOPIC") {
                $subtopics[] = ['index' => $topic_index, 'title' => $value];
            } elseif ($type == "P-SUBTOPIC") {
                $practice_subtopics[] = ['index' => $topic_index, 'title' => $value];
            } elseif ($type == "SOURCE") {
                $sources[] = ['index' => $topic_index, 'title' => $value];
            } elseif ($type == "P-SOURCE") {
                $practice_sources[] = ['index' => $topic_index, 'title' => $value];
            } elseif ($type == "TOPIC") {
                $topics[] = $value;
                $topic_index++;
            } elseif ($type == "P-TOPIC") {
                $practice_topics[] = $value;
                $practice_topic_index++;
            }
        }

        $templateProcessor->cloneRow('row_lecture_number', sizeof($topics));
        $i = 1;
        foreach ($topics as $topic) {
            $templateProcessor->setValue('row_lecture_number#' . $i, $i);
            $templateProcessor->setValue('row_lecture_hours#' . $i, "2 hours");
            $subtopics_text = "йгщгщшф";
            $sources_text = "ňъхчх";
//            foreach ($subtopics as $subtopic) {
//                if ($subtopic['index'] == $i) {
//                    $subtopics_text .= " - " . htmlspecialchars($subtopic['title'],ENT_COMPAT, 'UTF-8') . "</w:t><w:br/><w:t>";
//                }
//            }
            foreach ($sources as $source) {
                if ($source['index'] == $i) {
//                    echo mb_detect_encoding($source['title'])." - ".$source['title']."<br>";
                    $sources_text .= " - " . $source['title'] . "</w:t><w:br/><w:t>";
                }
            }
            $templateProcessor->setValue('row_lecture_topic#' . $i, $topic);
            $templateProcessor->setValue('row_lecture_subtopics#' . $i, $subtopics_text);
            $templateProcessor->setValue('row_lecture_sources#' . $i, $sources_text);
            $i++;
        }

        $templateProcessor->cloneRow('row_practice_number', sizeof($practice_topics));
        $i = 1;
        foreach ($practice_topics as $practice_topic) {
            $templateProcessor->setValue('row_practice_number#' . $i, $i);
//            echo $practice_topic."<br>";
            $templateProcessor->setValue('row_practice_topic#' . $i, htmlspecialchars($practice_topic));
            $templateProcessor->setValue('row_practice_hours#' . $i, "2 hours");
            $i++;
        }
        //prepare for export
//        die();
        $fileName = "syllabus_" . $programCourseCode . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";charset=utf-8');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    private function replaceNewline(string $str): ?string {
        $result = '';
        $chars = str_split($str);
        foreach ($chars as $char) {
            if (mb_ord($char) != 10) {
                $result .= "[" . $char . " = " . mb_ord($char) . "]";
            } else {
//                $result .= "-\r\n";
            }
        }
        return $result;
    }

}
