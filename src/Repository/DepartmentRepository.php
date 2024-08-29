<?php

namespace App\Repository;

use App\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Department|null find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Department::class);
    }

    // /**
    //  * @return int Returns an integer representing row count for sql
    //  */

    public function getRecordCount(array $params = null): int {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
                'SELECT COUNT(d.id)
            FROM App\Entity\Department d'
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

        $sql = "SELECT *,@row_number:=@row_number+1 AS row_number FROM department  ORDER BY ".$params['sorting']." LIMIT ".$params['startIndex'].", ".$params['pageSize'];
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
        $result = $connection->fetchAll("SELECT * FROM department WHERE id = LAST_INSERT_ID();");
        return $result;
    }

    public function save(Department $department): void {
        $this->getEntityManager()->persist($department);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(Department $department): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(Department $department): void {
        $this->getEntityManager()->remove($department);
        $this->getEntityManager()->flush();
        return;
    }

}
