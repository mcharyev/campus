<?php

namespace App\Repository;

use App\Entity\ProgramCourseType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProgramCourseType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgramCourseType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgramCourseType[]    findAll()
 * @method ProgramCourseType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramCourseTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProgramCourseType::class);
    }

}
