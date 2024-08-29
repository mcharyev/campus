<?php

namespace App\Repository;

use App\Entity\TeacherAttendance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method TeacherAttendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeacherAttendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeacherAttendance[]    findAll()
 * @method TeacherAttendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeacherAttendanceRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, TeacherAttendance::class);
        $this->connection = $this->getEntityManager()->getConnection();
         $this->entityManager = $this->getEntityManager();
    }
    
    public function findScheduleByDay(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\TeacherAttendance', 'u');
        $sql = "SELECT * FROM `teacher_attendance` WHERE " .
                "session='" . $params['session'] . "' AND " .
				"DATE(`date`)='" . $params['date'] . "' AND " .
                "teacher_id='" . $params['teacher_id'] . "' LIMIT 0,1";
				//echo $sql."<br>";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getOneOrNullResult();
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

    public function save(TeacherAttendance $teacherAttendance): void {
        $this->getEntityManager()->persist($teacherAttendance);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(TeacherAttendance $teacherAttendance): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(TeacherAttendance $teacherAttendance): void {
        $this->getEntityManager()->remove($teacherAttendance);
        $this->getEntityManager()->flush();
        return;
    }

}
