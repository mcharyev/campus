<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Service;

use App\Library\Entity\Publication;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of ElectronicLibraryItemManager
 *
 * @author nazar
 */
class PublicationManager {

    //put your code here
    public function createFromRequest(Request $request, User $author, User $recorder): Publication {
        $publication = new Publication();
        $publication->setTitle($request->request->get('title'))
                ->setPublication($request->request->get('publication'))
                ->setAuthor($author)
                ->setRecorder($recorder)
                ->setDatePublished(new \DateTime($request->request->get('date_created')))
                ->setDateUpdated(new \DateTime());
        return $publication;
    }

    public function updateFromRequest(Request $request, Publication $publication, User $author, User $recorder): Publication {
        $publication->setTitle($request->request->get('title'))
                ->setPublication($request->request->get('publication'))
                ->setAuthor($author)
                ->setRecorder($recorder)
                ->setDatePublished(new \DateTime($request->request->get('date_created')))
                ->setDateUpdated(new \DateTime());
        return $publication;
    }

}
