<?php

namespace App\Hr\Repository;

use App\Hr\Entity\Movement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method Movement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movement[]    findAll()
 * @method Movement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovementRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Movement::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function getMovement($day, $month, $year, $employeeNumber, $movementType) {
        $fresult = array('movement_datetime' => '0000-00-00 00:00:00', 'found' => false);
        $sql = "SELECT movement_date FROM movement WHERE DAY(movement_date)=" . $day . " AND " .
                "YEAR(movement_date)=" . $year . " AND MONTH(movement_date)=" . $month . " AND " .
                "movement_type=" . $movementType . " AND employee_number=" . $employeeNumber . " LIMIT 0,1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $movement = $stmt->fetch();
        //echo "Employee number:" . $employeeNumber . "mov:" . gettype($movement) . "<br>";
        if ($movement) {
            $fresult['found'] = true;
            $fresult['movement_datetime'] = $movement['movement_date'];
        }
        return $fresult;
    }

    public function getRecords(array $params = []) {
        $stmt = $this->connection->prepare("SET @row_number := 0;");
        $stmt->execute();

        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT *, DATE(movement_date) AS movement_day, TIME(movement_date) AS movement_time, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%' ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        } else {
            $sql = "SELECT *, DATE(movement_date) AS movement_day, TIME(movement_date) AS movement_time, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecordCount(array $params = []): int {
        $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" . $params['table'] . "`";
        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" .
                    $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%'";
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result['RecordCount']);
    }

    public function getLastInserted(array $params = []): array {
        $sql = "SELECT * FROM `" . $params['table'] . "` WHERE id = LAST_INSERT_ID();";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function save(Movement $movement): void {
        $this->getEntityManager()->persist($movement);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(Movement $movement): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(Movement $movement): void {
        $this->getEntityManager()->remove($movement);
        $this->getEntityManager()->flush();
        return;
    }

}
