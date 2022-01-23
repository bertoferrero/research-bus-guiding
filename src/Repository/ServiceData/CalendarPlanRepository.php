<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\CalendarPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendarPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarPlan[]    findAll()
 * @method CalendarPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarPlan::class);
    }

    // /**
    //  * @return CalendarPlan[] Returns an array of CalendarPlan objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CalendarPlan
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
