<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageController
 *
 * @author NMammedov
 */

namespace App\Utility;

class LocaleConverter {
    /*
     * Converts dd.mm.yyyy format date to yyyy-mm-dd 00:00:00
     */

    public function convertToSqlDate($date, $long = true): string {
        if (strlen($date) < 8) {
            return "";
        } else {
            $dateParts = explode(".", $date);
            if ($long) {
                return $dateParts[2] . "-" . $dateParts[1] . "-" . $dateParts[0] . " 00:00:00";
            } else {
                return $dateParts[2] . "-" . $dateParts[1] . "-" . $dateParts[0];
            }
        }
    }

}
