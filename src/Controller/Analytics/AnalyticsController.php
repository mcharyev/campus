<?php

namespace App\Controller\Analytics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\StudyProgram;
use App\Entity\Group;
use App\Entity\AlumnusStudent;
use App\Entity\Department;
use App\Entity\Faculty;
use App\Service\SystemInfoManager;
use App\Entity\EnrolledStudent;

class AnalyticsController extends AbstractController {

    private $systemInfoManager;
    private $currentYear;

    public function __construct(SystemInfoManager $systemInfoManager) {
        $this->systemInfoManager = $systemInfoManager;
        $this->currentYear = $this->systemInfoManager->getCurrentCommencementYear();
    }

    /**
     * @Route("/faculty/analytics", name="faculty_analytics")
     */
    public function index() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => ''
        ]);
    }

    /**
     * @Route("/faculty/analytics/major_count", name="faculty_analytics_major_count")
     */
    public function major_count() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $content = '';
        $content .= "<h3>Halkara ynsanperwer ylymlary we ösüş uniwersitetinde bilim alýan talyplar barada maglumat</h3><p>";
        $content .= "<table border=1 cellspacing=0 cellpadding=5>";
        $content .= "<tr style='font-weight:bold;'><td>T/b</td><td>Hünär</td><td>Kär</td><td>Jemi</td><td>D</td><td>1</td><td>2</td><td>3</td><td>4</td><td>M</td><td>Hemmesi</td></tr>";
        $check_year = $this->systemInfoManager->getCurrentGraduationYear() + 4;
        $years = [0, 1, 2, 3, 4];

        $groupRepository = $this->getDoctrine()->getRepository(Group::class);

        $programRepository = $this->getDoctrine()->getRepository(StudyProgram::class);
        $programs = $programRepository->findAll();

        $majors = array();
        $counts[0] = array('total' => 0, 'male' => 0, 'female' => 0);
        $counts[1] = array('total' => 0, 'male' => 0, 'female' => 0);
        $counts[2] = array('total' => 0, 'male' => 0, 'female' => 0);
        $counts[3] = array('total' => 0, 'male' => 0, 'female' => 0);
        $counts[4] = array('total' => 0, 'male' => 0, 'female' => 0);
        $counts[5] = array('total' => 0, 'male' => 0, 'female' => 0);
        $university = array(0, 0, 0, 0, 0, 0);
        $i = 1;
        foreach ($programs as $program) {
            if (!in_array($program->getNameTurkmen(), $majors)) {
                $majors[] = $program->getNameTurkmen();
                $content .= "<tr valign='top'>";
                $content .= "<td rowspan=4>" . $i . "</td>";
                $content .= "<td rowspan=4>" . $program->getNameTurkmen() . "</td>";
//$content .= "<td>1</td>";
//$content .= "</tr><tr>";
//$content .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
                $content .= "</tr>";
//$content .= "<td></td><td></td><td></td>";
//$content .= "<td>Jemi</td>";
//$content .= "<td>gyz</td>";
//$content .= "<td>oglan</td>";
                $grandtotal = 0;
                $maletotal = 0;
                $femaletotal = 0;
                foreach ($years as $year) {
                    //echo "Checking year - " . $program->getNameTurkmen() . "-" . ($check_year - $year) . "<br>";
                    $groups = $groupRepository->findBy(
                            ['graduationYear' => ($check_year - $year)],
                            ['graduationYear' => 'DESC']
                    );
//                    $sql2 = "SELECT * FROM iuhd_groups WHERE group_major_tk = '" . $row['program_name_tk'] . "' AND group_year = " . ($check_year - $year) . " ORDER BY group_year DESC";
//                    //echo $sql2."<br>";
//                    $result2 = $conn->query($sql2);
                    $total = 0;
                    $male = 0;
                    $female = 0;

                    $current_year = $year;

                    foreach ($groups as $group) {
                        if ($group->getStudyProgram()->getNameTurkmen() == $program->getNameTurkmen()) {
                            //echo $group->getLetterCode() . " - " . $year . "<br>";
                            $male += $group->getMaleStudentCount();
                            $female += $group->getFemaleStudentCount();
                            $total += $male + $female;
                        }
                    }

//                    if ($row2['group_code'] == 19511 || $row2['group_code'] == 19521) {
//                        $current_year = 5;
//                        $counts[4]['total'] = 0;
//                        $counts[4]['female'] = 0;
//                        $counts[4]['male'] = 0;
//                    }

                    $counts[$current_year]['total'] = $total;
                    $counts[$current_year]['female'] = $female;
                    $counts[$current_year]['male'] = $male;

                    $grandtotal += $total;
                    $maletotal += $male;
                    $femaletotal += $female;
                    $university[$current_year] += $total;
                }
                $content .= "<tr>";
                $content .= "<td></td><td>Jemi</td><td>" . $counts[0]['total'] . "</td><td>" . $counts[1]['total'] . "</td><td>" . $counts[2]['total'] . "</td><td>" . $counts[3]['total'] . "</td><td>" . $counts[4]['total'] . "</td><td>" . $counts[5]['total'] . "</td><td>" . $grandtotal . "</td>";
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td></td><td>gyz</td><td>" . $counts[0]['female'] . "</td><td>" . $counts[1]['female'] . "</td><td>" . $counts[2]['female'] . "</td><td>" . $counts[3]['female'] . "</td><td>" . $counts[4]['female'] . "</td><td>" . $counts[5]['female'] . "</td><td>" . $femaletotal . "</td>";
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td></td><td>oglan</td><td>" . $counts[0]['male'] . "</td><td>" . $counts[1]['male'] . "</td><td>" . $counts[2]['male'] . "</td><td>" . $counts[3]['male'] . "</td><td>" . $counts[4]['male'] . "</td><td>" . $counts[5]['male'] . "</td><td>" . $maletotal . "</td>";
                $content .= "</tr>";

//$content .= "<td>".$grandtotal."</td>";
//$content .= "<td>0</td>";
//$content .= "<td>0</td>";
//$content .= "</tr>";
                $i++;
            }
        }
        $content .= "<tr>";
        $content .= "<td></td><td></td><td></td><td>Jemi</td><td>" . $university[0] . "</td><td>" . $university[1] . "</td><td>" . $university[2] . "</td><td>" . $university[3] . "</td><td>" . $university[4] . "</td><td>" . $university[5] . "</td><td>" . array_sum($university) . "</td>";
        $content .= "</tr>";

        $content .= "</table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/group_count", name="faculty_analytics_group_count")
     */
    public function group_count() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $content = '';
        $content .= "<h3>Student counts by group</h3><p>";

        $content .= "<table id='mainTable' class='table table-striped'>";
        $content .= "<thead><tr>";
        $content .= "<th>No.</td>";
        $content .= "<th>Group name</th>";
        $content .= "<th>Group code</th>";
//$content .= "<td>Group major</td>";
        $content .= "<th>Female count</th>";
        $content .= "<th>Male count</th>";
        $content .= "<th>Total</th>";
        $content .= "</tr></thead>";
        $t = 1;
        $totalCounts = ['female' => 0, 'male' => 0, 'total' => 0];
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupRepository->findAll();
        $groupsData = [];
        $beginYear = $this->systemInfoManager->getFirstSemesterYear();
        $endYear = $beginYear + 5;
        foreach ($groups as $group) {
//            include groups with graduation years within 5 years interval
            if ($group->getGraduationYear() > $beginYear && $group->getGraduationYear() <= $endYear) {
                $groupsData[] = [
                    'letterCode' => $group->getLetterCode(),
                    'systemId' => $group->getSystemId(),
                    'studyProgramCode' => $group->getStudyProgram()->getSystemId(),
                    'graduationYear' => $group->getGraduationYear(),
                    'femaleCount' => $group->getFemaleStudentCount(),
                    'maleCount' => $group->getMaleStudentCount(),
                    'totalCount' => $group->getTotalStudentCount()
                ];
            }
        }

        $sortedGroups = $this->array_orderby($groupsData, 'studyProgramCode', SORT_ASC, 'graduationYear', SORT_DESC);
        $content .= "<tbody>";
        foreach ($sortedGroups as $group) {
            $content .= "<tr>";
            $content .= "<td>" . $t . "</td>";
            $content .= "<td>" . $group['letterCode'] . "</td>";
            $content .= "<td>" . $group['systemId'] . "</td>";
            $content .= "<td>" . $group['femaleCount'] . "</td>";
            $content .= "<td>" . $group['maleCount'] . "</td>";
            $content .= "<td>" . $group['totalCount'] . "</td>";
            $content .= "</tr>";
            $t++;
            $totalCounts['female'] += $group['femaleCount'];
            $totalCounts['male'] += $group['maleCount'];
            $totalCounts['total'] += $group['totalCount'];
        }


        $content .= "<tr>";
        $content .= "<td></td>";
        $content .= "<td>Total number</td>";
        $content .= "<td></td>";
        $content .= "<td>" . $totalCounts['female'] . "</td>";
        $content .= "<td>" . $totalCounts['male'] . "</td>";
        $content .= "<td>" . $totalCounts['total'] . "</td>";
        $content .= "</tr>";
        $content .= "</td></tr></tbody></table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    private function array_orderby() {
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

    /**
     * @Route("/faculty/analytics/faculty_count", name="faculty_analytics_faculty_count")
     */
    public function faculty_count() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $content = '';
        $graduationYear = $this->systemInfoManager->getCurrentGraduationYear();
        $lldYear = $graduationYear + 4;
        $content .= "<h3>Student counts by Faculty</h3><p>";
        $content .= "<table><tr valign='top'><td>";

        $content .= "<table border=1 cellspacing=0 cellpadding = 5>";
        $content .= "<tr class='header'>";
        $content .= "<td>No.</td>";
        $content .= "<td>Group name</td>";
        $content .= "<td>Group code</td>";
//$content .= "<td>Group major</td>";
        $content .= "<td>Female count</td>";
        $content .= "<td>Male count</td>";
        $content .= "<td>Total</td>";
        $content .= "</tr>";
        $t = 1;
        $totalCounts = ['female' => 0, 'male' => 0, 'total' => 0];
        $faculty_ids = [1, 2, 3, 4, 6];
        $faculty_sums = [
            1 => ['male' => 0, 'female' => 0],
            2 => ['male' => 0, 'female' => 0],
            3 => ['male' => 0, 'female' => 0],
            4 => ['male' => 0, 'female' => 0],
            6 => ['male' => 0, 'female' => 0]
        ];
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $groups = $groupRepository->findAll();
        foreach ($faculty_ids as $faculty_id) {
            $faculty = $facultyRepository->findOneBy(['systemId' => $faculty_id]);
            $content .= "<tr>";
            $content .= "<td class='main' colspan=6>" . $faculty->getNameEnglish() . "</td>";
            $content .= "</tr>";

            $departments = $faculty->getDepartments();
            foreach ($departments as $department) {
                $studyPrograms = $department->getStudyPrograms();
                foreach ($studyPrograms as $studyProgram) {
                    if ($faculty_id != 6) {
                        $groups = $studyProgram->getGroups();
                    } else {
                        $groups = $groupRepository->findBy(["graduationYear" => $lldYear]);
                    }

                    foreach ($groups as $group) {
                        if ($group->getGraduationYear() >= $graduationYear && $group->getGraduationYear() < $lldYear) {
                            $content .= "<tr>";
                            $content .= "<td>" . $t . "</td>";
                            $content .= "<td>" . $group->getLetterCode() . "</td>";
                            $content .= "<td>" . $group->getSystemId() . "</td>";
//$content .= "<td>" . $group->getStudyProgram()->getNameEnglish() . "</td>";
                            $content .= "<td>" . $group->getFemaleStudentCount() . "</td>";
                            $content .= "<td>" . $group->getMaleStudentCount() . "</td>";
                            $content .= "<td>" . $group->getTotalStudentCount() . "</td>";
                            $content .= "</tr>";
                            $t++;
//                            $totalCounts['female'] += $group->getFemaleStudentCount();
//                            $totalCounts['male'] += $group->getMaleStudentCount();
//                            $totalCounts['total'] += $group->getTotalStudentCount();
                            $faculty_sums[$faculty_id]['male'] += $group->getMaleStudentCount();
                            $faculty_sums[$faculty_id]['female'] += $group->getFemaleStudentCount();
                        }
                        if ($faculty_id == 6 && $group->getGraduationYear() == $lldYear) {
                            $content .= "<tr>";
                            $content .= "<td>" . $t . "</td>";
                            $content .= "<td>" . $group->getLetterCode() . "</td>";
                            $content .= "<td>" . $group->getSystemId() . "</td>";
//$content .= "<td>" . $group->getStudyProgram()->getNameEnglish() . "</td>";
                            $content .= "<td>" . $group->getFemaleStudentCount() . "</td>";
                            $content .= "<td>" . $group->getMaleStudentCount() . "</td>";
                            $content .= "<td>" . $group->getTotalStudentCount() . "</td>";
                            $content .= "</tr>";
                            $t++;
//                            $totalCounts['female'] += $group->getFemaleStudentCount();
//                            $totalCounts['male'] += $group->getMaleStudentCount();
//                            $totalCounts['total'] += $group->getTotalStudentCount();
                            $faculty_sums[$faculty_id]['male'] += $group->getMaleStudentCount();
                            $faculty_sums[$faculty_id]['female'] += $group->getFemaleStudentCount();
                        }
                    }
                }
            }

            $content .= "<tr>";
            $content .= "<td colspan=3>TOTAL</td>";
            $content .= "<td>" . $faculty_sums[$faculty_id]['male'] . "</td>";
            $content .= "<td>" . $faculty_sums[$faculty_id]['female'] . "</td>";
            $content .= "<td>" . ($faculty_sums[$faculty_id]['male'] + $faculty_sums[$faculty_id]['female']) . "</td>";
            $content .= "</tr>";
        }

        $content .= "<tr>";
        $content .= "<td></td>";
        $content .= "<td>Total number</td>";
        $content .= "<td></td>";
        $content .= "<td>" . $totalCounts['female'] . "</td>";
        $content .= "<td>" . $totalCounts['male'] . "</td>";
        $content .= "<td>" . $totalCounts['total'] . "</td>";
        $content .= "</tr>";
        $content .= "</td></tr></table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/all_students", name="faculty_analytics_all_students")
     */
    public function all_students() {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $content = '';
        $graduationYear = $this->systemInfoManager->getCurrentGraduationYear();
        $lldYear = $graduationYear + 4;
        $content .= "<h3>International University for the Humanities and Development<br>Student list by Groups 2019-2020 Academic Year</h3><p>";
        $content .= "<table><tr valign='top'><td>";

        $content .= "<table border=1 cellspacing=0 cellpadding = 5>";
        $content .= "<tr class='header'>";
        $content .= "<td>No.</td>";
        $content .= "<td>Photo</td>";
        $content .= "<td>Student ID</td>";
        $content .= "<td>Full name</td>";
        $content .= "<td>Birthdate</td>";
        $content .= "<td>Group</td>";
        $content .= "<td>Address</td>";
        $content .= "</tr>";
        $t = 1;
        $totalCounts = ['female' => 0, 'male' => 0, 'total' => 0];
        $faculty_ids = [1, 2, 3, 4, 6];
        $faculty_sums = [
            1 => ['male' => 0, 'female' => 0],
            2 => ['male' => 0, 'female' => 0],
            3 => ['male' => 0, 'female' => 0],
            4 => ['male' => 0, 'female' => 0],
            6 => ['male' => 0, 'female' => 0]
        ];
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $groups = $groupRepository->findAll();
        foreach ($faculty_ids as $faculty_id) {
            $faculty = $facultyRepository->findOneBy(['systemId' => $faculty_id]);
            $content .= "<tr>";
            $content .= "<td class='main boldclass' colspan=6>" . $faculty->getNameEnglish() . "</td>";
            $content .= "</tr>";

            $departments = $faculty->getDepartments();
            foreach ($departments as $department) {
                $studyPrograms = $department->getStudyPrograms();
                foreach ($studyPrograms as $studyProgram) {
                    if ($faculty_id != 6) {
                        $groups = $studyProgram->getGroups();
                    } else {
                        $groups = $groupRepository->findBy(["graduationYear" => $lldYear]);
                    }

                    foreach ($groups as $group) {
                        if ($group->getGraduationYear() == $graduationYear) {
                            $content .= "<tr>";
                            $content .= "<td colspan=6 class='boldclass'>" . $group->getLetterCode() . " - " . $group->getStudyProgram()->getNameEnglish() . "</td>";
                            $content .= "</tr>";
                            $students = $group->getStudents();
                            $i = 1;
                            foreach ($students as $student) {
                                if ($student->getGender() == 1) {
                                    $content .= "<tr>";
                                    $content .= "<td>" . $i . "</td>";
                                    $content .= "<td><img src='/build/photos/" . $group->getSystemId() . "/" . $student->getSystemId() . ".jpg' width='60'></td>";
                                    $content .= "<td>" . $student->getSystemId() . "</td>";
                                    $content .= "<td>" . $student->getThreeNames() . "</td>";
                                    $content .= "<td>" . $student->getBirthdate()->format("d.m.Y") . "</td>";
                                    $content .= "<td>" . $group->getLetterCode() . "</td>";
//                                    $content .= "<td>" . $student->getDataField("address") . "</td>";
                                    $content .= "</tr>";
                                    $i++;
                                }
                            }
                        }
//                        if ($faculty_id == 6 && $group->getGraduationYear() == $lldYear) {
//                            $content .= "<tr>";
//                            $content .= "<td colspan=6 class='boldclass'>" . $group->getLetterCode() . " - " . $group->getStudyProgram()->getNameEnglish() . "</td>";
//                            $content .= "</tr>";
//                            $students = $group->getStudents();
//                            $i = 1;
//                            foreach ($students as $student) {
//                                $content .= "<tr>";
//                                $content .= "<td>" . $i . "</td>";
//                                $content .= "<td><img src='/build/photos/" .$group->getSystemId(). "/" . $student->getSystemId() . ".jpg' width='60'></td>";
//                                $content .= "<td>" . $student->getSystemId() . "</td>";
//                                $content .= "<td>" . $student->getThreeNames() . "</td>";
//                                $content .= "<td>" . $student->getBirthdate()->format("d.m.Y") . "</td>";
//                                $content .= "<td>" . $group->getLetterCode() . "</td>";
//                                $content .= "<td>" . $student->getDataField("address") . "</td>";
//                                $content .= "</tr>";
//                                $i++;
//                            }
//                        }
                    }
                }
            }
        }

        $content .= "</td></tr></table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/health_list", name="faculty_analytics_health_list")
     */
    public function health_list() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $content = '';
        $graduationYear = $this->systemInfoManager->getCurrentGraduationYear();
        $lldYear = $graduationYear + 4;
        $today = new \DateTime();
        $content .= "<h3>International University for the Humanities and Development<br>Student list by Groups 2019-2020 Academic Year</h3><p>";
        $content .= "<table><tr valign='top'><td>";

        $content .= "<table border=1 cellspacing=0 cellpadding = 5>";
        $content .= "<tr class='header'>";
        $content .= "<td>No.</td>";
        $content .= "<td>Student ID</td>";
        $content .= "<td>Full name</td>";
        $content .= "<td>Birthdate</td>";
        $content .= "<td>Age</td>";
        $content .= "<td>Address2</td>";
        $content .= "<td>Address</td>";
        $content .= "<td>Oglan</td>";
        $content .= "<td>Gyz</td>";
        $content .= "<td>Group</td>";
        $content .= "</tr>";
        $t = 1;
        $totalCounts = ['female' => 0, 'male' => 0, 'total' => 0];
        $faculty_ids = [1, 2, 3, 4, 6];
//        $faculty_ids = [1];
        $faculty_sums = [
            1 => ['male' => 0, 'female' => 0],
            2 => ['male' => 0, 'female' => 0],
            3 => ['male' => 0, 'female' => 0],
            4 => ['male' => 0, 'female' => 0],
            6 => ['male' => 0, 'female' => 0]
        ];
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $groups = $groupRepository->findAll();
        foreach ($faculty_ids as $faculty_id) {
            $faculty = $facultyRepository->findOneBy(['systemId' => $faculty_id]);
            $content .= "<tr>";
            $content .= "<td class='main boldclass' colspan=10>" . $faculty->getNameEnglish() . "</td>";
            $content .= "</tr>";

            $departments = $faculty->getDepartments();
            foreach ($departments as $department) {
                $studyPrograms = $department->getStudyPrograms();
                foreach ($studyPrograms as $studyProgram) {
                    if ($faculty_id != 6) {
                        $groups = $studyProgram->getGroups();
                    } else {
                        $groups = $groupRepository->findBy(["graduationYear" => $lldYear]);
                    }

                    foreach ($groups as $group) {
                        if ($group->getGraduationYear() >= $graduationYear && $group->getGraduationYear() < $lldYear) {
                            $content .= "<tr>";
                            $content .= "<td colspan=6 class='boldclass'>" . $group->getLetterCode() . " - " . $group->getStudyProgram()->getNameEnglish() . "</td>";
                            $content .= "</tr>";
                            $students = $group->getStudents();
                            $i = 1;
                            foreach ($students as $student) {
                                $content .= "<tr>";
                                $content .= "<td>" . $i . "</td>";
                                $content .= "<td>HYYÖU</td>";
                                $content .= "<td>" . $student->getThreeNames() . "</td>";
                                $content .= "<td>" . $student->getBirthdate()->format("d.m.Y") . "</td>";
                                $interval = $today->diff($student->getBirthdate());
                                $content .= "<td>" . $interval->y . "</td>";
                                $content .= "<td>" . $student->getDataField("address2") . "</td>";
                                $content .= "<td>" . $student->getDataField("address") . "</td>";
                                if ($student->getGender() == 1) {
                                    $content .= "<td>oglan</td>";
                                    $content .= "<td></td>";
                                } else {
                                    $content .= "<td></td>";
                                    $content .= "<td>gyz</td>";
                                }
                                $content .= "<td>" . $group->getLetterCode() . "</td>";
                                $content .= "</tr>";
                                $i++;
                            }
                        }
                        if ($faculty_id == 6 && $group->getGraduationYear() == $lldYear) {
                            $content .= "<tr>";
                            $content .= "<td colspan=6 class='boldclass'>" . $group->getLetterCode() . " - " . $group->getStudyProgram()->getNameEnglish() . "</td>";
                            $content .= "</tr>";
                            $students = $group->getStudents();
                            $i = 1;
                            foreach ($students as $student) {
                                $content .= "<tr>";
                                $content .= "<td>" . $i . "</td>";
                                $content .= "<td>HYYÖU</td>";
                                $content .= "<td>" . $student->getThreeNames() . "</td>";
                                $content .= "<td>" . $student->getBirthdate()->format("d.m.Y") . "</td>";
                                $interval = $today->diff($student->getBirthdate());
                                $content .= "<td>" . $interval->y . "</td>";
                                $content .= "<td>" . $student->getDataField("address2") . "</td>";
                                $content .= "<td>" . $student->getDataField("address") . "</td>";
                                if ($student->getGender() == 1) {
                                    $content .= "<td>oglan</td>";
                                    $content .= "<td></td>";
                                } else {
                                    $content .= "<td></td>";
                                    $content .= "<td>gyz</td>";
                                }
                                $content .= "<td>" . $group->getLetterCode() . "</td>";
                                $content .= "</tr>";
                                $i++;
                            }
                        }
                    }
                }
            }
        }

        $content .= "</td></tr></table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/velayat_count", name="faculty_analytics_velayatcount")
     */
    public function velayatCount(Request $request) {
        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $studyProgramRepository = $this->getDoctrine()->getRepository(StudyProgram::class);
        $content = '';
        $facultyIds = [1, 2, 3, 4, 5, 6];
        $facultyMajors = [
            1 => [11, 12, 13],
            2 => [21, 22, 23, 28, 29],
            3 => [31, 32, 33, 34, 35],
            4 => [],
            5 => [41, 42, 43],
            6 => [],
        ];
//        $programIds = [11, 12, 13, 21, 22, 23, 28, 29, 31, 32, 33, 34, 35, 41, 42, 43];
        $listedPrograms = [];

        $content .= "<table class='table table-bordered table-sm'>";
        $content .= '<thead>';
        $content .= "<tr><th colspan=30 style='text-align:center;'>Halkara ynsanperwer ylymlary we ösüş uniwersiteti talyplaryň okuwa giren welaýatlary we Aşgabat şäheri boýunça san maglumaty</th></tr>";
        $content .= "<tr>";
        $content .= "<th>Tamamlamaly ýyly</th>";
        $content .= "<th>AG</th>";
        $content .= "<th>O</th>";
        $content .= "<th>G</th>";
        $content .= "<th>AH</th>";
        $content .= "<th>O</th>";
        $content .= "<th>G</th>";
        $content .= "<th>BN</th>";
        $content .= "<th>O</th>";
        $content .= "<th>G</th>";
        $content .= "<th>DZ</th>";
        $content .= "<th>O</th>";
        $content .= "<th>G</th>";
        $content .= "<th>LB</th>";
        $content .= "<th>O</th>";
        $content .= "<th>G</th>";
        $content .= "<th>MR</th>";
        $content .= "<th>O</th>";
        $content .= "<th>G</th>";
        $content .= "<th>DÝ</th>";
        $content .= "<th>O</th>";
        $content .= "<th>G</th>";
        $content .= "<th>JEMI</th>";
        $content .= "</tr>";
        $content .= '</thead>';
        $content .= '<tbody>';
//$content .= 'System id:' . $systemId;
        foreach ($facultyIds as $facultyId) {
            $faculty = $facultyRepository->find($facultyId);
            $content .= '<tr><td colspan=30>' . $faculty->getNameTurkmen() . '</td></tr>';
            $departments = $faculty->getDepartments();
//                $studyPrograms = $department->getStudyPrograms();
            $programIds = $facultyMajors[$facultyId];
            foreach ($programIds as $programId) {
                $studyPrograms = $studyProgramRepository->findBy(['systemId' => $programId]);
                foreach ($studyPrograms as $studyProgram) {
                    if (!in_array($studyProgram->getSystemId(), $listedPrograms)) {
                        $content .= '<tr><td colspan=30>' . $studyProgram->getNameTurkmen() . ' ' . $studyProgram->getApprovalYear() . '</td></tr>';
//                        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
                    } else {
                        $listedPrograms[] = $studyProgram->getSystemId();
                    }
                    $groups = $studyProgram->getGroups();
                    foreach ($groups as $group) {
                        if ($group->getStatus() == 1 && $group->getGraduationYear() > 2019) {
                            $regions = [
                                'ah' => ['male' => 0, 'female' => '0', 'total' => 0],
                                'bn' => ['male' => 0, 'female' => '0', 'total' => 0],
                                'dz' => ['male' => 0, 'female' => '0', 'total' => 0],
                                'lb' => ['male' => 0, 'female' => '0', 'total' => 0],
                                'mr' => ['male' => 0, 'female' => '0', 'total' => 0],
                                'ag' => ['male' => 0, 'female' => '0', 'total' => 0],
                                'zz' => ['male' => 0, 'female' => '0', 'total' => 0],
                            ];
//$content .= 'Group:' . $group->getLetterCode();
                            $students = $group->getStudents();
                            $i = 1;
                            foreach ($students as $student) {
//calculate regions
                                if ($student->getGender() == 1) {
                                    $regions[$student->getRegion()->getLetterCode()]['male']++;
                                    continue;
                                } else {
                                    $regions[$student->getRegion()->getLetterCode()]['female']++;
                                    continue;
                                }
                                $regions[$student->getRegion()->getLetterCode()]['total']++;
//$i++;
                            }
                            $content .= '<tr><td>' . $group->getLetterCode() . '</td>';
                            $content .= "<td class='main'>" . array_sum($regions['ag']) . "</td>";
                            $content .= "<td>" . $regions['ag']['male'] . "</td>";
                            $content .= "<td>" . $regions['ag']['female'] . "</td>";
                            $content .= "<td class='main'>" . array_sum($regions['ah']) . "</td>";
                            $content .= "<td>" . $regions['ah']['male'] . "</td>";
                            $content .= "<td>" . $regions['ah']['female'] . "</td>";
                            $content .= "<td class='main'>" . array_sum($regions['bn']) . "</td>";
                            $content .= "<td>" . $regions['bn']['male'] . "</td>";
                            $content .= "<td>" . $regions['bn']['female'] . "</td>";
                            $content .= "<td class='main'>" . array_sum($regions['dz']) . "</td>";
                            $content .= "<td>" . $regions['dz']['male'] . "</td>";
                            $content .= "<td>" . $regions['dz']['female'] . "</td>";
                            $content .= "<td class='main'>" . array_sum($regions['lb']) . "</td>";
                            $content .= "<td>" . $regions['lb']['male'] . "</td>";
                            $content .= "<td>" . $regions['lb']['female'] . "</td>";
                            $content .= "<td class='main'>" . array_sum($regions['mr']) . "</td>";
                            $content .= "<td>" . $regions['mr']['male'] . "</td>";
                            $content .= "<td>" . $regions['mr']['female'] . "</td>";
                            $content .= "<td class='main'>" . array_sum($regions['zz']) . "</td>";
                            $content .= "<td>" . $regions['zz']['male'] . "</td>";
                            $content .= "<td>" . $regions['zz']['female'] . "</td>";
                            $total = 0;
                            foreach ($regions as $region) {
                                $total += array_sum($region);
                            }
                            $content .= "<td>" . $total . "</td>";
                            $content .= '</tr>';
                        }
                    }
                }
            }
        }
        $content .= '</tbody>';
        $content .= "</table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/alumni", name="faculty_analytics_alumni")
     */
    public function alumniReport(Request $request) {
        $alumnusRepository = $this->getDoctrine()->getRepository(AlumnusStudent::class);
        $alumni = $alumnusRepository->findAll();

        $keywords = array('magistr', 'gulluk', 'hususy', 'döwlet');
        $counts = array('master' => 0, 'military' => 0, 'state' => 0, 'private' => 0);
        $content = '';

        foreach ($alumni as $alumnus) {

            if (strpos($alumnus->getTags(), 'magistr') > -1)
                $counts['master']++;
            elseif (strpos($alumnus->getTags(), 'gulluk') > -1)
                $counts['military']++;
            elseif (strpos($alumnus->getTags(), 'döwlet') > -1)
                $counts['state']++;
            elseif (strpos($alumnus->getTags(), 'hususy') > -1)
                $counts['private']++;
        }
        $content .= "<table class='table table-condensed table-striped'>";
        $content .= "<tr><th>Type</th><th>Number</th></tr>";
        $content .= "<tr><td>Magistr</td><td>" . $counts['master'] . "</td></tr>";
        $content .= "<tr><td>Harby gulluk</td><td>" . $counts['military'] . "</td></tr>";
        $content .= "<tr><td>Döwlet</td><td>" . $counts['state'] . "</td></tr>";
        $content .= "<tr><td>Hususy</td><td>" . $counts['private'] . "</td></tr>";
        $content .= "<tr><td>Total</td><td>" . array_sum($counts) . " / " . sizeof($alumni) . "</td></tr>";
        $content .= "</table>";

        $content .= '<p>';

        $content .= "<table class='table table-bordered table-sm'>";
        $content .= '<thead>';
        $content .= "<tr><th colspan=30 style='text-align:center;'>Halkara ynsanperwer ylymlary we ösüş uniwersiteti 2017-nji ýyldan bäri tamamlan uçurumlaryň welaýatlar we Aşgabat şäheri boýunça san maglumaty</th></tr>";
        $content .= "<tr>";
        $content .= "<th>Tamamlan ýyly</th>";
        $content .= "<th>AG</th>";
        $content .= "<th>D</th>";
        $content .= "<th>H</th>";
        $content .= "<th>G</th>";
        $content .= "<th>AH</th>";
        $content .= "<th>D</th>";
        $content .= "<th>H</th>";
        $content .= "<th>G</th>";
        $content .= "<th>BN</th>";
        $content .= "<th>D</th>";
        $content .= "<th>H</th>";
        $content .= "<th>G</th>";
        $content .= "<th>DZ</th>";
        $content .= "<th>D</th>";
        $content .= "<th>H</th>";
        $content .= "<th>G</th>";
        $content .= "<th>LB</th>";
        $content .= "<th>D</th>";
        $content .= "<th>H</th>";
        $content .= "<th>G</th>";
        $content .= "<th>MR</th>";
        $content .= "<th>D</th>";
        $content .= "<th>H</th>";
        $content .= "<th>G</th>";
        $content .= "<th>DÝ</th>";
        $content .= "<th>D</th>";
        $content .= "<th>H</th>";
        $content .= "<th>G</th>";
        $content .= "<th>JEMI</th>";
        $content .= "</tr>";
        $content .= '</thead>';
        $content .= '<tbody>';
        for ($graduationYear = 2017; $graduationYear < 2020; $graduationYear++) {
            $regions = [
                'ah' => ['d' => 0, 'h' => '0', 'g' => 0],
                'bn' => ['d' => 0, 'h' => '0', 'g' => 0],
                'dz' => ['d' => 0, 'h' => '0', 'g' => 0],
                'lb' => ['d' => 0, 'h' => '0', 'g' => 0],
                'mr' => ['d' => 0, 'h' => '0', 'g' => 0],
                'ag' => ['d' => 0, 'h' => '0', 'g' => 0],
                'zz' => ['d' => 0, 'h' => '0', 'g' => 0]
            ];
//$content .= 'System id:' . $systemId;
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $groups = $groupRepository->findBy(['graduationYear' => $graduationYear]);
            foreach ($groups as $group) {
//$content .= 'Group:' . $group->getLetterCode();
                $students = $group->getAlumniStudents();
                $i = 1;
                foreach ($students as $student) {
//calculate regions
                    if (strpos($student->getTags(), 'döwlet') !== false) {
                        $regions[$student->getRegion()->getLetterCode()]['d']++;
                        continue;
                    } elseif (strpos($student->getTags(), 'gulluk') !== false) {
                        $regions[$student->getRegion()->getLetterCode()]['g']++;
                        continue;
                    } elseif (strpos($student->getTags(), 'magistr') !== false) {
                        $regions[$student->getRegion()->getLetterCode()]['h']++;
                        continue;
                    } elseif (strpos($student->getTags(), 'hususy') !== false) {
                        $regions[$student->getRegion()->getLetterCode()]['h']++;
                        continue;
                    } elseif (strpos($student->getTags(), 'işsiz') !== false) {
                        $regions[$student->getRegion()->getLetterCode()]['h']++;
                        continue;
                    }
//$i++;
                }
            }
            $content .= '<tr><td>' . $graduationYear . '</td>';
            $content .= "<td class='main'>" . array_sum($regions['ag']) . "</td>";
            $content .= "<td>" . $regions['ag']['d'] . "</td>";
            $content .= "<td>" . $regions['ag']['h'] . "</td>";
            $content .= "<td>" . $regions['ag']['g'] . "</td>";
            $content .= "<td class='main'>" . array_sum($regions['ah']) . "</td>";
            $content .= "<td>" . $regions['ah']['d'] . "</td>";
            $content .= "<td>" . $regions['ah']['h'] . "</td>";
            $content .= "<td>" . $regions['ah']['g'] . "</td>";
            $content .= "<td class='main'>" . array_sum($regions['bn']) . "</td>";
            $content .= "<td>" . $regions['bn']['d'] . "</td>";
            $content .= "<td>" . $regions['bn']['h'] . "</td>";
            $content .= "<td>" . $regions['bn']['g'] . "</td>";
            $content .= "<td class='main'>" . array_sum($regions['dz']) . "</td>";
            $content .= "<td>" . $regions['dz']['d'] . "</td>";
            $content .= "<td>" . $regions['dz']['h'] . "</td>";
            $content .= "<td>" . $regions['dz']['g'] . "</td>";
            $content .= "<td class='main'>" . array_sum($regions['lb']) . "</td>";
            $content .= "<td>" . $regions['lb']['d'] . "</td>";
            $content .= "<td>" . $regions['lb']['h'] . "</td>";
            $content .= "<td>" . $regions['lb']['g'] . "</td>";
            $content .= "<td class='main'>" . array_sum($regions['mr']) . "</td>";
            $content .= "<td>" . $regions['mr']['d'] . "</td>";
            $content .= "<td>" . $regions['mr']['h'] . "</td>";
            $content .= "<td>" . $regions['mr']['g'] . "</td>";
            $content .= "<td class='main'>" . array_sum($regions['zz']) . "</td>";
            $content .= "<td>" . $regions['zz']['d'] . "</td>";
            $content .= "<td>" . $regions['zz']['h'] . "</td>";
            $content .= "<td>" . $regions['zz']['g'] . "</td>";
            $total = 0;
            foreach ($regions as $region) {
                $total += array_sum($region);
            }
            $content .= "<td>" . $total . "</td>";
            $content .= '</tr>';
        }
        $content .= '</tbody>';
        $content .= "</table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/alumniemployment/{year?}", name="faculty_analytics_alumniemployment")
     */
    public function alumniEmployment(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST", "ROLE_HR");
//$spreadsheet = new Spreadsheet();
//$sheet = $spreadsheet->getActiveSheet();
        $year = $request->attributes->get('year');
        $beginYear = 2019;
        $endYear = 2024;
        $currentYear = 2023;
        if ($year) {
            $beginYear = $year;
            $endYear = $year + 1;
        }
        $content = '';
        for ($graduationYear = $beginYear; $graduationYear < $endYear; $graduationYear++) {
            $content .= "<h6>Halkara ynsanperwer ylymlary we ösüş uniwersiteti boýunça " . $graduationYear . "-nji(y) ýylda tamamlan uçurymlar barada maglumat</h6>";
            $content .= "<table class='table table-bordered' id='mainTable'>";
            $content .= '<thead>';
//$content .= "<tr><th colspan=10 style='text-align:center;'></th></tr>";
            $content .= "<tr>";
            $content .= "<th>T/b</th>";
            $content .= "<th>Topar kody</th>";
            $content .= "<th>Topary</th>";
            $content .= "<th>Familiýasy, ady we atasynyň ady</td>";
            $content .= "<th>Berlen hünäri</th>";
            $content .= "<th>Okuwa kabul edilen welaýaty (Aşgabat şäheri)</th>";
            $content .= "<th>Işe ýollanan ýeri</th>";
            $content .= "<th>Häzirki wagtda işleýän ýeri</th>";
            if ($this->isGranted("ROLE_SPECIALIST")) {
                $content .= "<th>ID</th>";
                $content .= "<th>Amallar</th>";
            }
            $content .= "</tr>";
            $content .= '</thead>';
            $content .= '<tbody>';
//$content .= 'System id:' . $systemId;
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $groups = $groupRepository->findBy(['graduationYear' => $graduationYear]);
            foreach ($groups as $group) {
                if ($group->getStatus() == 1) {
//$content .= 'Group:' . $group->getLetterCode();
                    if ($graduationYear > $currentYear) {
                        $students = $group->getStudents();
                    } else {
                        $students = $group->getAlumniStudents();
                    }
                    $i = 1;
                    foreach ($students as $student) {
                        $content .= '<tr><td>' . $i . '</td>';
                        $content .= '<td>' . $group->getSystemId() . '</td>';
                        $content .= '<td>' . $group->getLetterCode() . '</td>';
                        $content .= '<td>' . $student->getThreenames() . '</td>';
                        $content .= '<td>' . $group->getStudyProgram()->getNameTurkmen() . '</td>';
                        $content .= '<td>' . $student->getRegion()->getNameTurkmen() . '</td>';
                        $content .= '<td>' . $student->getDataField('employment_place') . '</td>';
                        $content .= '<td>' . $student->getDataField('employment_position') . '</td>';
                        if ($this->isGranted("ROLE_SPECIALIST")) {
                            $content .= "<td class='no-print'>" . $student->getTags() . "</td>";
                            if ($graduationYear > $currentYear) {
                                $content .= "<td class='no-print'><a href='/faculty/enrolledstudent/edit/" . $student->getId() . "'>edit</a></td>";
                            } else {
                                $content .= "<td class='no-print'><a href='/faculty/alumnusstudent/edit/" . $student->getId() . "'>edit</a></td>";
                            }
                        }
                        $content .= '</tr>';
                        $i++;
                    }
                }
            }
            $content .= '</tbody>';
            $content .= "</table>";
        }


        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/internship", name="faculty_analytics_internship")
     */
    public function internship(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
//$spreadsheet = new Spreadsheet();
//$sheet = $spreadsheet->getActiveSheet();
//$systemId = $request->attributes->get('systemId');
        $content = '';
        for ($graduationYear = 2023; $graduationYear < 2024; $graduationYear++) {
            $content .= "<h6>Halkara ynsanperwer ylymlary we ösüş uniwersiteti boýunça " . $graduationYear . "-nji(y) ýylda önümçilik tejribeligi barada maglumat</h6>";
            $content .= "<table class='table table-bordered' id='mainTable'>";
            $content .= '<thead>';
//            $content .= "<tr><th colspan=10 style='text-align:center;'></th></tr>";
            $content .= "<tr>";
            $content .= "<th>T/b</th>";
            $content .= "<th>Kafedrasy</th>";
            $content .= "<th>Topary</th>";
            $content .= "<th>Familiýasy, ady we atasynyň ady</td>";
            $content .= "<th>Hünäri</th>";
            $content .= "<th>Okuwa kabul edilen welaýaty (Aşgabat şäheri)</th>";
            $content .= "<th>Önümçilik tejribeligi</th>";
            $content .= "<th>Ýolbaşçysy</th>";
            $content .= "<th>Amallar</th>";
            $content .= "</tr>";
            $content .= '</thead>';
            $content .= '<tbody>';
//$content .= 'System id:' . $systemId;
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $groups = $groupRepository->findBy(['graduationYear' => $graduationYear], ['departmentCode' => 'ASC']);
            foreach ($groups as $group) {
//$content .= 'Group:' . $group->getLetterCode();
                $students = $group->getStudents();
                $i = 1;
                foreach ($students as $student) {
                    $content .= '<tr>';
                    $content .= '<td>' . $i . '</td>';
                    $content .= '<td>' . $group->getDepartmentCode() . '</td>';
                    $content .= '<td>' . $group->getLetterCode() . '</td>';
                    $content .= '<td>' . $student->getThreenames() . '</td>';
                    $content .= '<td>' . $group->getStudyProgram()->getNameTurkmen() . '</td>';
                    $content .= '<td>' . $student->getRegion()->getNameTurkmen() . '</td>';
                    $content .= '<td>' . $student->getDataField('internship_place') . '</td>';
                    $content .= '<td>' . $student->getDataField('internship_advisor') . '</td>';
                    if ($this->isGranted("ROLE_SPECIALIST")) {
//$content .= "<td class='no-print'>" . $student->getTags() . "</td>";
                        $content .= "<td class='no-print'><a href='/faculty/enrolledstudent/edit/" . $student->getId() . "'>edit</a></td>";
                    }
                    $content .= '</td>';
                    $content .= '</tr>';
                    $i++;
                }
            }
            $content .= '</tbody>';
            $content .= "</table>";
        }


        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/analytics/internshipsummary", name="faculty_analytics_internshipsummary")
     */
    public function internshipSummary(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
//$spreadsheet = new Spreadsheet();
//$sheet = $spreadsheet->getActiveSheet();
//$systemId = $request->attributes->get('systemId');
        $content = '';
        $year1 = $this->systemInfoManager->getCurrentGraduationYear();
        $year2 = $year1 + 1;
        for ($graduationYear = $year1; $graduationYear < $year2; $graduationYear++) {
            $content .= "<h6>Halkara ynsanperwer ylymlary we ösüş uniwersiteti boýunça " . $graduationYear . "-nji(y) ýylda önümçilik tejribeligi barada maglumat</h6>";
            $content .= "<table class='table table-bordered table-sm'>";
            $content .= '<thead>';
//            $content .= "<tr><th colspan=10 style='text-align:center;'></th></tr>";
            $content .= "<tr>";
            $content .= "<th>T/b</th>";
            $content .= "<th>Kafedrasy</th>";
            $content .= "<th>Topary</th>";
            $content .= "<th>Hünäri</th>";
            $content .= "<th>Jemi</th>";
            $content .= "<th>Önümçilik tejribeligi</th>";
            $content .= "<th>Ýolbaşçysy</th>";
            $content .= "</tr>";
            $content .= '</thead>';
            $content .= '<tbody>';
//$content .= 'System id:' . $systemId;
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $groups = $groupRepository->findBy(['graduationYear' => $graduationYear], ['departmentCode' => 'ASC']);
            $groupData = [];
            foreach ($groups as $group) {
                if ($group->getStatus() == 1) {
//$content .= 'Group:' . $group->getLetterCode();
                    $students = $group->getStudents();
                    $totalStudentCount = $group->getTotalStudentCount();
                    $internshipFound = 0;
                    $advisorAppointed = 0;
                    $i = 1;
                    foreach ($students as $student) {
                        if (strlen($student->getDataField('internship_place')) > 0) {
                            $internshipFound++;
                        }
                        if (strlen($student->getDataField('internship_advisor')) > 0) {
                            $advisorAppointed++;
                        }
                        $i++;
                    }
                    $content .= '<tr>';
                    $content .= '<td>' . $i . '</td>';
                    $content .= '<td>' . $group->getDepartmentCode() . '</td>';
                    $content .= '<td>' . $group->getLetterCode() . '</td>';
                    $content .= '<td>' . $group->getStudyProgram()->getNameTurkmen() . '</td>';
                    $content .= '<td>' . $totalStudentCount . '</td>';
                    $content .= '<td>' . $internshipFound . '</td>';
                    $content .= '<td>' . $advisorAppointed . '</td>';
                    $content .= '</td>';
                    $content .= '</tr>';
                }
            }
            $content .= '</tbody>';
            $content .= "</table>";
        }


        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

}
