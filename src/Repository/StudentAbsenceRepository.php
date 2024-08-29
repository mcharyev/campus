<?php

namespace App\Repository;

use App\Entity\StudentAbsence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method StudentAbsence|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentAbsence|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentAbsence[]    findAll()
 * @method StudentAbsence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentAbsenceRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, StudentAbsence::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function getTopAbsences(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\StudentAbsence', 'u');
        //$rsm->addFieldResult('u', 'total_absence', '$fieldName')
        $sql = "SELECT *, COUNT(student_id) as total_absence FROM student_absence " .
                "GROUP BY student_id ORDER BY total_absence DESC LIMIT 0,30";
        //echo $sql."<br>";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function getTotalAbsences(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\StudentAbsence', 'u');
        if ($params['beginDate'] != null && $params['endDate'] != null) {
            $sql = "SELECT *, COUNT(student_id) as total_absence FROM student_absence " .
                    " WHERE " . $params['field'] . "='" . $params['value'] . "' AND " .
                    "date >= '" . $params['beginDate'] . "' AND date < '" . $params['endDate'] . "' " .
                    "GROUP BY student_id ORDER BY total_absence DESC";
            //echo $sql."<br>";
        } else {
            $sql = "SELECT *, COUNT(student_id) as total_absence FROM student_absence " .
                    " WHERE " . $params['field'] . "='" . $params['value'] . "' " .
                    "GROUP BY student_id ORDER BY total_absence DESC";
        }
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function findStudentAbsence(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\StudentAbsence', 'u');
        //$rsm->addFieldResult('u', 'total_absence', '$fieldName')
        $sql = "SELECT * FROM student_absence WHERE course_id=" . $params['course_id'] . " AND session=" . $params['session'] .
                " AND student_id=" . $params['student_id'] . " AND DATE(`date`)='" . $params['date'] . "';";
        //echo $sql."<br>";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getOneorNullResult();
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

    public function save(StudentAbsence $studentAbsence): void {
        $this->getEntityManager()->persist($studentAbsence);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(StudentAbsence $studentAbsence): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(StudentAbsence $studentAbsence): void {
        $this->getEntityManager()->remove($studentAbsence);
        $this->getEntityManager()->flush();
        return;
    }

}
