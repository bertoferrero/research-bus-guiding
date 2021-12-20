<?php

namespace App\Repository;

use DateTime;
use App\Entity\StopRequest;
use App\Lib\Enum\StopRequestStatusEnum;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method StopRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method StopRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method StopRequest[]    findAll()
 * @method StopRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StopRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StopRequest::class);
    }

    public function findPendingToCancel(DateTime $dateLimit){
        return $this->createQueryBuilder('entity')
        ->andWhere('entity.status = :status')->setParameter('status', StopRequestStatusEnum::PENDING)
        ->andWhere('entity.dateAdd < :dateLimit')->setParameter('dateLimit', $dateLimit)
        ->getQuery()->getResult();
    }

    // /**
    //  * @return StopRequest[] Returns an array of StopRequest objects
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
    public function findOneBySomeField($value): ?StopRequest
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
