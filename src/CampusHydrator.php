<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

/**
 * Description of CampusHydrator
 *
 * @author nazar
 */
class CampusHydrator extends AbstractHydrator {

    //put your code here
    protected function hydrateAllData() {
        $result = [];
        $i = 1;
//        
//        foreach ($this->_stmt->fetchAll() as $row) {
//            $row['row_number'] = $i;
//            echo json_encode($row);
//            $result[] = $row;
//            $i++;
//        }
        //$this->_stmt->execute(['row_number', 'system_id', 'letter_code']);
        while ($row = $this->_stmt->fetch()) {
            //echo json_encode($row);
            $result[] = $row;
        }
        return $result;
    }
}
