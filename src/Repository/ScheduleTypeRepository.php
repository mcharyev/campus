<?php

namespace App\Repository;

use App\Entity\ScheduleType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ScheduleType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduleType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduleType[]    findAll()
 * @method ScheduleType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ScheduleType::class);
    }

    // /**
    //  * @return ScheduleType[] Returns an array of ScheduleType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ScheduleType
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
