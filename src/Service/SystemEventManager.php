<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\SystemEvent;
use Psr\Log\LoggerInterface;
use App\Repository\SystemEventRepository;

/**
 * Description of StudentAbsenceManager
 *
 * @author nazar
 */
class SystemEventManager {

    private $repository;
    private $logger;

    public function __construct(RegistryInterface $registry, LoggerInterface $logger) {
        $this->logger = $logger;
        $this->repository = new SystemEventRepository($registry);
    }

    //put your code here
    public function create(?Request $request, int $systemEventType, int $subjectType, int $subjectId, int $objectType, int $objectId, string $data): int {
        $systemEvent = new SystemEvent();
        $systemEvent->setType($systemEventType);
        $systemEvent->setSubjectType($subjectType);
        $systemEvent->setSubjectId($subjectId);
        $systemEvent->setObjectType($objectType);
        $systemEvent->setObjectId($objectId);
        $systemEvent->setDateUpdated(new \DateTime());
        $systemEvent->setData($data);
        $this->repository->save($systemEvent);
        return 0;
    }
    
    public function getSystemEventsAfter($time)
    {
        return $this->repository->getRecordsAfter($time);
    }

}
