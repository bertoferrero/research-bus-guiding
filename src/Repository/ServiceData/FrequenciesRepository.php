<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\Frequencies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Frequencies|null find($id, $lockMode = null, $lockVersion = null)
 * @method Frequencies|null findOneBy(array $criteria, array $orderBy = null)
 * @method Frequencies[]    findAll()
 * @method Frequencies[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FrequenciesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Frequencies::class);
    }

    // /**
    //  * @return Frequencies[] Returns an array of Frequencies objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Frequencies
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
