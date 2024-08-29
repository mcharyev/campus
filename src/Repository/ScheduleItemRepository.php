<?php

namespace App\Repository;

use App\Entity\ScheduleItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method ScheduleItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduleItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduleItem[]    findAll()
 * @method ScheduleItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleItemRepository extends ServiceEntityRepository {

    private $connection;
    private $entityManager;

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, ScheduleItem::class);
        $this->connection = $this->getEntityManager()->getConnection();
        $this->entityManager = $this->getEntityManager();
    }

    public function findScheduleItems(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\ScheduleItem', 'u');
        if ($params['exactMatch']) {
            $sql = "SELECT * FROM `" . $params['table'] . "` WHERE `" . $params['field'] . "` = '" . $params['value'] . "'";
        }
        else
        {
            $sql = "SELECT * FROM `" . $params['table'] . "` WHERE `" . $params['field'] . "` LIKE '%" . $params['value'] . "%'";
        }
        //$sql = "SELECT * FROM schedule_item WHERE " . $params['studentGroups'] . " IN (`student_groups`)";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    /**
     * @description Finds all schedule items for the group
     */
    public function findGroupScheduleItemsAll($group_code) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\ScheduleItem', 'u');
        //$sql = "SELECT * FROM `" . $params['table'] . "` WHERE `" . $params['field'] . "` LIKE '%" . $params['value'] . "%'";
        $sql = "SELECT * FROM `schedule_item` WHERE `student_groups` LIKE '%" . $group_code . "%' ORDER BY day ASC, session ASC;";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function findGroupScheduleItemsAllNoSiw(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\ScheduleItem', 'u');
        //$sql = "SELECT * FROM `" . $params['table'] . "` WHERE `" . $params['field'] . "` LIKE '%" . $params['value'] . "%'";
        $sql = "SELECT * FROM `schedule_item` WHERE `student_groups` LIKE '%" . $params['group_code'] . "%' AND class_type_id<>6 ORDER BY day ASC, session ASC;";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function findCourseScheduleItems(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\ScheduleItem', 'u');
        $sql = "SELECT * FROM `schedule_item` WHERE taught_course_id='" . $params['course_id'] . "' AND " .
                "session='" . $params['session'] . "' AND day='" . $params['day'] . "' ORDER BY day ASC, session ASC;";
        //$sql = "SELECT * FROM schedule_item WHERE " . $params['studentGroups'] . " IN (`student_groups`)";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getOneorNullResult();
    }

    public function findCourseScheduleItemsAll(array $params = []) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\ScheduleItem', 'u');
        $sql = "SELECT * FROM `schedule_item` WHERE taught_course_id='" . $params['course_id'] . "' ORDER BY day ASC, session ASC;";
        //$sql = "SELECT * FROM schedule_item WHERE " . $params['studentGroups'] . " IN (`student_groups`)";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    /**
     * @description Finds schedule items for the teacher
     */
    public function findTeacherScheduleItemsAll($teacher_id) {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\ScheduleItem', 'u');
        $sql = "SELECT * FROM `schedule_item` WHERE teacher_id='" . $teacher_id . "' ORDER BY day ASC, session ASC;";
        //$sql = "SELECT * FROM schedule_item WHERE " . $params['studentGroups'] . " IN (`student_groups`)";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    /**
     * @description Finds distinct schedule item types for the course
     */
    public function getDistinctScheduleTypes(array $params = []) {
        $sql = "SELECT DISTINCT `class_type_id` FROM `" . $params['table'] . "` WHERE taught_course_id=" . $params['taughtCourseId'] . ";";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecords(array $params = []) {
        $stmt = $this->connection->prepare("SET @row_number := 0;");
        $stmt->execute();

        if (!empty($params['searchField']) && !empty($params['searchValue'])) {
            if ($params['exactMatch']) {
                $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE " . $params['searchField'] . " = '" . $params['searchValue'] . "' ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
            } else {
                $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%' ORDER BY " . $params['sorting'] . " LIMIT " . $params['offset'] . ", " . $params['pageSize'];
            }
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
            if ($params['exactMatch']) {
                $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" .
                        $params['table'] . "` WHERE " . $params['searchField'] . " = '" . $params['searchValue'] . "'";
            } else {
                $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" .
                        $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%'";
            }
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

    public function save(ScheduleItem $scheduleItem): void {
        $this->getEntityManager()->persist($scheduleItem);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(ScheduleItem $scheduleItem): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(ScheduleItem $scheduleItem): void {
        $this->getEntityManager()->remove($scheduleItem);
        $this->getEntityManager()->flush();
        return;
    }

}
