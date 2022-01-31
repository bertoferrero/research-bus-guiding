<?php

namespace App\Repository\ServiceData;

use Doctrine\ORM\QueryBuilder;
use App\Entity\ServiceData\Shape;
use App\Entity\ServiceData\ShapePoint;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ShapePoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShapePoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShapePoint[]    findAll()
 * @method ShapePoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShapePointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShapePoint::class);
    }

    protected function nearestPointsQueryBuilder(float $latitude, float $longitude, Shape $shape, array $previousStopsForPoints = []): QueryBuilder
    {
        //https://stackoverflow.com/questions/2234204/find-nearest-latitude-longitude-with-an-sql-query
        //https://github.com/beberlei/DoctrineExtensions
        $query = $this->createQueryBuilder('r');
        $query->addSelect('(3959 *
        acos(cos(radians(:latitude)) * 
        cos(radians(r.latitude)) * 
        cos(radians(r.longitude) - 
        radians(:longitude)) + 
        sin(radians(:latitude)) * 
        sin(radians(r.latitude)))) as distance');
        $query->andWhere('r.shape = :shape');
        $query->orderBy('distance', ' ASC');
        $query->setParameters(['shape'=>$shape, 'latitude' => $latitude, 'longitude' => $longitude]);
        if (!empty($previousStopsForPoints)) {
            $query->andWhere('r.prevStopInRoute IN (:previousStops)')->setParameter('previousStops', $previousStopsForPoints);
        }
        return $query;
    }

    public function findNearestPoint(float $latitude, float $longitude, Shape $shape, array $previousStopsForPoints = []): ?ShapePoint
    {
        $query = $this->nearestPointsQueryBuilder($latitude, $longitude, $shape, $previousStopsForPoints);
        $query->setMaxResults(1);
        $result = $query->getQuery()->getOneOrNullResult();
        if(!empty($result)){
            return $result[0];
        }
        return null;
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
