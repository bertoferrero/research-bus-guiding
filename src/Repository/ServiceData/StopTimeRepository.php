<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\StopTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StopTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method StopTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method StopTime[]    findAll()
 * @method StopTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StopTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StopTime::class);
    }

    // /**
    //  * @return StopTime[] Returns an array of StopTime objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StopTime
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
