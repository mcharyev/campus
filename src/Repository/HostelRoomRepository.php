<?php

namespace App\Repository;

use App\Entity\HostelRoom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HostelRoom|null find($id, $lockMode = null, $lockVersion = null)
 * @method HostelRoom|null findOneBy(array $criteria, array $orderBy = null)
 * @method HostelRoom[]    findAll()
 * @method HostelRoom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HostelRoomRepository extends ServiceEntityRepository
{
    private $connection;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, HostelRoom::class);
        $this->connection = $this->getEntityManager()->getConnection();
    }

    public function getRecords(array $params = []) {
        $stmt = $this->connection->prepare("SET @row_number := 0;");
        $stmt->execute();

        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%' ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        } else {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
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

    public function save(HostelRoom $hostelroom): void {
        $this->getEntityManager()->persist($hostelroom);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(HostelRoom $hostelroom): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(HostelRoom $hostelroom): void {
        $this->getEntityManager()->remove($hostelroom);
        $this->getEntityManager()->flush();
        return;
    }
}
