<?php

namespace App\Repository;

use App\Entity\ClassroomType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ClassroomType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassroomType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassroomType[]    findAll()
 * @method ClassroomType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassroomTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ClassroomType::class);
    }

    // /**
    //  * @return ClassroomType[] Returns an array of ClassroomType objects
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
    public function findOneBySomeField($value): ?ClassroomType
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
