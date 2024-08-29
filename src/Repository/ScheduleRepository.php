<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function getRecordCount(array $params = null): int {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
                'SELECT COUNT(t.id)
            FROM App\Entity\Schedule t'
        );
        return $query->getSingleScalarResult();
    }

    // /**
    //  * @return [] Returns a raw result set
    //  */

    public function getRecords(array $params = null) {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SET @row_number := 0;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "SELECT *,@row_number:=@row_number+1 AS row_number FROM schedule LIMIT 0, 10";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['startIndex' => $params['startIndex'], 'pageSize' => $params['pageSize']]);
        //$stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    // /**
    //  * @return array Returns a row result sql
    //  */

    public function getLastInserted(): array {
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->fetchAll("SELECT * FROM schedule WHERE id = LAST_INSERT_ID();");
        return $result;
    }

    public function save(Schedule $schedule): void {
        $this->getEntityManager()->persist($schedule);
        $this->getEntityManager()->flush();
        return;
    }

    public function update(Schedule $schedule): void {
        $this->getEntityManager()->flush();
        return;
    }

    public function remove(Schedule $schedule): void {
        $this->getEntityManager()->remove($schedule);
        $this->getEntityManager()->flush();
        return;
    }
}
