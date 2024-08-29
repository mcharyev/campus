<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppExtension
 *
 * @author support
 */
// src/Twig/AppExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension {

    public function getFilters() {
        return [
            new TwigFilter('fourpointgrade', [$this, 'fourPointGrade']),
            new TwigFilter('academicstanding', [$this, 'academicStanding']),
        ];
    }

    public function fourPointGrade($number) {
        $result = 0;
        if ($number < 50) {
            $result = 0;
        } elseif ($number >= 50 && $number <= 54) {
            $result = 1.00;
        } elseif ($number >= 55 && $number <= 59) {
            $result = 1.33;
        } elseif ($number >= 60 && $number <= 64) {
            $result = 1.67;
        } elseif ($number >= 65 && $number <= 69) {
            $result = 2.00;
        } elseif ($number >= 70 && $number <= 74) {
            $result = 2.33;
        } elseif ($number >= 75 && $number <= 79) {
            $result = 2.67;
        } elseif ($number >= 80 && $number <= 84) {
            $result = 3.00;
        } elseif ($number >= 85 && $number <= 89) {
            $result = 3.33;
        } elseif ($number >= 90 && $number <= 94) {
            $result = 3.67;
        } elseif ($number >= 95 && $number <= 100) {
            $result = 4.00;
        }
        return number_format($result, 2);
    }
    
    public function academicStanding(float $number) {
        $result = "FAIL";
        if ($number >= 1.00 && $number <= 2.00) {
            $result = "PROBATION";
        } elseif ($number >= 2.01 && $number <= 2.67) {
            $result = "SATISFACTORY";
        } elseif ($number >= 2.68 && $number <= 3.33) {
            $result = "HONOUR";
        } elseif ($number >= 3.34 && $number <= 4.00) {
            $result = "HIGH HONOUR";
        }
        
        return $result;
    }

}
