<?php

namespace App\Library\Repository;

use App\Library\Entity\ElectronicLibraryItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method ElectronicLibraryItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ElectronicLibraryItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ElectronicLibraryItem[]    findAll()
 * @method ElectronicLibraryItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElectronicLibraryItemRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, ElectronicLibraryItem::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function findLikeByField(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Library\Entity\ElectronicLibraryItem', 'u');
        $sql = "SELECT * FROM `" . $params['table'] . "` WHERE `library_unit_id`=" . $params['libraryUnitId'] . " AND " . $params['field'] . " LIKE '%" . $params['value'] . "%' LIMIT 0,15";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function getRecords(array $params = []) {
        $stmt = $this->connection->prepare("SET @row_number := 0;");
        $stmt->execute();

        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE `library_unit_id`=" . $params['libraryUnitId'] . " AND  " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%' ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        } else {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE `library_unit_id`=" . $params['libraryUnitId'] . " ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        }
        //echo $sql;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecordCount(array $params = []): int {
        $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" . $params['table'] . "` WHERE `library_unit_id`=" . $params['libraryUnitId'];
        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" .
                    $params['table'] . "` WHERE `library_unit_id`=" . $params['libraryUnitId'] . " AND " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%'";
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result['RecordCount']);
    }

    public function getLastInserted(array $params = []): array {
        $sql = "SELECT * FROM `" . $params['table'] . "` WHERE `library_unit_id`=" . $params['libraryUnitId'] . " AND id = LAST_INSERT_ID();";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function save(ElectronicLibraryItem $electronicLibraryItem): void {
        $this->getEntityManager()->persist($electronicLibraryItem);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(ElectronicLibraryItem $electronicLibraryItem): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(ElectronicLibraryItem $electronicLibraryItem): void {
        $this->getEntityManager()->remove($electronicLibraryItem);
        $this->getEntityManager()->flush();
        return;
    }

}
