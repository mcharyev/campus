<?php

namespace App\Controller\Interop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\TeacherAttendanceManager;
use App\Entity\Faculty;
use App\Entity\TaughtCourse;
use App\Entity\Group;
use App\Entity\EnrolledStudent;
use App\Entity\AlumnusStudent;
use App\Entity\ExpelledStudent;
use App\Entity\StudentAbsence;
use App\Entity\User;
use App\Hr\Entity\Employee;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class ExporterController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;
    private $turkmenMonths;
    private $monthsEndings;
    private $yearsEndings;
    private $daysEndings;
    private $orders;

    public function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->turkmenMonths = array('ýanwar', 'fewral', 'mart', 'aprel', 'maý', 'iýun', 'iýul', 'awgust', 'sentýabr', 'oktýabr', 'noýabr', 'dekabr');
        $this->monthsEndings = array('ýanwaryndaky', 'fewralyndaky', 'mart',
            'aprelindäki', 'maýyndaky', 'iýunyndaky', 'iýulyndaky', 'awgustyndaky',
            'sentýabryndaky', 'oktýabryndaky', 'noýabryndaky', 'dekabryndaky');
        $this->yearsEndings = [
            2012 => '2012-nji',
            2013 => '2013-nji',
            2014 => '2014-nji',
            2015 => '2015-nji',
            2016 => '2016-njy',
            2017 => '2017-nji',
            2018 => '2018-nji',
            2019 => '2019-njy',
            2020 => '2020-nji',
            2021 => '2021-nji',
            2022 => '2022-nji',
			2023 => '2023-nji',
        ];

        $this->daysEndings = [
            1 => '1-nji',
            2 => '2-nji',
            3 => '3-nji',
            4 => '4-nji',
            5 => '5-nji',
            6 => '6-nji',
            7 => '7-nji',
            8 => '8-nji',
            9 => '9-njy',
            10 => '10-njy',
            11 => '11-nji',
            12 => '12-nji',
            13 => '13-nji',
            14 => '14-nji',
            15 => '15-nji',
            16 => '16-nji',
            17 => '17-nji',
            18 => '18-nji',
            19 => '19-njy',
            20 => '20-nji',
            21 => '21-nji',
            22 => '22-nji',
            23 => '23-nji',
            24 => '24-nji',
            25 => '25-nji',
            26 => '26-njy',
            27 => '27-nji',
            28 => '28-nji',
            29 => '29-njy',
            30 => '30-njy',
            31 => '31-nji',
        ];

        $this->orders = [
            '2014b' => ['orderDate' => '2014-09-01', 'orderNumber' => 9],
            '2015b' => ['orderDate' => '2015-08-25', 'orderNumber' => 8],
            '2016b' => ['orderDate' => '2016-08-31', 'orderNumber' => 8],
            '2017b' => ['orderDate' => '2017-08-18', 'orderNumber' => 7],
            '2018b' => ['orderDate' => '2018-08-10', 'orderNumber' => 8],
            '2019b' => ['orderDate' => '2019-08-09', 'orderNumber' => 8],
            '2019m' => ['orderDate' => '2019-08-09', 'orderNumber' => 9],
            '2020b' => ['orderDate' => '2020-08-16', 'orderNumber' => 8],
            '2020m' => ['orderDate' => '2020-08-18', 'orderNumber' => 9],
            '2021b' => ['orderDate' => '2021-08-16', 'orderNumber' => 8],
            '2021m' => ['orderDate' => '2021-08-18', 'orderNumber' => 9],
            '2022b' => ['orderDate' => '2022-08-16', 'orderNumber' => 8],
            '2022m' => ['orderDate' => '2022-08-18', 'orderNumber' => 9],
			'2023b' => ['orderDate' => '2023-08-16', 'orderNumber' => 8],
            '2023m' => ['orderDate' => '2023-08-18', 'orderNumber' => 9],
            '2016tt' => ['orderDate' => '2016-08-15', 'orderNumber' => 103],
        ];
    }

    /**
     * @Route("/interop/exporter/simplegrouplist/{systemId}", name="interop_exporter_simplegrouplist")
     */
    public function getGroupSimpleList(Request $request) {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $systemId = $request->attributes->get('systemId');
        if (!empty($systemId)) {
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $group = $groupRepository->findOneBy(['systemId' => $systemId]);
        }

        if ($group) {
            $students = $group->getStudents();
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'ID');
            $sheet->setCellValue('C1', 'Student');
            $i = 2;
            foreach ($students as $student) {
                $sheet->setCellValue('A' . $i, ($i - 1));
                $sheet->setCellValue('B' . $i, $student->getSystemId());
                $sheet->setCellValue('C' . $i, $student->getLastnameTurkmen() . " " . $student->getFirstnameTurkmen());
                $i++;
            }

            $response = new Response();
            //$response->

            $writer = new Xlsx($spreadsheet);
//            //$writer->save($_SERVER['APP_REPORTS_DIR'] . "/" . $group->getLetterCode() . "_simplelist.xlsx");
//            // Redirect output to a client’s web browser (Xlsx)
//            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//            $response->headers->set('Content-Disposition', 'attachment;filename="myfile.xlsx"');
//            $response->headers->set('Cache-Control', 'max-age=0');
//
//            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
//            $writer->save('php://output');
//            return $response;
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_GROUP, $group->getSystemId(), 'Download simple group list');
            // redirect output to client browser
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="myfile.xls"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }
        return new Response('ok');
    }

    /**
     * @Route("/interop/exporter/courseattendancesheet/{courseId}/{type}/{semester}", name="interop_exporter_courseattendancesheet")
     */
    public function getCourseAttendanceSheet(Request $request) {

        $semestersEn = array('Fall', 'Spring');
        $examTypes = array('Midterm exam', 'Final exam', 'SIW Exam', 'Make up Final Exam');
        // Template processor instance creation
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_SERVER['APP_REPORTS_DIR'] . "/resources/template_exam_attendance_sheet.xlsx");

        $courseId = $request->attributes->get('courseId');
        $semester = $request->attributes->get('semester');
        $type = $request->attributes->get('type');

        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $year1 = $this->systemInfoManager->getSemesterBeginYear(1);
        $year2 = $this->systemInfoManager->getSemesterBeginYear(2);
        if (!empty($courseId)) {
            $taughtCourse = $taughtCourseRepository->find($courseId);
            //$departmentName = $taughtCourse->getDepartment()->getNameEnglish();
            $departmentName = $taughtCourse->getTeacher()->getDepartment()->getNameEnglish();
            $semesterName = $semestersEn[intval($semester)] . " Semester";
            $examType = $examTypes[intval($type)];
            $courseTitle = $taughtCourse->getNameEnglish();
            $instructor = $taughtCourse->getTeacher()->getFullname();

            if ($taughtCourse) {
                $group_ids = explode(",", $taughtCourse->getStudentGroups());
                $sheetIndex = 0;
                foreach ($group_ids as $group_id) {

                    $group = $groupRepository->findOneBy(['systemId' => $group_id]);
                    if ($group->getStatus() == 1) {
                        $spreadsheet->setActiveSheetIndex($sheetIndex);
                        $sheet = $spreadsheet->getActiveSheet();
                        $sheet->setTitle($group->getLetterCode());

                        $sheet->setCellValue('B2', $year1 . "-" . $year2 . " Academic Year " . $semesterName)
                                ->setCellValue('B3', $departmentName . " Department")
                                ->setCellValue('B4', $examType . " Attendance Sheet")
                                ->setCellValue('C5', "   :  " . $group->getStudyProgram()->getNameEnglish() . ", " . $group->getLetterCode())
                                ->setCellValue('C6', "   :  " . $courseTitle)
                                ->setCellValue('C7', "   : ________________________")
                                ->setCellValue('C14', "Exam supervisor: " . $instructor);

                        $students = $group->getStudents();
                        $baseRow = 11;
                        $i = 0;
                        $sheet->setCellValue('C12', "Total: " . sizeof($students) . " students");
                        foreach ($students as $student) {
                            $r = $baseRow + $i;
                            $sheet->insertNewRowBefore($r, 1)
                                    ->setCellValue('B' . $r, $i + 1)
                                    ->setCellValue('C' . $r, $student->getSystemId() . "   " . $student->getFullname())
                                    ->duplicateStyle($sheet->getStyle('B10'), 'B' . $r . ':' . 'B' . $r);
                            $i++;
                        }
                        $sheet->removeRow($baseRow - 1, 1);
                    }

                    $sheetIndex++;
                }

//                for ($removableIndex = $sheetIndex - 1; $removableIndex < 5; $removableIndex++) {
//                    $spreadsheet->removeSheetByIndex($removableIndex);
//                }
            }

            $response = new Response();

            $writer = new Xlsx($spreadsheet);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_GROUP, $group->getSystemId(), 'Download attendance sheet');
            // redirect output to client browser
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $courseTitle . ' ' . $examType . ' attendance sheet.xls"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }

        return new Response('ok');
    }

    /**
     * @Route("/interop/exporter/coursegradesheet/{courseId}/{type}/{semester}", name="interop_exporter_coursegradesheet")
     */
    public function getCourseGradeSheet(Request $request) {

        $semestersEn = array('Fall', 'Spring');
        $examTypes = array('Midterm exam', 'Final exam', 'SIW Exam', 'Make up Final Exam');
        // Template processor instance creation
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_SERVER['APP_REPORTS_DIR'] . "/resources/template_exam_grade_sheet.xlsx");

        $courseId = $request->attributes->get('courseId');
        $semester = $request->attributes->get('semester');
        $type = $request->attributes->get('type');

        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $year1 = $this->systemInfoManager->getSemesterBeginYear(1);
        $year2 = $this->systemInfoManager->getSemesterBeginYear(2);
        if (!empty($courseId)) {
            $taughtCourse = $taughtCourseRepository->find($courseId);
            //$departmentName = $taughtCourse->getDepartment()->getNameEnglish();
            $departmentName = $taughtCourse->getTeacher()->getDepartment()->getNameEnglish();
            $semesterName = $semestersEn[intval($semester)] . " Semester";
            $examType = "Course";
            $courseTitle = $taughtCourse->getNameEnglish();
            $instructor = $taughtCourse->getTeacher()->getShortFullname();

            if ($taughtCourse) {
                $group_ids = explode(",", $taughtCourse->getStudentGroups());
                $sheetIndex = 0;
                foreach ($group_ids as $group_id) {

                    $group = $groupRepository->findOneBy(['systemId' => $group_id]);
                    if ($group->getStatus() == 1) {
                        $departmentHead = $taughtCourse->getDepartment()->getDepartmentHead()->getShortFullname();
                        $dean = $group->getFaculty()->getDean()->getShortFullname();
                        $spreadsheet->setActiveSheetIndex($sheetIndex);
                        $sheet = $spreadsheet->getActiveSheet();
                        $sheet->setTitle($group->getLetterCode());

                        $sheet->setCellValue('B2', $year1 . "-" . $year2 . " Academic Year " . $semesterName)
                                ->setCellValue('B3', $departmentName . " Department")
                                ->setCellValue('B4', $examType . " Grade Sheet")
                                ->setCellValue('C5', "   :  " . $group->getStudyProgram()->getNameEnglish() . ", " . $group->getLetterCode())
                                ->setCellValue('C6', "   :  " . $courseTitle)
                                ->setCellValue('C7', "   : ________________________")
                                ->setCellValue('C14', "Exam supervisor: " . $instructor)
                                ->setCellValue('C19', "Head of Department: " . $departmentHead)
                                ->setCellValue('C22', "Dean: " . $dean);

                        $students = $group->getStudents();
                        $baseRow = 11;
                        $i = 0;
                        $sheet->setCellValue('C12', "Total: " . sizeof($students) . " students");
                        foreach ($students as $student) {
                            $r = $baseRow + $i;
                            $sheet->insertNewRowBefore($r, 1)
                                    ->setCellValue('B' . $r, $i + 1)
                                    ->setCellValue('C' . $r, $student->getSystemId() . "   " . $student->getFullname())
                                    ->duplicateStyle($sheet->getStyle('B10'), 'B' . $r . ':' . 'B' . $r);
                            $i++;
                        }
                        $sheet->removeRow($baseRow - 1, 1);
                    }

                    $sheetIndex++;
                }

//                for ($removableIndex = $sheetIndex - 1; $removableIndex < 5; $removableIndex++) {
//                    $spreadsheet->removeSheetByIndex($removableIndex);
//                }
            }

            $response = new Response();

            $writer = new Xlsx($spreadsheet);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_GROUP, $group->getSystemId(), 'Download grade sheet');
            // redirect output to client browser
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $courseTitle . ' ' . $examType . ' grade sheet.xls"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }

        return new Response('ok');
    }

    /**
     * @Route("/interop/exporter/courseexamcover/{courseId}/{type}/{semester}", name="interop_exporter_examcover")
     */
    public function examCover(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $semestersEn = array('Fall', 'Spring');
        $examTypes = array('Midterm exam', 'Final exam', 'SIW Exam', 'Make up Final Exam');
        // Template processor instance creation
        $courseId = $request->attributes->get('courseId');
        $semester = $request->attributes->get('semester');
        $type = $request->attributes->get('type');
        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/template_exam_roll_cover_computer.docx");
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $taughtCourse = $taughtCourseRepository->find($courseId);

        //$departmentName = $taughtCourse->getDepartment()->getNameEnglish();
        $departmentName = $taughtCourse->getTeacher()->getDepartment()->getNameEnglish();

        $semesterName = $semestersEn[intval($semester)] . " Semester";
        $examType = $examTypes[intval($type)];
        $courseTitle = $taughtCourse->getDataField('course_name');
        $instructor = $taughtCourse->getTeacher()->getFullname();

        $year1 = $this->systemInfoManager->getSemesterBeginYear(1);
        $year2 = $this->systemInfoManager->getSemesterBeginYear(2);

        // Variables on different parts of document
        $templateProcessor->setValue('academic_year', $year1 . "-" . $year2);
        $templateProcessor->setValue('department', $departmentName);
        $templateProcessor->setValue('semester', $semesterName);
        $templateProcessor->setValue('group', '_________');
        $templateProcessor->setValue('exam_type', $examType);
        $templateProcessor->setValue('course_title', $courseTitle);
        $templateProcessor->setValue('date', '___________');
        $templateProcessor->setValue('instructor', $instructor);
        $fileName = "exam_cover_" . $courseTitle . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_TAUGHTCOURSE, $taughtCourse->getId(), 'Download exam cover');

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    /**
     * @Route("/interop/exporter/supplementarycertificate/{id}", name="interop_exporter_supplementarycertificate")
     */
    public function supplementaryCertificate(Request $request) {
        // Template processor instance creation
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $id = $request->attributes->get('id');
        $studentRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
        $student = $studentRepository->findOneBy(['systemId' => $id]);
        $templateFile = "template_supplementary_certificate_bachelor.docx";
        if ($student->getStudentGroup()->getStudyProgram()->getProgramLevel()->getSystemId() == 7) {
            $templateFile = "template_supplementary_certificate_master.docx";
        }
        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);

        $lastname = $student->getLastnameEnglish();
        if (strlen($lastname) == 0)
            $lastname = $student->getLastnameTurkmen();

        $firstname = $student->getFirstnameEnglish();
        if (strlen($firstname) == 0)
            $firstname = $student->getFirstnameTurkmen();
        if ($student->getBirthdate())
            $birthdate = $student->getBirthdate()->format("j F Y");
        else
            $birthdate = '';
        $major = $student->getStudentGroup()->getStudyProgram()->getNameEnglish();

        // Variables on different parts of document
        $templateProcessor->setValue('lastname', $lastname);
        $templateProcessor->setValue('firstname', $firstname);
        $templateProcessor->setValue('birthdate', $birthdate);
        $templateProcessor->setValue('id', $id);
        $templateProcessor->setValue('major', $major);
        $fileName = "supplementary_certificate_" . $this->latinize($lastname . " " . $firstname) . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ALUMNUSSTUDENT, $student->getSystemId(), 'Download supplementary certificate');

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    /**
     * @Route("/interop/exporter/enrollmentcertificate/{id}", name="interop_exporter_enrollmentcertificate")
     */
    public function enrollmentCertificate(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        // Template processor instance creation
        $id = $request->attributes->get('id');
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $student = $studentRepository->findOneBy(['systemId' => $id]);
        $templateFile = "template_enrollment_certificate_bachelor.docx";
        $programLevel = $student->getStudentGroup()->getStudyProgram()->getProgramLevel()->getSystemId();
        if ($programLevel == 7) {
            $templateFile = "template_enrollment_certificate_master.docx";
        }
        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);

        $lastname = $student->getLastnameEnglish();
        if (strlen($lastname) == 0)
            $lastname = $student->getLastnameTurkmen();

        $firstname = $student->getFirstnameEnglish();
        if (strlen($firstname) == 0)
            $firstname = $student->getFirstnameTurkmen();
        if ($student->getBirthdate())
            $birthdate = $student->getBirthdate()->format("j F Y");
        else
            $birthdate = '';
        $major = $student->getStudentGroup()->getStudyProgram()->getNameEnglish();
        $graduationYear = $student->getStudentGroup()->getGraduationYear();
        $entryYear = substr(strval($id), 0, 2);

        if ($this->isTT($student->getStudentGroup()->getSystemId())) {
            $orderData = $this->orders['2016tt'];
        } else {
            $orderData = $this->orders['20' . $entryYear . 'b'];
        }
        $orderDate = new \DateTime($orderData['orderDate']);
        $orderNumber = $orderData['orderNumber'];

        $currentYear = date("Y");
        $currentMonth = date("n");
        $currentDay = date("d");
        if ($currentMonth > 8) {
            $studyYear = 5 - ($graduationYear - $currentYear);
        } else {
            $studyYear = 4 - ($graduationYear - $currentYear);
        }

        $matriculationDate = $orderDate->format("j F Y");
        $graduationDate = "28 June " . $graduationYear;

        // Variables on different parts of document
        $templateProcessor->setValue('lastname', $lastname);
        $templateProcessor->setValue('firstname', $firstname);
        $templateProcessor->setValue('birthdate', $birthdate);
        $templateProcessor->setValue('id', $id);
        $templateProcessor->setValue('major', $major);
        $templateProcessor->setValue('studyyear', $studyYear);
        $templateProcessor->setValue('matriculation_date', $matriculationDate);
        $templateProcessor->setValue('graduation_date', $graduationDate);
        $fileName = "enrollment_certificate_" . $this->latinize($lastname . " " . $firstname) . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getSystemId(), 'Download enrollment certificate');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    /**
     * @Route("/interop/exporter/englishstudycertificate/{type}/{id}", name="interop_exporter_englishstudycertificate")
     */
    public function studyInEnglishCertificate(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        // Template processor instance creation
        $id = $request->attributes->get('id');
        $type = $request->attributes->get('type');
        if ($type == 'enrolled')
            $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        else
            $studentRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
        $student = $studentRepository->findOneBy(['systemId' => $id]);
        $templateFile = "template_certificate_englishstudy_bachelor.docx";
        $programLevel = $student->getStudentGroup()->getStudyProgram()->getProgramLevel()->getSystemId();
//        if ($programLevel == 7) {
//            $templateFile = "template_enrollment_certificate_master.docx";
//        }
        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);

        $lastname = $student->getLastnameTurkmen();
        if (strlen($lastname) == 0)
            $lastname = $student->getLastnameTurkmen();

        $firstname = $student->getFirstnameTurkmen();
        if (strlen($firstname) == 0)
            $firstname = $student->getFirstnameTurkmen();

        $patronym = $student->getPatronymTurkmen();
        if (strlen($patronym) == 0)
            $patronym = $student->getPatronymTurkmen();

        $faculty = $student->getFaculty()->getNameTurkmen();
        $major = $student->getStudentGroup()->getStudyProgram()->getNameTurkmen();
        $graduationYear = $student->getStudentGroup()->getGraduationYear();

        $currentYear = date("Y");
        $currentMonth = date("n");
        $month = $this->turkmenMonths[$currentMonth - 1];
        $currentDay = date("d");

        $beginYear = $graduationYear - 5;
        $endYear = $graduationYear;
        $beginLLD = $beginYear;
        $endLLD = $beginLLD + 1;
        $beginBachelor = $endLLD;
        $endBachelor = $endYear;

        // Variables on different parts of document
        $templateProcessor->setValue('lastname', $lastname);
        $templateProcessor->setValue('firstname', $firstname);
        $templateProcessor->setValue('patronym', $patronym);
        $templateProcessor->setValue('id', $id);
        $templateProcessor->setValue('day', $currentDay);
        $templateProcessor->setValue('month', $month);
        $templateProcessor->setValue('year', $currentYear);
        $templateProcessor->setValue('faculty', $faculty);
        $templateProcessor->setValue('major', $major);
        $templateProcessor->setValue('beginyear', $beginYear);
        $templateProcessor->setValue('endyear', $endYear);
        $templateProcessor->setValue('beginlld', $beginLLD);
        $templateProcessor->setValue('endlld', $endLLD);
        $templateProcessor->setValue('beginbachelor', $beginBachelor);
        $templateProcessor->setValue('endbachelor', $endBachelor);
        $templateProcessor->setValue('destination', 'SORAÝAN EDARANY GÖRKEZIŇ');

        $fileName = "englishstudy_certificate_" . $this->latinize($lastname . " " . $firstname) . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ALUMNUSSTUDENT, $student->getSystemId(), 'Download English-study certificate');

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    /**
     * @Route("/interop/exporter/lldcertificate/{type}/{id}", name="interop_exporter_lldcertificate")
     */
    public function lldCertificate(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");
        // Template processor instance creation
        $id = $request->attributes->get('id');
        $type = $request->attributes->get('type');
        if ($type == 'enrolled')
            $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        else
            $studentRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
        $student = $studentRepository->findOneBy(['systemId' => $id]);
        $templateFile = "template_lld_certificate.docx";
        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);

        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculty = $facultyRepository->findOneBy(['letterCode' => "LLD"]);
        $dean = $faculty->getDean();
        $deanName = $dean->getShortFullname();

        if (!$this->isGranted("ROLE_LLDDEAN")) {
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TEACHER, $dean->getId(), 'Unauthorized LLD Certificate attempt');
            return $this->render('accessdenied.html.twig', []);
        }

        $lastname = $student->getLastnameEnglish();
        if (strlen($lastname) == 0)
            $lastname = $student->getLastnameTurkmen();

        $firstname = $student->getFirstnameEnglish();
        if (strlen($firstname) == 0)
            $firstname = $student->getFirstnameTurkmen();

        $graduationYear = $student->getStudentGroup()->getGraduationYear();

        $currentDay = date("d");
        $currentMonth = date("m");
        $currentYear = date("Y");

        $beginYear = $graduationYear - 5;
        $endYear = $graduationYear;
        $beginLLD = $beginYear;
        $endLLD = $beginLLD + 1;


        if ($student->getGender() == 1) {
            $title = "Mr.";
        } else {
            $title = "Ms.";
        }

        // Variables on different parts of document
        $templateProcessor->setValue('title', $title);
        $templateProcessor->setValue('lastname', $lastname);
        $templateProcessor->setValue('firstname', $firstname);
        $templateProcessor->setValue('id', $id);
        $templateProcessor->setValue('day', $currentDay);
        $templateProcessor->setValue('month', $currentMonth);
        $templateProcessor->setValue('year', $currentYear);
        $templateProcessor->setValue('year1', $beginLLD);
        $templateProcessor->setValue('year2', $endLLD);
        $templateProcessor->setValue('dean', $deanName);

        $fileName = "LLD_certificate_" . $this->latinize($lastname . " " . $firstname) . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ALUMNUSSTUDENT, $student->getSystemId(), 'Download LLD certificate');

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    /**
     * @Route("/interop/exporter/personalinfo/{type}/{id}/{formatType?}", name="interop_exporter_studentinfo")
     */
    public function studentInfo(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        // Template processor instance creation
        $id = $request->attributes->get('id');
        $type = $request->attributes->get('type');
        $formatType = $request->attributes->get('formatType');
        $person = null;
        $photoSource = null;
        $personFullName = null;

        //$templateFile = "template_maglumat_photo.docx";
        if ($formatType == 'halkmaslahaty') {
            $templateFile = "template_maglumat_photo_halkmaslahaty.docx";
        } elseif ($formatType == 'mejlis') {
            $templateFile = "template_maglumat_mejlis.docx";
        } elseif ($formatType == 'nophoto') {
            $templateFile = "template_maglumat_mejlis.docx";
        } else {
            $templateFile = "template_maglumat_photo.docx";
        }

        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);


        if ($type == 'enrolled') {
            $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $person = $repository->findOneBy(['systemId' => $id]);
            $photoSource = $_SERVER['APP_ROOT'] . "/public/build/photos/" . $person->getGroupCode() . "/" . $person->getSystemId() . ".jpg";
            $personFullName = $person->getLastnameTurkmen() . " " . $person->getFirstnameTurkmen() . " " . $person->getPatronymTurkmen();
        } elseif ($type == 'alumnus') {
            $repository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
            $person = $repository->findOneBy(['systemId' => $id]);
            $photoSource = $_SERVER['APP_ROOT'] . "/public/build/photos/" . $person->getGroupCode() . "/" . $person->getSystemId() . ".jpg";
            $personFullName = $person->getLastnameTurkmen() . " " . $person->getFirstnameTurkmen() . " " . $person->getPatronymTurkmen();
        } elseif ($type == 'expelled') {
            $repository = $this->getDoctrine()->getRepository(ExpelledStudent::class);
            $person = $repository->findOneBy(['systemId' => $id]);
            $photoSource = $_SERVER['APP_ROOT'] . "/public/build/photos/expelled/" . $person->getSystemId() . ".jpg";
            $personFullName = $person->getLastnameTurkmen() . " " . $person->getFirstnameTurkmen() . " " . $person->getPatronymTurkmen();
        } elseif ($type == 'employee') {
            $repository = $this->getDoctrine()->getRepository(Employee::class);
            $person = $repository->findOneBy(['systemId' => $id]);
            $photoSource = $_SERVER['APP_ROOT'] . "/public/build/employee_photos/" . $person->getSystemId() . ".jpg";
            $personFullName = $person->getLastname() . " " . $person->getFirstname() . " " . $person->getPatronym();
        }

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_NULL, 0, 'Download personal info. Type:' . $type . " Id:" . $id);


        // Variables on different parts of document
        $templateProcessor->setValue('fullname', $person->getDataField('name'));
        $templateProcessor->setValue('position', $person->getDataField('position'));
        $templateProcessor->setValue('dob', $person->getDataField('dob'));
        $templateProcessor->setValue('pob', $person->getDataField('dob'));
        $templateProcessor->setValue('nationality', $person->getDataField('nationality'));
        $templateProcessor->setValue('education', $person->getDataField('education'));
        $templateProcessor->setValue('school', $person->getDataField('school'));
        $templateProcessor->setValue('profession', $person->getDataField('profession'));
        $templateProcessor->setValue('degree', $person->getDataField('degree'));
        $templateProcessor->setValue('languages', $person->getDataField('languages'));
        $templateProcessor->setValue('awards', $person->getDataField('awards'));
        $templateProcessor->setValue('trips', $person->getDataField('trips'));
        $templateProcessor->setValue('mp', $person->getDataField('mp'));
        $templateProcessor->setValue('address', $person->getDataField('address'));
        $templateProcessor->setValue('address2', $person->getDataField('address2'));
        $templateProcessor->setValue('phone', $person->getDataField('phone'));
        $templateProcessor->setValue('fullname2', $person->getDataField('name'));

        if ($formatType == 'mejlis') {
            $templateProcessor->setValue('lastname', mb_strtoupper($person->getLastnameTurkmen()));
            $templateProcessor->setValue('firstname', $person->getFirstnameTurkmen());
            $templateProcessor->setValue('patronym', $person->getPatronymTurkmen());

            $graduationYear = $person->getStudentGroup()->getGraduationYear();
            $entryYear = substr(strval($id), 0, 2);
            //$matriculationDate = $orderDate->format("j F Y");
            if ($this->isTT($person->getStudentGroup()->getSystemId())) {
                $orderData = $this->orders['2016tt'];
            } else {
                $orderData = $this->orders['20' . $entryYear . 'b'];
            }
            $orderDate = new \DateTime($orderData['orderDate']);

            $beginYear = $orderDate->format("Y");
            $beginMonth = $orderDate->format("n");
            $beginDay = $orderDate->format("j");

            $templateProcessor->setValue('since', $this->addYearEnding($beginYear) . " ýylyň " . $this->addDayEnding($beginDay) . " " . $this->addMonthEnding($beginMonth));
        }

        $positions = $person->getPositions();
        $relatives = $person->getRelatives();
        $templateProcessor->cloneRow('wperiod', sizeof($positions));
        $templateProcessor->cloneRow('rrelative', sizeof($relatives));

        $i = 1;
        foreach ($positions as $p) {
            $templateProcessor->setValue('wperiod#' . $i, $p["period"]);
            $templateProcessor->setValue('wposition#' . $i, "− " . $p["position"]);
            $i++;
        }

        $i = 1;
        foreach ($relatives as $r) {
            $templateProcessor->setValue('rno#' . $i, $i);
            $templateProcessor->setValue('rrelative#' . $i, $r["name"]);
            $templateProcessor->setValue('rrelation#' . $i, $r["relation"]);
            $templateProcessor->setValue('rdob#' . $i, $r["dob"]);
            $templateProcessor->setValue('rpob#' . $i, $r["pob"]);
            $templateProcessor->setValue('rjob#' . $i, $r["job"]);
            $templateProcessor->setValue('raddress#' . $i, $r["address"]);
            $templateProcessor->setValue('rpenalty#' . $i, $r["penalty"]);
            $i++;
        }

        if (file_exists($photoSource)) {
            $templateProcessor->setImageValue('photo', $photoSource);
        }
        //prepare for export
        $fileName = "maglumat_" . $person->getSystemId() . "_" . $this->latinize($personFullName) . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    /**
     * @Route("/interop/exporter/officialcertificatebachelor/{type}/{id}", name="interop_exporter_officialcertificatebachelor")
     */
    public function officialCertificate(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        // Template processor instance creation
        $id = $request->attributes->get('id');
        $type = $request->attributes->get('type');
        $templateFile = "template_certificate_official_bachelor.docx";
        if ($type == 'enrolled') {
            $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        } else {
            $studentRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
            $templateFile = "template_certificate_official_bachelor_alumnus.docx";
        }
        $student = $studentRepository->findOneBy(['systemId' => $id]);
        $programLevel = $student->getStudentGroup()->getStudyProgram()->getProgramLevel()->getSystemId();
        $degree = "Bakalawr";
        if ($programLevel == 7) {
            $degree = "Magistr";
        }
        //$templateFile = "template_certificate_official_bachelor.docx";

        $lastname = $student->getLastnameTurkmen();
        if (strlen($lastname) == 0)
            $lastname = $student->getLastnameTurkmen();

        $firstname = $student->getFirstnameTurkmen();
        if (strlen($firstname) == 0)
            $firstname = $student->getFirstnameTurkmen();

        $patronym = $student->getPatronymTurkmen();
        if (strlen($patronym) == 0)
            $patronym = $student->getPatronymTurkmen();

        $faculty = $student->getFaculty()->getNameTurkmen();
        $major = $student->getStudentGroup()->getStudyProgram()->getNameTurkmen();
        $graduationYear = $student->getStudentGroup()->getGraduationYear();
        $entryYear = substr(strval($id), 0, 2);

        if ($this->isTT($student->getStudentGroup()->getSystemId())) {
            //$templateFile = "template_certificate_official_bachelor.docx";
            $orderData = $this->orders['2016tt'];
        } else {
            $orderData = $this->orders['20' . $entryYear . 'b'];
        }
        $orderDate = new \DateTime($orderData['orderDate']);
        $orderNumber = $orderData['orderNumber'];
        $orderYear = $this->yearsEndings[intval($orderDate->format("Y"))];
        $orderMonth = $this->monthsEndings[intval($orderDate->format("n")) - 1];
        $orderDay = $this->daysEndings[intval($orderDate->format("d"))];

        $currentYear = date("Y");
        $currentMonth = date("n");
        $currentDay = date("d");
        if ($currentMonth > 8) {
            $studyYear = 5 - ($graduationYear - $currentYear);
        } else {
            $studyYear = 4 - ($graduationYear - $currentYear);
        }
        $studyYearEnding = $this->daysEndings[$studyYear];

        $currentYear = date("Y");
        $currentMonth = date("n");
        $month = $this->turkmenMonths[$currentMonth - 1];
        $currentDay = date("d");

        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);
        // Variables on different parts of document
        $templateProcessor->setValue('lastname', $lastname);
        $templateProcessor->setValue('firstname', $firstname);
        $templateProcessor->setValue('patronym', $patronym);
        $templateProcessor->setValue('orderday', $orderDay);
        $templateProcessor->setValue('ordermonth', $orderMonth);
        $templateProcessor->setValue('orderyear', $orderYear);
        $templateProcessor->setValue('faculty', $faculty);
        $templateProcessor->setValue('major', $major);
        $templateProcessor->setValue('ordernumber', $orderNumber);
        $templateProcessor->setValue('studyyear', $studyYearEnding);
        if ($type == 'alumnus') {
            $templateProcessor->setValue('degree1', strtolower($degree));
            $templateProcessor->setValue('degree2', $degree);
        }

        $fileName = "official_certificate_" . $this->latinize($lastname . " " . $firstname) . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ALUMNUSSTUDENT, $student->getSystemId(), 'Download English-study certificate');

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    /**
     * @Route("/interop/exporter/reference/{type}/{id}", name="interop_exporter_referencebachelor")
     */
    public function referenceLetter(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        // Template processor instance creation
        $id = $request->attributes->get('id');
        $type = $request->attributes->get('type');
        if ($type == 'enrolled') {
            $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $templateFile = "template_reference_bachelor.docx";
        } else {
            $studentRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
            $templateFile = "template_reference_bachelor_graduate.docx";
        }
        $student = $studentRepository->findOneBy(['systemId' => $id]);
        $programLevel = $student->getStudentGroup()->getStudyProgram()->getProgramLevel()->getSystemId();
//        if ($programLevel == 7) {
//            $templateFile = "template_enrollment_certificate_master.docx";
//        }

        $lastname = $student->getLastnameTurkmen();
        if (strlen($lastname) == 0)
            $lastname = $student->getLastnameTurkmen();

        $firstname = $student->getFirstnameTurkmen();
        if (strlen($firstname) == 0)
            $firstname = $student->getFirstnameTurkmen();

        $patronym = $student->getPatronymTurkmen();
        if (strlen($patronym) == 0)
            $patronym = $student->getPatronymTurkmen();

        $faculty = $student->getFaculty()->getNameTurkmen();
        $major = $student->getStudentGroup()->getStudyProgram()->getNameTurkmen();
        $graduationYear = $student->getStudentGroup()->getGraduationYear();
        $nationality = $student->getNationality()->getNameTurkmen();
        $entryYear = substr(strval($id), 0, 2);


        $templateProcessor = new TemplateProcessor($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $templateFile);
        // Variables on different parts of document
        $templateProcessor->setValue('fullname', $lastname . " " . $firstname . " " . $patronym);
        $templateProcessor->setValue('birthyear', $student->getBirthdate()->format("Y"));
        $templateProcessor->setValue('nationality', $nationality);
        $templateProcessor->setValue('graduation_year', $graduationYear);

        $templateProcessor->setValue('firstname_lastname', $firstname . " " . $lastname);
        $templateProcessor->setValue('faculty', $faculty);
        $templateProcessor->setValue('major', $major);
        $templateProcessor->setValue('advisor', $student->getStudentGroup()->getAdvisor()->getShortFullname());
        $templateProcessor->setValue('dean', $student->getFaculty()->getDean()->getShortFullname());

        $fileName = "reference_" . $this->latinize($lastname . " " . $firstname) . ".docx";
        $fileSavePath = $_SERVER['APP_REPORTS_DIR'] . "/" . $fileName;
        $templateProcessor->saveAs($fileSavePath);

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ALUMNUSSTUDENT, $student->getSystemId(), 'Download English-study certificate');

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Cache-Control: max-age=0');
        return new Response(file_get_contents($fileSavePath));
    }

    private function isTT($id) {
        $major_code = substr(strval($id), 2, 2);
        return ($major_code == '28' || $major_code == '29');
    }

    private function latinize($str) {
        $tk = array("Ý", "ý", "Ç", "ç", "Ä", "ä", "Ň", "ň", "Ö", "ö", "Ü", "ü", "Ž", "ž", "Ş", "ş");
        $lt = array("Y", "y", "C", "c", "A", "a", "N", "n", "O", "o", "U", "u", "Z", "z", "S", "s");

        $result = str_replace($tk, $lt, $str);

        return $result;
    }

    private function addMonthEnding($month) {
        switch ($month) {
            case 1:
                return 'ýanwaryndan';
            case 2:
                return 'fewralyndan';
            case 3:
                return 'martyndan';
            case 4:
                return 'aprelinden';
            case 5:
                return 'maýyndan';
            case 6:
                return 'iýunyndan';
            case 7:
                return 'iýulyndan';
            case 8:
                return 'awgustyndan';
            case 9:
                return 'sentýabryndan';
            case 10:
                return 'oktýabryndan';
            case 11:
                return 'noýabryndan';
            case 12:
                return 'dekabryndan';
        }
    }

    private function addYearEnding($year) {
        switch ($year) {
            case 2014 :
            case 2015:
            case 2017:
            case 2018:
            case 2020:
            case 2021:
            case 2022:
			case 2023:
                return $year . '-nji';
                break;
            case 2016:
            case 2019:
                return $year . '-njy';
        }
    }

    private function addDayEnding($day) {
        switch ($day) {
            case 1 :
            case 2:
            case 3:
            case 4:
            case 5:
            case 7:
            case 8:
            case 11:
            case 12:
            case 13:
            case 14:
            case 15:
            case 17:
            case 18:
            case 20:
            case 21:
            case 22:
            case 23:
            case 24:
            case 25:
            case 27:
            case 28:
            case 31:
                return $day . '-nji';
                break;
            case 6:
            case 9:
            case 10:
            case 16:
            case 19:
            case 26:
            case 29:
            case 30:
                return $day . '-njy';
        }
    }

    private function checkUser(User $user, string $password) {
        if (!$user) {
            return false;
        }
        if ($this->isGranted("ROLE_ADMIN")) {
            return true;
        }
        $adServer = $_SERVER['APP_AD_SERVER'];
        $ldap = ldap_connect($adServer);
        $ldaprdn = $_SERVER['APP_AD_DOMAIN'] . "\\" . $user->getUsername();
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        if (strlen($password) == 0) {
            $bind = 0;
        } else {
            $bind = @ldap_bind($ldap, $ldaprdn, $password);
        }

        if ($bind) {
            return true;
        } else {
//$this->logger->debug('Domain binding returned FALSE!');
            return false;
        }
    }

    /**
     * @Route("/interop/exporter/referrals/{ids?}", name="interop_exporter_referrals")
     */
    public function getReferrals(Request $request) {

//        $spreadsheet = new Spreadsheet();
//        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet = IOFactory::load($_SERVER['APP_REPORTS_DIR'] . "/resources/template_mastering.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $ids = $request->attributes->get('ids');
        if (!empty($ids)) {
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, 'Download simple group list' . $ids);
            $studentAbsenceRepository = $this->getDoctrine()->getRepository(StudentAbsence::class);
            $idsArray = explode(",", $ids);
            //$students = $group->getStudents();
//            $sheet->setCellValue('A1', 'No');
//            $sheet->setCellValue('B1', 'ID');
//            $sheet->setCellValue('C1', 'Student');
            $i = 2;
            $offset = 12;
            $yearTitle = $this->systemInfoManager->getCurrentCommencementYear() . "-" . $this->systemInfoManager->getCurrentGraduationYear();
            foreach ($idsArray as $id) {
                if ($i < 6) {
                    $studentAbsence = $studentAbsenceRepository->find($id);
                    $student = $studentAbsence->getStudent();
                    $faculty_name = str_replace("Faculty of", "", $student->getStudentGroup()->getDepartment()->getFaculty()->getNameEnglish());
                    $sheet->setCellValue('B' . (($i - 2) * $offset + 3), $studentAbsence->getId());
                    $sheet->setCellValue('H' . (($i - 2) * $offset + 3), $yearTitle);
                    $sheet->setCellValue('B' . (($i - 2) * $offset + 4), $student->getFullname() . " (" . $student->getSystemId() . ")");
                    $sheet->setCellValue('B' . (($i - 2) * $offset + 5), $faculty_name);
                    $sheet->setCellValue('G' . (($i - 2) * $offset + 5), $student->getMajorInEnglish());
                    $sheet->setCellValue('B' . (($i - 2) * $offset + 6), $studentAbsence->getCourse()->getNameEnglish());
                    $sheet->setCellValue('H' . (($i - 2) * $offset + 6), $studentAbsence->getDate()->format("d.m.Y"));
                    $sheet->setCellValue('B' . (($i - 2) * $offset + 9), $studentAbsence->getAuthor()->getShortFullname());
                    $sheet->setCellValue('B' . (($i - 2) * $offset + 10), $studentAbsence->getAuthor()->getDepartment()->getDepartmentHead()->getShortFullname());
                    $sheet->setCellValue('B' . (($i - 2) * $offset + 11), $student->getStudentGroup()->getDepartment()->getFaculty()->getDean()->getShortFullname());
                    $i++;
                }
            }

//
//            $worksheet->getCell('A1')->setValue('John');
//            $worksheet->getCell('A2')->setValue('Smith');
//
//            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
//            $writer->save('write.xls');

            $response = new Response();
            //$response->

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="student_referrals.xls"');
            header('Cache-Control: max-age=0');

            // redirect output to client browser
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');

            return new Response('ok');
        } else {
            return new Response('wrong parameters');
        }
    }

    /**
     * @Route("/interop/exporter/postdata", name="interop_exporter_postdata")
     */
    public function postData(Request $request) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $body = '';
        $body .= $data["template"];
        $template_file = $data['template'];
        //echo $_SERVER['APP_REPORTS_DIR'] . "/resources/" . $template_file;
        if (strlen($template_file) == 0 || !file_exists($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $template_file)) {
            return new Response('File not acceptable');
        }

        try {
            $spreadsheet = IOFactory::load($_SERVER['APP_REPORTS_DIR'] . "/resources/" . $template_file);
            $sheet = $spreadsheet->getActiveSheet();
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), 0, 0, 'Download post data excel');

            //enter data for speciall cells
            if ($data['special_cells']) {
                foreach ($data["special_cells"] as $special_cell) {
                    $sheet->setCellValue($special_cell[0], $special_cell[1]);
                }
            }

            if ($data['rows']) {
                //enter data for data rows
                $baseRow = $data["startrow"];
                $i = 0;
                foreach ($data["rows"] as $row) {
                    $r = $baseRow + $i;
                    $sheet->insertNewRowBefore($r, 1);
                    $column = 1;
                    foreach ($row as $field) {
                        if ($column > 3) {
                            if ($field[1] == 0) {
                                $sheet->getStyle($field[0] . $r)->getFont()->getColor()->setARGB($data["lightcolor"]);
                            } else {
                                $sheet->getStyle($field[0] . $r)->getFont()->getColor()->setARGB(Color::COLOR_BLACK);
                            }
                        }
                        $sheet->setCellValue($field[0] . $r, $this->cleanText($field[1]));
                        $column++;
                    }
                    $i++;
                }
                $sheet->removeRow($baseRow - 1, 1);
            }

            if ($data["footerRowData"]) {
                //enter date for footer row
                $column = 1;
                foreach ($data["footerRowData"] as $field) {
                    if ($column > 3) {
                        if ($field[1] == 0) {
                            $sheet->getStyle($field[0] . $r)->getFont()->getColor()->setARGB($data["lightcolor"]);
                        } else {
                            $sheet->getStyle($field[0] . $r)->getFont()->getColor()->setARGB(Color::COLOR_BLACK);
                        }
                    }
                    $sheet->setCellValue($field[0] . $r, $this->cleanText($field[1]));
                    $column++;
                }
            }

            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment;filename='" . $data['filename'] . "'");
            header("Cache-Control: max-age=0");

            // redirect output to client browser
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');

            return new Response('ok');
        } Catch (\Exception $ex) {
            $result = "Error: " . $ex->getMessage();
            return new Response($result);
        }
    }

    private function cleanText($str) {
        $result = strip_tags($str);
        $result = str_replace("\r", "", $result);
        $result = str_replace("\n", "", $result);
        $result = trim($result);
        return $result;
    }

}
