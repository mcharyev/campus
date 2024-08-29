<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Workload;

/**
 * Description of WorkColumnsArray
 *
 * @author NMammedov
 */
class WorkColumnsArray {
    //put your code here
    public function getEmptyWorkColumnsArray() {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    }

    public function getWorkColumnTexts() {
        return ['', 'T/b', 'Ady', 'Topar.', 'Okuw ý.', 'Tal. sn.', 'Tap. sn.', 'Top. sn', 'Tpç sn.',
            'UOM', 'UOJ', 'AOM', 'AOJ', 'TOM', 'TOJ', 'Berk.s.', 'Has.', 'Ara. sy.', 'Jem. sy.', 'Döw. sy.', 'MÝTÖI',
            'Tab. barl.', 'Ön. tej.', 'Dip. ýolb.', 'Dip. gor.', 'D. bäsl.', 'Sap. gatn.', 'Fak. kaf. ý', 'Syn ý.', 'Jemi'];
    }
}
