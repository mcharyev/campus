<?php

namespace App\Repository;

use App\Entity\DepartmentWorkItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DepartmentWorkItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepartmentWorkItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepartmentWorkItem[]    findAll()
 * @method DepartmentWorkItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentWorkItemRepository extends ServiceEntityRepository
{
    private $connection;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, DepartmentWorkItem::class);
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

    public function save(DepartmentWorkItem $departmentWorkItem): void {
        $this->getEntityManager()->persist($departmentWorkItem);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(DepartmentWorkItem $departmentWorkItem): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(DepartmentWorkItem $departmentWorkItem): void {
        $this->getEntityManager()->remove($departmentWorkItem);
        $this->getEntityManager()->flush();
        return;
    }
}
