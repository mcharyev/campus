<?php

namespace App\Repository;

use App\Entity\SystemEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method SystemEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemEvent[]    findAll()
 * @method SystemEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemEventRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, SystemEvent::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function getRecordsAfter($time) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\SystemEvent', 'u');
        $sql = "SELECT * FROM `system_event` WHERE date_updated > '" . $time . "' ORDER BY date_updated DESC";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
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

    public function save(SystemEvent $systemEvent): void {
        $this->getEntityManager()->persist($systemEvent);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(SystemEvent $systemEvent): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(SystemEvent $systemEvent): void {
        $this->getEntityManager()->remove($systemEvent);
        $this->getEntityManager()->flush();
        return;
    }

}
