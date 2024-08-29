<?php

namespace App\Library\Repository;

use App\Library\Entity\LibraryAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LibraryAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method LibraryAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method LibraryAccess[]    findAll()
 * @method LibraryAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LibraryAccessRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LibraryAccess::class);
    }

    
}
