<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

/**
 * Description of AbstractStudent
 *
 * @author nazar
 */
abstract class AbstractStudent {
    //put your code here

    /**
     * @return string[]
     */
    public function getFullname() {
        return $this->getLastnameTurkmen() . " " . $this->getFirstnameTurkmen();
    }

    /**
     * @return string[]
     */
    public function getThreenames() {
        if (mb_strlen($this->getPreviousLastnameTurkmen()) > 0) {
            return $this->getLastnameTurkmen() . " (" . $this->getPreviousLastnameTurkmen() . ") " . $this->getFirstnameTurkmen() . " " . $this->getPatronymTurkmen();
        } else {
            return $this->getLastnameTurkmen() . " " . $this->getFirstnameTurkmen() . " " . $this->getPatronymTurkmen();
        }
    }

}
