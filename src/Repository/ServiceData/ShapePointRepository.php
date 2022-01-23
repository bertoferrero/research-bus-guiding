<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\ShapePoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Shape|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shape|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shape[]    findAll()
 * @method Shape[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShapePointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShapePoint::class);
    }

    // /**
    //  * @return ShapePoint[] Returns an array of ShapePoint objects
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
    public function findOneBySomeField($value): ?ShapePoint
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
