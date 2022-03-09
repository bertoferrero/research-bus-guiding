<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\Route;
use App\Entity\ServiceData\StopTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Stop|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stop|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stop[]    findAll()
 * @method Stop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stop::class);
    }

    public function findBySchemaId(string $schemaId)
    {
        $query = $this->createQueryBuilder('entity');
        $query->andWhere('entity.schemaId = :schemaid')->setParameter('schemaid', $schemaId);
        return $query->getQuery()->getOneOrNullResult();
    }

    public function findByTrip(Trip $trip, bool $orderByTripSequence = true)
    {
        $query = $this->createQueryBuilder('entity');
        $query->innerJoin('entity.stopTimes', 'stoptime', Join::WITH, 'stoptime.trip = :trip');
        $query->setParameter('trip', $trip);
        if ($orderByTripSequence) {
            $query->orderBy('stoptime.stopSequence', 'ASC');
        }
        return $query->getQuery()->getResult();
    }

    public function findByTrips(array $trips)
    {
        $query = $this->createQueryBuilder('entity');
        $query->select('entity');
        $query->innerJoin('entity.stopTimes', 'stoptime');
        $query->innerJoin('stoptime.trip', 'trip');
        $query->andWhere('trip IN (:trips)');
        $query->setParameter('trips', $trips);
        $query->orderBy('trip.id', 'ASC');
        $query->addOrderBy('stoptime.stopSequence', 'ASC');
        $query->groupBy('entity.id');
        return $query->getQuery()->getResult();
    }

    public function findByLatitudeLongitude(float $latitude, float $longitude, float $distanceLimit = 0, ?float &$distance = null): ?Stop
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
        if ($distanceLimit > 0) {
            $query->having('distance <= :distanceLimit')->setParameter('distanceLimit', $distanceLimit);
        }
        $query->setMaxResults(1);

        $result = $query->getQuery()->getOneOrNullResult();
        if (!empty($result)) {
            if($distance!==null){
                $distance = (float)$result["distance"];
            }
            return $result[0];
        }
        return null;
    }

    /*public function findByRoute(Route $route){
        //TODO This only could work if there is one trip and one stoptime between route and stops... if this changes, for example, with a different timeshift on weekend, this will fail
        $query = $this->createQueryBuilder('s');
        $query->innerJoin('s.stopTimes','st');
        $query->innerJoin('st.trip','t');
        $query->andWhere('t.route = :route')->setParameter('route', $route);
        $query->orderBy('st.stopSequence','ASC');
        return $query->getQuery()->getResult();
    }*/

    // /**
    //  * @return Stop[] Returns an array of Stop objects
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
    public function findOneBySomeField($value): ?Stop
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
