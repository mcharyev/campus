<?php

namespace App\Library\Repository;

use App\Library\Entity\LibraryItemType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LibraryItemType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LibraryItemType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LibraryItemType[]    findAll()
 * @method LibraryItemType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LibraryItemTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LibraryItemType::class);
    }
}
