<?php

namespace App\Library\Repository;

use App\Library\Entity\LibraryUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LibraryUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method LibraryUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method LibraryUnit[]    findAll()
 * @method LibraryUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LibraryUnitRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LibraryUnit::class);
    }
}
