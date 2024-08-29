<?php

namespace App\Repository;

use App\Entity\ProgramLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProgramLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgramLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgramLevel[]    findAll()
 * @method ProgramLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramLevelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProgramLevel::class);
    }

    // /**
    //  * @return ProgramLevel[] Returns an array of ProgramLevel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProgramLevel
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
