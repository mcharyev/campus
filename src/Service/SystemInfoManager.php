<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Description of SystemInfoManager
 *
 * @author nazar
 */
class SystemInfoManager {

    private $params;

    public function __construct(ContainerBagInterface $params) {
        $this->params = $params;
    }

    //put your code here
    public function getWorkloadNamesArray() {
        return ['1.00', '0.75', '0.50', '0.25', '0.33', 'Hourly', 'Replacement', 'Vacancy'];
    }

    public function getFallSemesterBeginDate() {
        return $this->params->get('first_semester_begin_date');
    }

    public function getFallSemesterEndDate() {
        return $this->params->get('first_semester_end_date');
    }

    public function getSpringSemesterBeginDate() {
        return $this->params->get('second_semester_begin_date');
    }

    public function getSpringSemesterEndDate() {
        return $this->params->get('second_semester_end_date');
    }

//    public function getCurrentSemesterBeginDate() {
//        return $this->params->get('current_semester_begin_date');
//    }
//
//    public function getCurrentSemesterEndDate() {
//        return $this->params->get('current_semester_end_date');
//    }
//
//    public function getCurrentSemesterFinalEndDate() {
//        return $this->params->get('current_semester_final_end_date');
//    }
//
//    public function getCurrentSemesterFourthYearEndDate() {
//        return $this->params->get('current_semester_fourthyear_end_date');
//    }

    public function getSemesterMonths(int $semester) {
        $months = [[9, 10, 11, 12, 1], [2, 3, 4, 5, 6, 7], [8]];
        return $months[$semester - 1];
    }

    public function getFirstSemesterYear() {
        return $this->params->get('first_semester_year');
    }

    public function getSecondSemesterYear() {
        return ($this->params->get('second_semester_year'));
    }

    public function getCurrentCommencementYear() {
        return $this->getFirstSemesterYear();
    }

    public function getCurrentGraduationYear() {
        return $this->getSecondSemesterYear();
    }

    public function getCurrentYear() {
        return $this->params->get('first_semester_year');
    }

    public function getCurrentSemester() {
        return $this->params->get('current_semester');
    }

    public function getSemesterBeginYear(int $semester): int {
        $years = [
            $this->params->get('first_semester_year'),
            $this->params->get('second_semester_year'),
            $this->params->get('third_semester_year')
        ];
        return $years[$semester - 1];
    }

    public function getSemesterBeginDate(int $semester) {
        $dates = [
            new \DateTime($this->params->get('first_semester_begin_date')),
            new \DateTime($this->params->get('second_semester_begin_date')),
            new \DateTime($this->params->get('third_semester_begin_date')),
        ];
        return $dates[$semester - 1];
    }

    public function getSemesterEndDate(int $semester) {
        $dates = [
            new \DateTime($this->params->get('first_semester_end_date')),
            new \DateTime($this->params->get('second_semester_end_date')),
            new \DateTime($this->params->get('third_semester_end_date')),
        ];
        return $dates[$semester - 1];
    }

    public function getSemesterFinalEndDate(int $semester) {
        $dates = [
            new \DateTime($this->params->get('first_semester_final_end_date')),
            new \DateTime($this->params->get('second_semester_final_end_date')),
            new \DateTime($this->params->get('third_semester_final_end_date')),
        ];
        return $dates[$semester - 1];
    }

    public function getSemesterFourthYearEndDate(int $semester) {
        $dates = [
            new \DateTime($this->params->get('first_semester_fourth_year_end_date')),
            new \DateTime($this->params->get('second_semester_fourth_year_end_date')),
            new \DateTime($this->params->get('third_semester_fourth_year_end_date')),
        ];
        return $dates[$semester - 1];
    }

    /*
     * Trimester functions
     */

    public function getCurrentTrimester() {
        return $this->params->get('current_trimester');
    }

    public function getTrimesterBeginDate(int $semester) {
        $dates = [
            new \DateTime($this->params->get('first_trimester_begin_date')),
            new \DateTime($this->params->get('second_trimester_begin_date')),
            new \DateTime($this->params->get('third_trimester_begin_date')),
        ];
        return $dates[$semester - 1];
    }

    public function getTrimesterEndDate(int $semester) {
        $dates = [
            new \DateTime($this->params->get('first_trimester_end_date')),
            new \DateTime($this->params->get('second_trimester_end_date')),
            new \DateTime($this->params->get('third_trimester_end_date')),
        ];
        return $dates[$semester - 1];
    }

    public function getTrimesterFinalEndDate(int $semester) {
        $dates = [
            new \DateTime($this->params->get('first_trimester_final_end_date')),
            new \DateTime($this->params->get('second_trimester_final_end_date')),
            new \DateTime($this->params->get('third_trimester_final_end_date')),
        ];
        return $dates[$semester - 1];
    }

    public function getTrimesterBeginYear(int $semester): int {
        $years = [
            $this->params->get('first_trimester_year'),
            $this->params->get('second_trimester_year'),
            $this->params->get('third_trimester_year')
        ];
        return $years[$semester - 1];
    }

    public function getTrimesterMonths(int $semester) {
        $months = [[9, 10, 11], [12, 1, 2, 3], [3, 4, 5, 6]];
        return $months[$semester - 1];
    }

//    public function getThirdTrimesterBeginDate() {
//        return "2020-03-16";
//    }
//
//    public function getThirdTrimesterEndDate() {
//        return "2020-06-07";
//    }

    public function getPublicBuildPath() {
        return $this->params->get('public_build_path');
    }

    public function getRootPath() {
        return $this->params->get('root_path');
    }

    public function getLLDSystemId() {
        return $this->params->get('lld_system_id');
    }

    public function getWeekdays() {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    public function getSessions($isLLD = false) {
        if ($isLLD) {
            return ['1) 9:00 - 9:50', '2) 10:00 - 11:20', '3) 11:40 - 13:00', '4) 14:00 - 15:20', '5) 15:30 - 16:50', '6) 17:00 - 18:20', '7)', '8)'];
        } else {
            //return ['1) 8:30 - 9:50', '2) 10:00 - 11:20', '3) 11:40 - 13:00', '4) 14:00 - 15:20', '5) 15:30 - 16:50', '6) 17:00 - 18:20', '7)', '8)'];
            return ['1) 8:30 - 9:10', '2) 9:20 - 10:00', '3) 10:10 - 10:50', '4) 11:10 - 11:50', '5) 12:00 - 12:40', '6) 13:30 - 14:10', '7) 14:20 - 15:00', '8) 15:10 - 15:50', '9) 16:00 - 16:40', '10) 16:00 - 16:40', '11) 16:00 - 16:40', '12) 16:00 - 16:40'];
        }
    }

}
