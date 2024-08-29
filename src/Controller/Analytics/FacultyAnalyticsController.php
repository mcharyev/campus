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

class FacultyAnalyticsController extends AbstractController {

    private $systemInfoManager;
    private $currentYear;

    public function __construct(SystemInfoManager $systemInfoManager) {
        $this->systemInfoManager = $systemInfoManager;
        $this->currentYear = $this->systemInfoManager->getCurrentCommencementYear();
    }

    /**
     * @Route("/faculty/facultyanalytics", name="faculty_faculty_analytics")
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
     * @Route("/faculty/facultyanalytics/faculty_students_year", name="faculty_facultyanalytics_faculty_students_year")
     */
    public function faculty_students_year() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $currentYear = $this->systemInfoManager->getCurrentCommencementYear();
        $content = '';
//        $content .= "<h3>Halkara ynsanperwer ylymlary we ösüş uniwersitetinde bilim alýan talyplar barada maglumat</h3><p>";
        $content .= "<h3>Fakultetler we okuw ýyllary boýunça talyplaryň sany barada</h3><p>";
        $content .= "<table border=1 cellspacing=0 cellpadding=5>";
        $content .= "<tr style='font-weight:bold;'>";
        $content .= "<td>T/b</td>";
        $content .= "<td>Fakultetiň ady</td>";
        $content .= "<td>DÖB</td>";
        $content .= "<td>I</td>";
        $content .= "<td>II</td>";
        $content .= "<td>III</td>";
        $content .= "<td>IV</td>";
        $content .= "<td>Jemi</td>";
        $content .= "</tr>";

        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculties = $facultyRepository->findBy([], ["systemId" => 'ASC']);
        $facultyNumber = 1;
        $universityStats = [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];
        foreach ($faculties as $faculty) {
            if ($faculty->getSystemId() <= 6) {
                $facultyStats = $faculty->getStudentCountByYear();
                $content .= "<tr>";
                $content .= "<td>" . $facultyNumber . "</td>";
                $content .= "<td>" . $faculty->getNameTurkmen() . "</td>";
                foreach ($facultyStats as $key => $value) {
                    $content .= "<td>" . $value . "</td>";
                    $universityStats[$key] += $facultyStats[$key];
                }
                $content .= "</tr>";
                $facultyNumber++;
            }
        }
        $content .= "<tr>";
        $content .= "<td></td>";
        $content .= "<td>JEMI</td>";
        foreach ($universityStats as $key => $value) {
            $content .= "<td>" . $value . "</td>";
        }
        $content .= "</tr>";
        $content .= "</table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/facultyanalytics/faculty_students_gender_year", name="faculty_facultyanalytics_faculty_students_gender_year")
     */
    public function faculty_students_gender_year() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $content = '';
        $content .= "<h3>Fakultetleriniň talyplarynyň oglan-gyz sany barada</h3><p>";
        $content .= "<table сlass='table table-bordered' border=1 cellpadding=5>";
        $content .= "<thead><tr>";
        $content .= "<th rowspan=2>T/b</th>";
        $content .= "<th rowspan=2>Fakultetiň ady</th>";
        $content .= "<th rowspan=2>Talyplaryň sany</th>";
        $content .= "<th rowspan=2>JEMI</th>";
        $content .= "<th colspan=5>Şol sanda ýyllar boýunça</th>";
        $content .= "</tr>";
        $content .= "<tr style='font-weight:bold;'>";
        $content .= "<th>DÖB</th>";
        $content .= "<th>I</th>";
        $content .= "<th>II</th>";
        $content .= "<th>III</th>";
        $content .= "<th>IV</th>";
        $content .= "</tr></thead>";

        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculties = $facultyRepository->findBy([], ["systemId" => 'ASC']);
        $facultyNumber = 1;
        $universityStats = [
            0 => [0, 0, 0],
            1 => [0, 0, 0],
            2 => [0, 0, 0],
            3 => [0, 0, 0],
            4 => [0, 0, 0],
            5 => [0, 0, 0],
        ];

//        years
        $cols = [5, 0, 1, 2, 3, 4];
//        genders
        $rows = [2, 1, 0];

        $content .= "<tbody>";
        foreach ($faculties as $faculty) {
            if ($faculty->getSystemId() <= 6) {
                $facultyStats = $faculty->getStudentGenderCountByYear();
                $content .= "<tr>";
                $content .= "<td rowspan=3>" . $facultyNumber . "</td>";
                $content .= "<td rowspan=3>" . $faculty->getNameTurkmen() . "</td>";
                $content .= "<td><strong>Jemi</strong></td>";
                foreach ($cols as $col) {
                    $content .= "<td>" . $facultyStats[$col][2] . "</td>";
                    $universityStats[$col][2] += $facultyStats[$col][2];
                }
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td>gyz</td>";
                foreach ($cols as $col) {
                    $content .= "<td>" . $facultyStats[$col][1] . "</td>";
                    $universityStats[$col][1] += $facultyStats[$col][1];
                }
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td>oglan</td>";
                foreach ($cols as $col) {
                    $content .= "<td>" . $facultyStats[$col][0] . "</td>";
                    $universityStats[$col][0] += $facultyStats[$col][0];
                }
                $content .= "</tr>";

                $facultyNumber++;
            }
        }
        $content .= "</tbody>";
        $content .= "<tfoot>";
        $content .= "<tr>";
        $content .= "<td rowspan=3 colspan=2>HYYÖU boýunça</td>";
        $content .= "<td><strong>Jemi</strong></td>";
        foreach ($cols as $col) {
            $content .= "<td>" . $universityStats[$col][2] . "</td>";
        }
        $content .= "</tr>";
        $content .= "<tr>";
        $content .= "<td>gyz</td>";
        foreach ($cols as $col) {
            $content .= "<td>" . $universityStats[$col][1] . "</td>";
        }
        $content .= "</tr>";
        $content .= "<tr>";
        $content .= "<td>oglan</td>";
        foreach ($cols as $col) {
            $content .= "<td>" . $universityStats[$col][0] . "</td>";
        }
        $content .= "</tr>";
        $content .= "</table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

    /**
     * @Route("/faculty/facultyanalytics/faculty_students_velayat", name="faculty_facultyanalytics_faculty_students_velayat")
     */
    public function faculty_students_velayat() {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $content = '';
        $content .= "<h3>Welaýatlar boýunça talyplaryň sany</h3><p>";
        $content .= "<table border=1 cellspacing=0 cellpadding=5>";
        $content .= "<tr style='font-weight:bold;'>";
        $content .= "<td>T/b</td>";
        $content .= "<td>Fakultetiň ady</td>";
        $content .= "<td>Aşgabat</td>";
        $content .= "<td>Ahal</td>";
        $content .= "<td>Balkan</td>";
        $content .= "<td>Lebap</td>";
        $content .= "<td>Daşoguz</td>";
        $content .= "<td>Mary</td>";
        $content .= "<td>Daşary ýurtly</td>";
        $content .= "<td>Jemi</td>";
        $content .= "</tr>";

        $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
        $faculties = $facultyRepository->findBy([], ["systemId" => 'ASC']);
        $facultyNumber = 1;
        $universityStats = [
            6 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            7 => 0,
            10 => 0,
        ];
        foreach ($faculties as $faculty) {
            if ($faculty->getSystemId() <= 6) {
                $facultyStats = $faculty->getStudentCountByRegion();
                $content .= "<tr>";
                $content .= "<td>" . $facultyNumber . "</td>";
                $content .= "<td>" . $faculty->getNameTurkmen() . "</td>";
                foreach ($facultyStats as $key => $value) {
                    $content .= "<td>" . $value . "</td>";
                    $universityStats[$key] += $facultyStats[$key];
                }
                $content .= "</tr>";
                $facultyNumber++;
            }
        }
        $content .= "<tr>";
        $content .= "<td></td>";
        $content .= "<td>JEMI</td>";
        foreach ($universityStats as $key => $value) {
            $content .= "<td>" . $value . "</td>";
        }
        $content .= "</tr>";
        $content .= "</table>";

        return $this->render('analytics/index.html.twig', [
                    'controller_name' => 'AnalyticsController',
                    'year' => $this->currentYear,
                    'analytics_content' => $content
        ]);
    }

}
