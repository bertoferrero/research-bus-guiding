<?php

namespace App\Repository\ServiceData;

use Doctrine\ORM\QueryBuilder;
use App\Entity\ServiceData\Shape;
use App\Entity\ServiceData\ShapePoint;
use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Trip;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;

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

    protected function nearestPointsQueryBuilder(float $latitude, float $longitude, float $distanceLimit = 0, array $previousStopsForPoints = [], ?ShapePoint $alwaysForwardPoint = null): QueryBuilder
    {
        //https://stackoverflow.com/questions/2234204/find-nearest-latitude-longitude-with-an-sql-query
        //https://github.com/beberlei/DoctrineExtensions
        $query = $this->createQueryBuilder('r');
        $query->addSelect('(6371 *
        acos(cos(radians(:latitude)) * 
        cos(radians(r.latitude)) * 
        cos(radians(r.longitude) - 
        radians(:longitude)) + 
        sin(radians(:latitude)) * 
        sin(radians(r.latitude))))*1000 as distance');
        $query->orderBy('distance', ' ASC');
        $query->setParameters(['latitude' => $latitude, 'longitude' => $longitude]);
        if (!empty($previousStopsForPoints)) {
            $query->andWhere('r.prevStopInRoute IN (:previousStops)')->setParameter('previousStops', $previousStopsForPoints);
            if($alwaysForwardPoint != null){
                $query->andWhere('(
                    r.nextStopRemainingDistance <= :alwf_remainingDistance
                    OR
                    r.prevStopInRoute != :alwf_prevStop
                    OR
                    r.shape != :alwf_shape
                )');
                $query->setParameter("alwf_remainingDistance", $alwaysForwardPoint->getNextStopRemainingDistance());
                $query->setParameter("alwf_prevStop", $alwaysForwardPoint->getPrevStopInRoute());
                $query->setParameter("alwf_shape", $alwaysForwardPoint->getShape());
            }
        }
        if ($distanceLimit > 0) {
            $query->having('distance <= :distanceLimit')->setParameter('distanceLimit', $distanceLimit);
        }
        return $query;
    }

    /**
     * Returns the nearest point for a specific shape
     *
     * @param float $latitude
     * @param float $longitude
     * @param Shape $shape
     * @param array $previousStopsForPoints
     * @return ShapePoint|null
     */
    public function findNearestPoint(float $latitude, float $longitude, Shape $shape, float $distanceLimit = 0, array $previousStopsForPoints = []): ?ShapePoint
    {
        $query = $this->nearestPointsQueryBuilder($latitude, $longitude, $distanceLimit, $previousStopsForPoints);
        $query->andWhere('r.shape = :shape')->setParameter('shape', $shape);
        $query->setMaxResults(1);
        $result = $query->getQuery()->getOneOrNullResult();
        if (!empty($result)) {
            return $result[0];
        }
        return null;
    }

    /**
     * Returns the nearest point for a set of trips
     *
     * @param float $latitude
     * @param float $longitude
     * @param array<int,Trip> $trips
     * @param array<int,Stop> $previousStopsForPoints
     * @param ShapePoint|null $alwaysForwardPoint this only works if $previousStopsForPoints is defined. Defines the last point in the route from the algorithm must to go ahead. The result cannot be a previous point.
     * @return ShapePoint|null
     */
    public function findNearestPointFromTripSet(float $latitude, float $longitude, float $distanceLimit, array $trips, array $previousStopsForPoints = [], ?ShapePoint $alwaysForwardPoint = null): ?ShapePoint
    {
        $query = $this->nearestPointsQueryBuilder($latitude, $longitude, $distanceLimit, $previousStopsForPoints, $alwaysForwardPoint);
        $query->innerJoin('r.shape', 'shape');
        $query->innerJoin('shape.trips', 'trip', Join::WITH, 'trip IN (:trips)')->setParameter('trips', $trips);
        $query->setMaxResults(1);
        $result = $query->getQuery()->getOneOrNullResult();
        if (!empty($result)) {
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
