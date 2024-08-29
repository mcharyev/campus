<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\HostelRoom;
use App\Entity\Teacher;
use App\Entity\Hostel;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of HostelroomManager
 *
 * @author nazar
 */
class HostelRoomManager {

    //put your code here
    public function createFromRequest(Request $request, Teacher $teacher, Hostel $hostel): HostelRoom {
        $hostelroom = new HostelRoom();
        $hostelroom->setFloor($request->request->get('floor'))
                ->setRoomNumber($request->request->get('room_number'))
                ->setRoomName($request->request->get('room_name'))
                ->setInstructor($teacher)
                ->setHostel($hostel)
                ->setDateUpdated(new \DateTime());
        return $hostelroom;
    }

    public function updateFromRequest(Request $request, HostelRoom $hostelroom, Teacher $teacher, Hostel $hostel): HostelRoom {
        $hostelroom->setFloor($request->request->get('floor'))
                ->setRoomNumber($request->request->get('room_number'))
                ->setRoomName($request->request->get('room_name'))
                ->setInstructor($teacher)
                ->setHostel($hostel)
                ->setDateUpdated(new \DateTime());
        return $hostelroom;
    }

}
