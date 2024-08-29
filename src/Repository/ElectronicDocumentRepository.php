<?php

namespace App\Repository;

use App\Entity\ElectronicDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method ElectronicDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method ElectronicDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method ElectronicDocument[]    findAll()
 * @method ElectronicDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElectronicDocumentRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, ElectronicDocument::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function getRecords(array $params = []) {
        $stmt = $this->connection->prepare("SET @row_number := 0;");
        $stmt->execute();

        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE entry_type = " . $params['entryType'] . " AND "
                    . "" . $params['searchField'] . " " . $params['comparisonOperator'] . " '" . $params['comparisonCharacter'] . $params['searchValue'] .
                    $params['comparisonCharacter'] . "' ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        } else {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE entry_type = " . $params['entryType'] . " "
                    . "ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecordCount(array $params = []): int {
        $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" . $params['table'] . "` WHERE entry_type = " . $params['entryType'];
        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" .
                    $params['table'] . "` WHERE entry_type = " . $params['entryType'] . " AND " . $params['searchField'] . " " . $params['comparisonOperator'] .
                    " '" . $params['comparisonCharacter'] . $params['searchValue'] . $params['comparisonCharacter'] . "'";
        }
        //echo $sql;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result['RecordCount']);
    }

    public function getLastInserted(array $params = []): array {
        $sql = "SELECT * FROM `" . $params['table'] . "` WHERE id = LAST_INSERT_ID();
";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function save(ElectronicDocument $electronicDocument): void {
        $this->getEntityManager()->persist($electronicDocument);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(ElectronicDocument $electronicDocument): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(ElectronicDocument $electronicDocument): void {
        $this->getEntityManager()->remove($electronicDocument);
        $this->getEntityManager()->flush();
        return;
    }

}
