<?php

namespace App\Library\Repository;

use App\Library\Entity\LibraryLoan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method LibraryLoan|null find($id, $lockMode = null, $lockVersion = null)
 * @method LibraryLoan|null findOneBy(array $criteria, array $orderBy = null)
 * @method LibraryLoan[]    findAll()
 * @method LibraryLoan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LibraryLoanRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, LibraryLoan::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function findLikeByField(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Library\Entity\LibraryLoan', 'u');
        $sql = "SELECT * FROM `" . $params['table'] . "` WHERE `library_unit_id`=" . $params['libraryUnitId'] . " AND " . $params['field'] . " LIKE '%" . $params['value'] . "%' LIMIT 0,15";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function getTopReaders(array $params = []) {
        $sql = "SELECT *, COUNT(user_id) AS total_loans, (SELECT CONCAT(lastname,' ',firstname) FROM user " .
                "WHERE user.id=library_loan.user_id) AS loan_username FROM library_loan WHERE `library_unit_id`=" . $params['libraryUnitId'] . " GROUP BY user_id " .
                "ORDER BY total_loans DESC LIMIT 0,100";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopItems(array $params = []) {
        $sql = "SELECT *, COUNT(library_item_id) AS total_loans, (SELECT CONCAT(main_title,', ',author) ".
                "FROM library_item WHERE library_item.id=library_loan.library_item_id ".
                "LIMIT 0,1) AS loan_title FROM library_loan GROUP BY library_item_id ORDER BY total_loans ".
                "DESC LIMIT 0,100";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
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

    public function save(LibraryLoan $libraryLoan): void {
        $this->getEntityManager()->persist($libraryLoan);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(LibraryLoan $libraryLoan): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(LibraryLoan $libraryLoan): void {
        $this->getEntityManager()->remove($libraryLoan);
        $this->getEntityManager()->flush();
        return;
    }

}
