<?php

namespace App\Repository;

use App\Entity\EnrolledStudent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method EnrolledStudent|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnrolledStudent|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnrolledStudent[]    findAll()
 * @method EnrolledStudent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnrolledStudentRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, EnrolledStudent::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function getRecords(array $params = []) {
        $stmt = $this->connection->prepare("SET @row_number := 0;");
        $stmt->execute();

        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%' ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        } else {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
        }
//        $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "`";
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

    public function findLikeByField(string $field, string $value) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\EnrolledStudent', 'u');
        $sql = "SELECT * FROM `enrolled_student` WHERE " . $field . " LIKE '%" . $value . "%' LIMIT 0,15";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function getLastInserted(array $params = []): array {
        $sql = "SELECT * FROM `" . $params['table'] . "` WHERE id = LAST_INSERT_ID();";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function save(EnrolledStudent $enrolledStudent): void {
        $this->getEntityManager()->persist($enrolledStudent);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(EnrolledStudent $enrolledStudent): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function updateData(EnrolledStudent $enrolledStudent, $data): void {
        $sql = "UPDATE `enrolled_student` SET data='" . $data . "' WHERE id = " . $enrolledStudent->getId() . ";";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return;
    }

    public function remove(EnrolledStudent $enrolledStudent): void {
        $this->getEntityManager()->remove($enrolledStudent);
        $this->getEntityManager()->flush();
        return;
    }

}
