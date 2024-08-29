<?php

namespace App\Repository;

use App\Entity\ProgramModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProgramModule|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgramModule|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgramModule[]    findAll()
 * @method ProgramModule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramModuleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProgramModule::class);
    }

    // /**
    //  * @return ProgramModule[] Returns an array of ProgramModule objects
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
    public function findOneBySomeField($value): ?ProgramModule
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
