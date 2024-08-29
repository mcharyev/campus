<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Hr\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Hr\Entity\Employee;
use App\Entity\Faculty;
use App\Entity\Department;
use App\Entity\Teacher;
use App\Hr\Entity\Movement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Description of ReportsController
 *
 * @author NMammedov
 */
class ReportController extends AbstractController {

//put your code here
    private const LATE_THRESHOLD = 30;
    private const EARLY_THRESHOLD = 5;

    /**
     * @Route("/hr/report", name="hr_report")
     * @Route("/hr/report/today", name="hr_report_today")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_HR");
//$searchField = $request->attributes->get('searchField');

        $content = $this->calculateMonthlyReport(2, 2020, 300);
        return $this->render('hr/controller/report/index.html.twig', [
                    'controller_name' => 'EmployeeController',
                    'content' => $content,
                    'title' => 'Reports'
        ]);
    }

    /**
     * @Route("/hr/report/dailyreport/", name="hr_report_employee_daily")
     * @Route("/hr/report/dailyreport/{day?}/{month?}/{year?}", name="hr_report_employee_daily")
     */
    public function calculateDailyReport(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_HR", "ROLE_DEAN", "ROLE_DEPARTMENTHEAD", "ROLE_AAHR"]);
        $content = '';
        $turkmen_weekdays = array('Sun' => 'Ýekşenbe', 'Sat' => 'Şenbe', 'Mon' => 'Duşenbe', 'Tue' => 'Sişenbe', 'Wed' => 'Çarşenbe', 'Thu' => 'Penşenbe', 'Fri' => 'Anna');
        $turkmen_weekdays2 = array('Duşenbe', 'Sişenbe', 'Çarşenbe', 'Penşenbe', 'Anna', 'Şenbe', 'Ýekşenbe');
        $turkmen_months = array("", "ýanwar", "fewral", "mart", "aprel", "maý", "iýun", "iýul", "awgust", "sentýabr", "oktýabr", "noýabr", "dekabr");

        $day = $request->attributes->get('day');
        $month = $request->attributes->get('month');
        $year = $request->attributes->get('year');
        if ($day && $month && $year) {
            $sdate = new \DateTime();
            $sdate = $sdate->setDate($year, $month, $day);
            $wday = $sdate->format('N');
            $tday = $day;
            $tmonth = $month;
            $tyear = $year;
            $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
        } else {
            $sdate = new \DateTime();
            //$sdate = $sdate->setDate($year, $month, $day);
            $wday = $sdate->format('N');
            $tday = $day;
            $tmonth = $month;
            $tyear = $year;
            $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
        }

        $date = time(); //date('m/d/Y h:i:s a', time()); //mktime(0, 0, 0, $day, $month, $year);
        $sqldate = date("Y-m-d", $date);
        $wday = date("N", $date);
        $tday = date("j", $date);
        $tmonth = date("n", $date);
        $tyear = date("Y", $date);
//echo $wday;
        $tstring = date("d-m-Y", $date) . " " . $turkmen_weekdays2[$wday - 1];

        if (intval($day) != 0) {
            $sdate = new \DateTime();
            $sdate = $sdate->setDate($year, $month, $day);
            $wday = $sdate->format('N');
            $tday = $day;
            $tmonth = $month;
            $tyear = $year;
            $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
        }

        $content .= "<b>All employees movement report for today [" . $tstring . "]</b><br>";

        $style_black = "style=''";
        $style_red = "style='color:red;'";
        $style_orange = "style='color:orange;'";
        $cellstyle = '';
        $exit_cellstyle = '';
        $style = "style='font-weight:bold;'";

        $content .= "<table class='table table-bordered table-sm table-striped small' id='mainTable'>";
        $content .= "<thead><tr $style>";
        $content .= "<th $cellstyle>Number</th>";
        $content .= "<th $cellstyle>Employee</th>";
        $content .= "<th $cellstyle>Nominal Entry</th>";
        $content .= "<th $cellstyle>Real Entry</th>";
        $content .= "<th $cellstyle>Difference</th>";
        $content .= "<th $cellstyle>Entry Status</th>";
        $content .= "<th $cellstyle>Nominal Exit</th>";
        $content .= "<th $cellstyle>Real Exit</th>";
        $content .= "<th $cellstyle>Exit Difference</th>";
        $content .= "<th $cellstyle>Exit Status</th>";
        $content .= "<th $cellstyle>Total hours</th>";
        $content .= "<th $cellstyle>Position</th>";
        $content .= "</tr></thead><tbody>";
        $ideal_date = new \DateTime();
        $ideal_date = $ideal_date->setDate($tyear, $tmonth, $tday);
        $ideal_leave_date = clone $ideal_date;
        $movementRepository = $this->getDoctrine()->getRepository(Movement::class);
        $employeeRepository = $this->getDoctrine()->getRepository(Employee::class);
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        if ($this->isGranted("ROLE_HR") || $this->isGranted("ROLE_AAHR")) {
            $employees = $employeeRepository->findBy(['status' => 1], ['systemId' => 'ASC']);
        } elseif ($this->isGranted("ROLE_DEAN")) {
            $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            $faculty = $teacherRepository->getManagedFaculty(['teacher_id' => $viewingTeacher->getId()]);
            $departments = $faculty->getDepartments();
            $employees = [];
            foreach ($departments as $department) {
                $employees_department = $employeeRepository->findBy(['status' => 1,
                    'departmentCode' => $department->getSystemId()], ['systemId' => 'ASC']);
                foreach ($employees_department as $employee) {
                    $employees[] = $employee;
                }
            }
        } elseif ($this->isGranted("ROLE_DEPARTMENTHEAD")) {
            $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            $department = $teacherRepository->getManagedDepartment(['teacher_id' => $viewingTeacher->getId()]);
            $employees = [];
            $employees_department = $employeeRepository->findBy(['status' => 1,
                'departmentCode' => $department->getSystemId()], ['systemId' => 'ASC']);
            foreach ($employees_department as $employee) {
                $employees[] = $employee;
            }
        } elseif ($this->isGranted("ROLE_TEACHER")) {
            $employees = [];
        }

        foreach ($employees as $employee) {
            $worktimeCategory = $employee->getWorktimeCategory();
            if ($worktimeCategory == 1) {
                $ideal_date->setTime(8, 00, 0);
                $ideal_leave_date->setTime(15, 00, 0);
            } elseif ($worktimeCategory == 2) {
                $ideal_date->setTime(8, 00, 0);
                $ideal_leave_date->setTime(16, 30, 0);
            } elseif ($worktimeCategory == 3) {
                $ideal_date->setTime(8, 00, 0);
                $ideal_leave_date->setTime(12, 30, 0);
            } else {
                $ideal_date->setTime(8, 0, 0);
                $ideal_leave_date->setTime(16, 0, 0);
                if ($wday == 6) {
                    $ideal_leave_date->setTime(13, 0, 0);
                }
            }

            $entry = $movementRepository->getMovement($tday, $tmonth, $tyear, $employee->getSystemId(), 0);
            $exit = $movementRepository->getMovement($tday, $tmonth, $tyear, $employee->getSystemId(), 1);
//$content .= "Date:".$entry['movement_datetime']." : ".strval($entry['found'])."<br>";

            $datetime1 = new \DateTime($entry['movement_datetime']);
            $datetime2 = new \DateTime($exit['movement_datetime']);
            $interval = $datetime1->diff($datetime2);
            $hours_in = $interval->format('%H') . ":" . $interval->format('%I');

            $late_interval = $ideal_date->diff($datetime1);
            $late_minutes = (int) $late_interval->format('%r%i');
            $late_text = $late_interval->format('%H') . ":" . $late_interval->format('%I');
            $late_sign = "";
            if ($datetime1->format('H') == '00') {
                $late_sign = " - ýok -";
                $cellstyle = $style_red;
            } elseif ($late_minutes <= 0) {
                $cellstyle = $style_black;
                $late_sign = " - ir -";
            } elseif ($late_minutes > 0 && $late_minutes < $this::LATE_THRESHOLD) {
                $late_sign = " - giç -";
                $cellstyle = $style_orange;
            } elseif ($late_minutes >= $this::LATE_THRESHOLD) {
                $late_sign = " - örän giç -";
                $cellstyle = $style_red;
            } else {
                $cellstyle = $style_black;
            }

            $exit_interval = $ideal_leave_date->diff($datetime2);
            $exit_minutes = (int) $exit_interval->format('%r%i');
            $exit_text = $exit_interval->format('%H') . ":" . $exit_interval->format('%I');
            $exit_sign = "";
            if ($datetime2->format('H') == '00') {
                $exit_cellstyle = $style_red;
                $exit_sign = " - ýok -";
            } elseif ($exit_minutes < 0 && abs($exit_minutes) < $this::EARLY_THRESHOLD) {
                $exit_cellstyle = $style_orange;
                $exit_sign = " - ir -";
            } elseif ($exit_minutes < 0 && abs($exit_minutes) >= $this::EARLY_THRESHOLD) {
                $exit_sign = " - örän ir -";
                $exit_cellstyle = $style_red;
            } elseif ($exit_minutes > 0) {
                $exit_sign = " - giç -";
                $exit_cellstyle = $style_black;
            } else {
                $exit_cellstyle = $style_black;
            }

            $content .= "<tr>";
            $content .= "<td $style_black>" . $employee->getSystemId() . "</td>";
            $content .= "<td $style_black><a href='/hr/report/employeemonthly/" . $employee->getSystemId() . "/" . $tmonth . "/" . $tyear . "'>" . $employee->getLastnameFirstname() . "</a></td>";
            $content .= "<td $style_black>" . $ideal_date->format('H:i') . "</td>";
            $content .= "<td $cellstyle>" . $datetime1->format('H:i') . "</td>";
            $content .= "<td $cellstyle>" . $late_text . "</td>";
            $content .= "<td $cellstyle>" . $late_sign . "</td>";
            $content .= "<td $style_black>" . $ideal_leave_date->format('H:i') . "</td>";
            $content .= "<td $exit_cellstyle>" . $datetime2->format('H:i') . "</td>";
            $content .= "<td $exit_cellstyle>" . $exit_text . "</td>";
            $content .= "<td $exit_cellstyle>" . $exit_sign . "</td>";
            $content .= "<td $style_black>$hours_in</td>";
            $content .= "<td $style_black>" . $employee->getPosition() . "</td>";
            $content .= "</tr>";
        }
        $content .= "</tbody></table>";
        if ($this->isGranted("ROLE_HR")) {
            $templateFile = 'hr/controller/report/index.html.twig';
        } else {
            $templateFile = 'teacher/worktimereport.html.twig';
        }

        return $this->render($templateFile, [
                    'controller_name' => 'EmployeeController',
                    'content' => $content,
                    'title' => 'Employee Movement Today\'s Report'
        ]);
    }

    /**
     * @Route("/hr/report/employeemonthly/{employee_number}/{month?}/{year?}", name="hr_report_employee_monthly")
     */
    public function employeeMonthlyReport(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_HR", "ROLE_DEAN", "ROLE_DEPARTMENTHEAD", "ROLE_AAHR"]);
        $employeeNumber = $request->attributes->get('employee_number');

        $month = $request->attributes->get('month');
        $year = $request->attributes->get('year');

        $content = '';
        $turkmen_weekdays = array('Sun' => 'Ýekşenbe', 'Sat' => 'Şenbe', 'Mon' => 'Duşenbe', 'Tue' => 'Sişenbe', 'Wed' => 'Çarşenbe', 'Thu' => 'Penşenbe', 'Fri' => 'Anna');
        $turkmen_weekdays2 = array('Duşenbe', 'Sişenbe', 'Çarşenbe', 'Penşenbe', 'Anna', 'Şenbe', 'Ýekşenbe');
        $turkmen_months = array("", "ýanwar", "fewral", "mart", "aprel", "maý", "iýun", "iýul", "awgust", "sentýabr", "oktýabr", "noýabr", "dekabr");

        if (!$month) {
            $sdate = new \DateTime();
            //$sdate = $sdate->setDate($year, $month, $day);
            $wday = $sdate->format('N');
            $month = $sdate->format('n');
            $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
        }

        if (!$year) {
            $sdate = new \DateTime();
            //$sdate = $sdate->setDate($year, $month, $day);
            $wday = $sdate->format('N');
            $year = $sdate->format('Y');
            $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
        }

        $working_days_count = $this->countDays($year, $month, array(0));

        $late_sum = 0;
        $early_sum = 0;

        $ideal_entry_date = new \DateTime();
        $ideal_leave_date = new \DateTime();

        $ideal_entry_date->setTime(8, 30, 0);
        $ideal_leave_date->setTime(15, 0, 0);

        $movementRepository = $this->getDoctrine()->getRepository(Movement::class);
        $employeeRepository = $this->getDoctrine()->getRepository(Employee::class);
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        $employee = $employeeRepository->findOneBy(['systemId' => $employeeNumber], ['systemId' => 'ASC']);
        $authorized = false;
        if ($this->isGranted("ROLE_HR") || $this->isGranted("ROLE_AAHR")) {
            $authorized = true;
        } elseif ($this->isGranted("ROLE_DEAN")) {
            $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            $faculty = $teacherRepository->getManagedFaculty(['teacher_id' => $viewingTeacher->getId()]);
            $departments = $faculty->getDepartments();
            foreach ($departments as $department) {
                if ($employee->getDepartmentCode() == $department->getSystemId()) {
                    $authorized = true;
                    break;
                }
            }
        } elseif ($this->isGranted("ROLE_DEPARTMENTHEAD")) {
            $viewingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            $department = $teacherRepository->getManagedDepartment(['teacher_id' => $viewingTeacher->getId()]);
            if ($employee->getDepartmentCode() == $department->getSystemId()) {
                $authorized = true;
            }
        }

        if (!$employee) {
            $content .= "Employee not found!";
        } else {
            if (!$authorized) {
                $content .= "You are not authorized to view this information.";
            }
        }

        if ($employee && $authorized) {
            $worktimeCategory = $employee->getWorktimeCategory();
            $content .= "<b>" . $employee->getLastnameFirstname() . " üçin " . $year . "-nji(y) ýylyň " . $turkmen_months[$month] . " aýy üçin giriş-çykyş hasabaty</b><br>";

            $style_green = "style='color:green;font-weight:bold;'";
            $style_red = "style='color:red;font-weight:bold;'";
            $cellstyle = '';
            $cellstyle_exit = '';
            $style = "style='font-weight:bold;'";
            $style_black = "style='color:black;'";

            /* $begin = new DateTime("'".$year."-".$);
              $end = new DateTime($trimester_end_dates[TRIMESTER]);
              $interval = DateInterval::createFromDateString('1 day');
              $period = new DatePeriod($begin, $interval, $end);
             */
            $content .= "<table class='table table-bordered table-sm table-striped small' id='mainTable'>";
            $content .= "<thead><tr $style>";
            $content .= "<th $cellstyle>Güni</th>";
            $content .= "<th $cellstyle>Hepdäniň güni</th>";
            $content .= "<th $cellstyle>Giriş</th>";
            $content .= "<th $cellstyle>Çykyş</th>";
            $content .= "<th $cellstyle>Jemi sagat</th>";
            $content .= "<th $cellstyle>Gijä galmak</th>";
            $content .= "<th $cellstyle>Ir gitmek</th>";
            $content .= "</tr></thead><tbody>";
            $dayscount = cal_days_in_month(CAL_GREGORIAN, $month, $year); // prints 31
            for ($i = 1; $i <= $dayscount; $i++) {
                $date = mktime(0, 0, 0, $month, $i, $year);
                $sqldate = date("Y-m-d", $date);
                $wday = date("N", $date);
                $month = date("n", $date);
                if ($wday != 7) {
                    $ideal_entry_date->setDate($year, $month, $i);
                    $ideal_leave_date->setDate($year, $month, $i);

                    if ($worktimeCategory == 1) {
                        $ideal_entry_date->setTime(8, 00, 0);
                        $ideal_leave_date->setTime(15, 00, 0);
                    } elseif ($worktimeCategory == 2) {
                        $ideal_entry_date->setTime(8, 00, 0);
                        $ideal_leave_date->setTime(16, 30, 0);
                    } elseif ($worktimeCategory == 3) {
                        $ideal_entry_date->setTime(8, 00, 0);
                        $ideal_leave_date->setTime(12, 30, 0);
                    } else {
                        $ideal_entry_date->setTime(8, 0, 0);
                        $ideal_leave_date->setTime(16, 0, 0);
                        if ($wday == 6) {
                            $ideal_leave_date->setTime(13, 0, 0);
                        }
                    }

                    $entry = $movementRepository->getMovement($i, $month, $year, $employee->getSystemId(), 0);
                    $exit = $movementRepository->getMovement($i, $month, $year, $employee->getSystemId(), 1);

                    $datetime1 = new \DateTime($entry['movement_datetime']);
                    $datetime2 = new \DateTime($exit['movement_datetime']);
                    $interval = $datetime1->diff($datetime2);
                    $hours_in = $interval->format('%H') . ":" . $interval->format('%i');

                    $late_interval = $ideal_entry_date->diff($datetime1);
                    $late_minutes = $this->differenceMinutes($late_interval); //(int)$late_interval->format('%r%i');
                    $late_text = $late_interval->format('%H') . ":" . $late_interval->format('%I');
                    $late_sign = "";

                    $early_interval = $ideal_leave_date->diff($datetime2);
                    $early_minutes = $this->differenceMinutes($early_interval) * -1;

//DateInterval::invert will be 1 if the second date is before the first date, and 0 if the the second date is on or after the first date.
//if($late_interval->invert)
                    if ($datetime1->format('H') == '00') {
                        $late_minutes = 0;
                    }

                    if ($late_minutes > 0) {
                        $late_sum += $late_minutes;
                    }

                    if ($late_minutes > $this::LATE_THRESHOLD) {
                        $cellstyle = $style_red;
                    } else {
                        $cellstyle = $style_black;
                    }

                    if ($early_minutes > $this::LATE_THRESHOLD) {
                        $cellstyle_exit = $style_red;
                    } else {
                        $cellstyle_exit = $style_black;
                    }

                    $content .= "<tr>";
                    $content .= "<td>" . date("d-m-Y", $date) . "</td>";
                    $content .= "<td>" . $turkmen_weekdays[date("D", $date)] . "</td>";
                    $content .= "<td>" . $datetime1->format('H:i:s') . "</td>";
                    $content .= "<td>" . $datetime2->format('H:i:s') . "</td>";
                    $content .= "<td>$hours_in</td>";
                    $content .= "<td $cellstyle>" . $late_minutes . "</td>";
                    $content .= "<td $cellstyle_exit>" . $early_minutes . "</td>";
                    $content .= "</tr>";
                }
            }
            $content .= "</tbody></table>";
            $content .= "<br>Gijä galyş ortaça: " . round(($late_sum / $working_days_count), 0);
        }
        //return $content;
        return $this->render('hr/controller/report/index.html.twig', [
                    'controller_name' => 'EmployeeController',
                    'employeeNumber' => $employeeNumber,
                    'reportMonth' => $month,
                    'reportYear' => $year,
                    'content' => $content,
                    'title' => 'Monthly Movement Report for ' . $employee->getLastnameFirstname()
        ]);
    }

    /**
     * @Route("/hr/report/monthlyreport/{month?}/{year?}/{facultyCode?}", name="hr_report_employee_monthly_total")
     */
    public function monthlyReportTotal(Request $request) {

        $month = $request->attributes->get('month');
        $year = $request->attributes->get('year');
        $facultyCode = $request->attributes->get('facultyCode');

        $content = '';
        $turkmen_weekdays = array('Sun' => 'Ýekşenbe', 'Sat' => 'Şenbe', 'Mon' => 'Duşenbe', 'Tue' => 'Sişenbe', 'Wed' => 'Çarşenbe', 'Thu' => 'Penşenbe', 'Fri' => 'Anna');
        $turkmen_weekdays2 = array('Duşenbe', 'Sişenbe', 'Çarşenbe', 'Penşenbe', 'Anna', 'Şenbe', 'Ýekşenbe');
        $turkmen_months = array("", "ýanwar", "fewral", "mart", "aprel", "maý", "iýun", "iýul", "awgust", "sentýabr", "oktýabr", "noýabr", "dekabr");

        if ($month && $year) {
            $sdate = new \DateTime();
            $sdate = $sdate->setDate($year, $month, 1);
            $wday = $sdate->format('N');
            $tday = 1;
            $tmonth = $month;
            $tyear = $year;
            $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
        } else {
            $sdate = new \DateTime();
            //$sdate = $sdate->setDate($year, $month, $day);
            $wday = $sdate->format('N');
            $tday = $sdate->format('j');
            $tmonth = $sdate->format('n');
            $tyear = $sdate->format('Y');
            $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
            $month = $tmonth;
            $year = $tyear;
        }

        $content .= "<b style='font-weight:bold;'>Işgärleriň " . $turkmen_months[$month] . " aýy üçin giriş-çykyş hasabaty</b><br><br>";

        //number of working days in month
        $working_days_count = $this->countDays($year, $month, array(0));

        $style_black = "style=''";
        $style_red = "style='color:red;'";
        $cellstyle = '';
        $style = "style='font-weight:bold;'";

        $content .= "<table class='table table-bordered table-sm table-striped small' id='mainTable'>";
        $content .= "<thead><tr $style>";
        $content .= "<th $cellstyle>Belgisi</th>";
        $content .= "<th $cellstyle>Işgäriň ady</th>";
        $content .= "<th $cellstyle>Jemi iş güni</th>";
        $content .= "<th $cellstyle>Geliş ýazgysy ýok günleriň sany</th>";
        $content .= "<th $cellstyle>Gidiş ýazgysy ýok günleriň sany</th>";
        $content .= "<th $cellstyle>Giç gelen günleri</th>";
        $content .= "<th $cellstyle>Ir giden günleri</th>";
        $content .= "<th $cellstyle>Ortaça gijä galyş (minut/gün)</th>";
        $content .= "<th $cellstyle>Ortaça ir gidiş (minut/gün)</th>";
        $content .= "<th $cellstyle>Jemi gijä galyş (minut/gün)</th>";
        $content .= "<th $cellstyle>Jemi ir gidiş (minut/gün)</th>";
        $content .= "<th $cellstyle>Wezipesi</th>";
        $content .= "</tr></thead><tbody>";

        $limit = 50;
        $count = 0;

        $ideal_date = new \DateTime();
        $ideal_date->setDate($tyear, $tmonth, $tday);
        $ideal_date->setTime(8, 0, 0);
        $movementRepository = $this->getDoctrine()->getRepository(Movement::class);
        $employeeRepository = $this->getDoctrine()->getRepository(Employee::class);
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $departmentRepository = $this->getDoctrine()->getRepository(Department::class);
        $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
        if ($facultyCode) {
            $faculty = $facultyRepository->findOneBy(['systemId' => $facultyCode]);
            $departments = $faculty->getDepartments();
            $employees = [];
            foreach ($departments as $department) {
                $employees_department = $employeeRepository->findBy(['status' => 1,
                    'departmentCode' => $department->getSystemId()], ['systemId' => 'ASC']);
                foreach ($employees_department as $employee) {
                    $employees[] = $employee;
                }
            }
        } else {
            $employees = $employeeRepository->findBy(['status' => 1]);
        }
//        $employees = $employeeRepository->findBy(['status' => 1, 'departmentCode' => $departmentCode], ['systemId' => 'ASC']);
        foreach ($employees as $employee) {
            if ($count > $limit) {
                break;
            }
            $stats = $this->getMovementStats($movementRepository, $month, $year, $employee);

            $content .= "<tr>";
            $content .= "<td $style_black>" . $employee->getSystemId() . "</td>";
            $content .= "<td $style_black><a href='/hr/report/employeemonthly/" . $employee->getSystemId() . "/" . $tmonth . "/" . $tyear . "'>" . $employee->getLastnameFirstname() . "</a></td>";
            $content .= "<td $cellstyle>" . $working_days_count . "</td>";
            $content .= "<td $cellstyle>" . $stats['absent_days'] . "</td>";
            $content .= "<td $cellstyle>" . $stats['no_leave_days'] . "</td>";
            $content .= "<td $cellstyle>" . $stats['entry_late_days'] . "</td>";
            $content .= "<td $cellstyle>" . $stats['exit_early_days'] . "</td>";
            $content .= "<td $cellstyle>" . round($stats['entry_late_sum'] / $working_days_count, 0) . "</td>";
            $content .= "<td $cellstyle>" . round($stats['exit_early_sum'] / $working_days_count, 0) * -1 . "</td>";
            $content .= "<td $cellstyle>" . round($stats['entry_late_total_sum'] / $working_days_count, 0) . "</td>";
            $content .= "<td $cellstyle>" . round($stats['exit_early_total_sum'] / $working_days_count, 0) * -1 . "</td>";
            $content .= "<td $style_black>" . $employee->getPosition() . "</td>";
            $content .= "</tr>";

            $count++;
        }

        $content .= "</tbody></table>";
        //return $content;
        return $this->render('hr/controller/report/index.html.twig', [
                    'controller_name' => 'EmployeeController',
                    'content' => $content,
                    'title' => 'Monthly Consolidated Movement Report'
        ]);
    }

    /**
     * @Route("/hr/report/birthdays", name="hr_report_employee_monthly_birthdays")
     */
    public function birthdaysReport(Request $request) {

        $content = '';
        $turkmen_weekdays = array('Sun' => 'Ýekşenbe', 'Sat' => 'Şenbe', 'Mon' => 'Duşenbe', 'Tue' => 'Sişenbe', 'Wed' => 'Çarşenbe', 'Thu' => 'Penşenbe', 'Fri' => 'Anna');
        $turkmen_weekdays2 = array('Duşenbe', 'Sişenbe', 'Çarşenbe', 'Penşenbe', 'Anna', 'Şenbe', 'Ýekşenbe');
        $turkmen_months = array("", "ýanwar", "fewral", "mart", "aprel", "maý", "iýun", "iýul", "awgust", "sentýabr", "oktýabr", "noýabr", "dekabr");

        $sdate = new \DateTime();
        //$sdate = $sdate->setDate($year, $month, $day);
        $wday = $sdate->format('N');
        $tday = $sdate->format('j');
        $tmonth = $sdate->format('n');
        $tyear = $sdate->format('Y');
        $tstring = $sdate->format('d-m-Y') . " " . $turkmen_weekdays2[$wday - 1];
        $month = $tmonth;
        $year = $tyear;

        $content .= "<table class='table table-condensed table-striped'>";
        $content .= "<tr>";
        $content .= "<th>Belgisi</th>";
        $content .= "<th>Işgäriň ady</th>";
        $content .= "<th>Doglan güni</th>";
        $content .= "<th>Wezipesi</th>";
        $content .= "</tr>";


        $employeeRepository = $this->getDoctrine()->getRepository(Employee::class);
        $employees = $employeeRepository->findBy([], ['systemId' => 'ASC']);
        foreach ($employees as $employee) {
            if ($employee->getBirthdate()) {
                if ($employee->getBirthdate()->format("m") == $sdate->format('m') && $employee->getBirthdate()->format("d") >= $sdate->format('d')) {
                    $content .= "<tr>";
                    $content .= "<td>" . $employee->getSystemId() . "</td>";
                    $content .= "<td><a href='/hr/report/employeemonthly/" . $employee->getSystemId() . "/" . $tmonth . "/" . $tyear . "'>" . $employee->getLastnameFirstname() . "</a></td>";
                    $today = "<span style='color:green'>ŞU AÝ</span>";
                    if ($employee->getBirthdate()->format("d.m") == $sdate->format('d.m')) {
                        $today = "<span style='color:green'>ŞU GÜN</span>";
                    }
                    $content .= "<td>" . $employee->getBirthdate()->format("d.m.y") . " " . $today . " </td>";

                    $content .= "<td>" . $employee->getPosition() . "</td>";
                    $content .= "</tr>";
                }
            }
        }

        $content .= "</table>";
        //return $content;
        return $this->render('hr/controller/report/index.html.twig', [
                    'controller_name' => 'EmployeeController',
                    'content' => $content,
                    'title' => 'Birthdays report'
        ]);
    }

    function getMovementStats(ServiceEntityRepository $movementRepository, $month, $year, Employee $employee) {

        $fresult = [
            'absent_days' => 0,
            'no_leave_days' => 0,
            'entry_late_days' => 0,
            'entry_late_sum' => 0,
            'exit_early_days' => 0,
            'exit_early_sum' => 0,
            'entry_late_total_sum' => 0,
            'exit_early_total_sum' => 0
        ];

        $ideal_entry_date = new \DateTime();
        $ideal_leave_date = new \DateTime();

        $worktimeCategory = $employee->getWorktimeCategory();

        $dayscount = cal_days_in_month(CAL_GREGORIAN, $month, $year); // prints 31

        $absent_days = 0;
        $no_leave_days = 0;

        $entry_late_days = 0;
        $entry_late_sum = 0;

        $exit_early_days = 0;
        $exit_early_sum = 0;

        $entry_late_total_sum = 0;
        $exit_early_total_sum = 0;

        for ($i = 1; $i <= $dayscount; $i++) {
            $date = mktime(0, 0, 0, $month, $i, $year);
//            $sqldate = date("Y-m-d", $date);
            $wday = date("N", $date);
            $month = date("n", $date);
            //echo "Day:".$wday."<br>";
            if ($wday != 7) {
                $ideal_entry_date->setDate($year, $month, $i);
                $ideal_leave_date->setDate($year, $month, $i);

                if ($worktimeCategory == 1) {
                    $ideal_entry_date->setTime(8, 00, 0);
                    $ideal_leave_date->setTime(15, 00, 0);
                } elseif ($worktimeCategory == 2) {
                    $ideal_entry_date->setTime(8, 00, 0);
                    $ideal_leave_date->setTime(16, 30, 0);
                } elseif ($worktimeCategory == 3) {
                    $ideal_entry_date->setTime(8, 00, 0);
                    $ideal_leave_date->setTime(12, 30, 0);
                } else {
                    $ideal_entry_date->setTime(8, 0, 0);
                    $ideal_leave_date->setTime(16, 0, 0);
                    if ($wday == 6) {
                        $ideal_leave_date->setTime(13, 0, 0);
                    }
                }

                $entry = $movementRepository->getMovement($i, $month, $year, $employee->getSystemId(), 0);
                $exit = $movementRepository->getMovement($i, $month, $year, $employee->getSystemId(), 1);
                //echo "Entry:".$entry['found']."<br>";
                if (!$entry['found']) {
                    //echo 'yes ';
                    $absent_days++;
                } else {
                    $datetime1 = new \DateTime($entry['movement_datetime']);
                    $entry_interval = $ideal_entry_date->diff($datetime1);
                    $entry_late_minutes = $this->differenceMinutes($entry_interval);
                    //echo "late:".$entry_late_minutes."<br>";
                    if ($entry_late_minutes > 0) {
                        if ($entry_late_minutes > $this::LATE_THRESHOLD) {
                            $entry_late_days++;
                        }
                        $entry_late_sum += $entry_late_minutes;
                    }
                    $entry_late_total_sum += $entry_late_minutes;
                }

                if (!$exit['found']) {
                    //echo 'yes ';
                    $no_leave_days++;
                } else {
                    $datetime2 = new \DateTime($exit['movement_datetime']);

                    $exit_interval = $ideal_leave_date->diff($datetime2);
                    $exit_early_minutes = $this->differenceMinutes($exit_interval);
                    //echo "early:".$exit_early_minutes."<br>";
                    if ($exit_early_minutes < 0) {
                        if ($exit_early_minutes > $this::EARLY_THRESHOLD) {
                            $exit_early_days++;
                        }
                        $exit_early_sum += $exit_early_minutes;
                    }
                    $exit_early_total_sum += $exit_early_minutes;
                }
            }
        }

        $fresult['absent_days'] = $absent_days;
        $fresult['no_leave_days'] = $no_leave_days;
        $fresult['entry_late_days'] = $entry_late_days;
        $fresult['exit_early_days'] = $exit_early_days;
        $fresult['entry_late_sum'] = $entry_late_sum;
        $fresult['exit_early_sum'] = $exit_early_sum;
        $fresult['entry_late_total_sum'] = $entry_late_total_sum;
        $fresult['exit_early_total_sum'] = $exit_early_total_sum;

        return $fresult;
    }

    private function countDays($year, $month, $ignore) {
        $count = 0;
        $counter = mktime(0, 0, 0, $month, 1, $year);
        while (date("n", $counter) == $month) {
            if (in_array(date("w", $counter), $ignore) == false) {
                $count++;
            }
            $counter = strtotime("+1 day", $counter);
        }
        return $count;
    }

    private function differenceMinutes($interval) {
        //$minutes = $interval->days * 24 * 60;
        $minutes = 0;
        $minutes += $interval->h * 60;
        $minutes += $interval->i;
        if ($interval->invert == 1)
            $minutes = $minutes * -1;

        return $minutes;
    }

}
