<?php

namespace App\Repository;

use App\Entity\ClassType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ClassType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassType[]    findAll()
 * @method ClassType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ClassType::class);
    }

    // /**
    //  * @return ClassType[] Returns an array of ClassType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClassType
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
