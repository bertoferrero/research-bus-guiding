<?php

namespace App\Repository;

use App\Entity\NotificationLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NotificationLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationLog[]    findAll()
 * @method NotificationLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationLog::class);
    }

    // /**
    //  * @return NotificationLog[] Returns an array of NotificationLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NotificationLog
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
