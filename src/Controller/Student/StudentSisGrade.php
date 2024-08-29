<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Student;

/**
 * Description of StudentSisGrade
 *
 * @author NMammedov
 */
class StudentSisGrade {

    //put your code here
    private $orderNumber;
    private $systemId;
    private $fullname;
    private $midterm;
    private $final;
    private $makeup;
    private $siwsi;
    private $average;

    public function __construct(array $params = []) {
        $this->orderNumber = $params['ordernumber'];
        $this->systemId = $params['systemid'];
        $this->fullname = $params['fullname'];
        $this->midterm = $params['midterm'];
        $this->final = $params['final'];
        $this->makeup = $params['makeup'];
        $this->siwsi = $params['siwsi'];
        $this->average = $params['average'];
    }

    public function getFullname() {
        return $this->fullname;
    }

    public function getMidterm() {
        return $this->midterm;
    }

    public function getFinal() {
        return $this->final;
    }

    public function getMakeup() {
        return $this->makeup;
    }

    public function getSiwsi() {
        return $this->siwsi;
    }

    public function getAverage() {
        return intval($this->average);
    }

    public function getSystemId() {
        return $this->systemId;
    }

    public function getOrderNumber() {
        return $this->ordernumber;
    }

    public function getFivePointSystemGrade() {
        $avg = intval($this->getAverage());
        if ($avg >= 0 && $avg <= 49) {
            return 2;
        }
        if ($avg >= 50 && $avg <= 69) {
            return 3;
        }
        if ($avg >= 70 && $avg <= 84) {
            return 4;
        }
        if ($avg >= 85 && $avg <= 100) {
            return 5;
        }
        return 2;
    }

}
