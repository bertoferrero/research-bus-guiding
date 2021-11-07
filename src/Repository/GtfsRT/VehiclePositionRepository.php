<?php

namespace App\Repository\GtfsRT;

use App\Entity\GtfsRT\VehiclePosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VehiclePosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehiclePosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehiclePosition[]    findAll()
 * @method VehiclePosition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiclePositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehiclePosition::class);
    }

    // /**
    //  * @return VehiclePosition[] Returns an array of VehiclePosition objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VehiclePosition
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
