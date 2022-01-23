<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\CalendarDates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendarDates|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarDates|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarDates[]    findAll()
 * @method CalendarDates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarDatesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarDates::class);
    }

    // /**
    //  * @return CalendarDates[] Returns an array of CalendarDates objects
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
    public function findOneBySomeField($value): ?CalendarDates
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
