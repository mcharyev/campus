<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Service;

use App\Library\Entity\LibraryLoan;
use App\Library\Entity\LibraryUnit;
use App\Library\Entity\LibraryItem;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of EnrolledStudentManager
 *
 * @author nazar
 */
class LibraryLoanManager {

    //put your code here
    public function createFromRequest(Request $request, LibraryItem $libraryLoan,
            LibraryUnit $libraryUnit, User $user, User $author): LibraryLoan {
        $libraryLoan = new LibraryLoan();
        return $libraryLoan;
    }

    public function updateFromRequest(Request $request, LibraryItem $libraryLoan,
            LibraryUnit $libraryUnit, User $user, User $author): LibraryLoan {
        return $libraryLoan;
    }

}
