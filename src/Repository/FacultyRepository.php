<?php

namespace App\Repository;

use App\Entity\Faculty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Faculty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faculty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faculty[]    findAll()
 * @method Faculty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacultyRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Faculty::class);
    }

    // /**
    //  * @return int Returns an integer representing row count for sql
    //  */

    public function getRecordCount(array $params = null): int {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
                'SELECT COUNT(f.id)
            FROM App\Entity\Faculty f'
        );
        return $query->getSingleScalarResult();
    }

    // /**
    //  * @return [] Returns a raw result set
    //  */

    public function getRecords(array $params = null) {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SET @row_number := 0;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "SELECT *,@row_number:=@row_number+1 AS row_number FROM faculty LIMIT 0, 10";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['startIndex' => $params['startIndex'], 'pageSize' => $params['pageSize']]);
        //$stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    // /**
    //  * @return array Returns a row result sql
    //  */

    public function getLastInserted(): array {
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->fetchAll("SELECT * FROM faculty WHERE id = LAST_INSERT_ID();");
        return $result;
    }

    public function save(Faculty $faculty): void {
        $this->getEntityManager()->persist($faculty);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(Faculty $faculty): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(Faculty $faculty): void {
        $this->getEntityManager()->remove($faculty);
        $this->getEntityManager()->flush();
        return;
    }
}
