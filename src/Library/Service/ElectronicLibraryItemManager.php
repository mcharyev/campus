<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Service;

use App\Library\Entity\ElectronicLibraryItem;
use App\Library\Entity\LibraryUnit;
use App\Entity\Language;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * Description of ElectronicLibraryItemManager
 *
 * @author nazar
 */
class ElectronicLibraryItemManager {

    //put your code here
    public function createFromRequest(Request $request, Language $language, LibraryUnit $libraryUnit): ElectronicLibraryItem {
        $libraryItem = new ElectronicLibraryItem();
        $libraryItem->setType($request->request->get('type'))
                ->setMainTitle($request->request->get('main_title'))
                ->setSecondaryTitle($request->request->get('secondary_title'))
                ->setAuthor($request->request->get('author'))
                ->setWriterNumber($request->request->get('writer_number'))
                ->setYear($request->request->get('year'))
                ->setEdition($request->request->get('edition'))
                ->setNumber($request->request->get('number'))
                ->setVolume($request->request->get('volume'))
                ->setCopyNumber($request->request->get('copy_number'))
                ->setCallNumber($request->request->get('call_number'))
                ->setCallNumberOriginal($request->request->get('call_number'))
                ->setIsbn($request->request->get('isbn'))
                ->setUok($request->request->get('uok'))
                ->setStatus($request->request->get('status'))
                ->setPublisher($request->request->get('publisher'))
                ->setPlace($request->request->get('place'))
                ->setDateCreated(new \DateTime($request->request->get('date_created')))
                ->setDateUpdated(new \DateTime())
                ->setLanguage($language)
                ->setLibraryUnit($libraryUnit)
                ->setPrice(0)
                ->setInvoice('');
        if (strlen($libraryItem->getCallNumberOriginal()) < 6) {
            if (intval($libraryItem->getCallNumberOriginal()) == 0) {
                $libraryItem->setCallNumberOriginal($libraryItem->getCallNumber());
            } else {
                $libraryItem->SetCallNumberOriginal(str_pad($libraryItem->getCallNumberOriginal(), 6, "0", STR_PAD_LEFT));
            }
        }
        return $libraryItem;
    }

    public function updateFromRequest(Request $request, ElectronicLibraryItem $libraryItem, Language $language, LibraryUnit $libraryUnit): ElectronicLibraryItem {
        $libraryItem->setType($request->request->get('type'))
                ->setMainTitle($request->request->get('main_title'))
                ->setSecondaryTitle($request->request->get('secondary_title'))
                ->setAuthor($request->request->get('author'))
                ->setWriterNumber($request->request->get('writer_number'))
                ->setYear($request->request->get('year'))
                ->setEdition($request->request->get('edition'))
                ->setNumber($request->request->get('number'))
                ->setVolume($request->request->get('volume'))
                ->setCopyNumber($request->request->get('copy_number'))
                ->setCallNumber($request->request->get('call_number'))
                ->setCallNumberOriginal($request->request->get('call_number'))
                ->setIsbn($request->request->get('isbn'))
                ->setUok($request->request->get('uok'))
                ->setStatus($request->request->get('status'))
                ->setPublisher($request->request->get('publisher'))
                ->setPlace($request->request->get('place'))
                ->setDateCreated(new \DateTime($request->request->get('date_created')))
                ->setDateUpdated(new \DateTime())
                ->setLanguage($language)
                ->setLibraryUnit($libraryUnit)
                ->setPrice(0)
                ->setInvoice('');
        if (strlen($libraryItem->getCallNumberOriginal()) < 6) {
            if (intval($libraryItem->getCallNumberOriginal()) == 0) {
                $libraryItem->setCallNumberOriginal($libraryItem->getCallNumber());
            } else {
                $libraryItem->SetCallNumberOriginal(str_pad($libraryItem->getCallNumberOriginal(), 6, "0", STR_PAD_LEFT));
            }
        }
        return $libraryItem;
    }

}
