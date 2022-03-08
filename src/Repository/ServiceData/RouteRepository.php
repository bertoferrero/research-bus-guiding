<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Route|null find($id, $lockMode = null, $lockVersion = null)
 * @method Route|null findOneBy(array $criteria, array $orderBy = null)
 * @method Route[]    findAll()
 * @method Route[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Route::class);
    }

    public function findBySchemaId(string $schemaId){
        $query = $this->createQueryBuilder('entity');
        $query->andWhere('entity.schemaId = :schemaid')->setParameter('schemaid', $schemaId);
        return $query->getQuery()->getOneOrNullResult();
    }

    protected function findByStopQB(Stop $stop): QueryBuilder
    {
        $query = $this->createQueryBuilder('entity');
        $query->innerJoin('entity.trips', 'trips');
        $query->innerJoin('trips.stopTimes', 'stoptimes');
        $query->innerJoin('stoptimes.stop', 'stop', Join::WITH, 'stop = :stop')->setParameter('stop', $stop);
        $query->select('DISTINCT(entity)');
        return $query;
    }

    public function findByStop(Stop $stop){
        return $this->findByStopQB($stop)->getQuery()->getResult();
    }

    /**
     * Checks if there is a connection between the route and the stop
     *
     * @param Route $route
     * @param Stop $stop
     * @return boolean
     */
    public function checkRouteAndStop(Route $route, Stop $stop):bool{
        $query = $this->findByStopQB($stop);
        $query->andWhere('entity = :route')->setParameter('route', $route);
        $query->select('count(entity.id)');
        $count = $query->getQuery()->getSingleScalarResult();
        return $count > 0;
    }

    public function findByStopAndWorkingDate(Stop $stop, \DateTime $workingDate):array{
        //Get today information
       // $timeNow = $workingDate;
        //$today = clone ($timeNow);
        //$today->setTime(0, 0);
        $weekDay = $this->getWeekDay($workingDate);

        //In many database configuration, the direct using of datetime in dql generates a non valid sql query
        $todayText = $workingDate->format('Y-m-d');
        $nowText = $workingDate->format('H:i:s');

        $query = $this->createQueryBuilder('r');
        $query->innerJoin('r.trips','t');
        $query->innerJoin('t.calendar', 'c');
        $query->innerJoin('c.calendarPlan', 'cp');
        $query->leftJoin('c.calendarDates', 'cd', Join::WITH, 'cd.date = :today');
        $query->innerJoin('t.shape', 'shape');
        $query->innerJoin('shape.shapePoints','shapepoints',Join::WITH, 'shapepoints.stop = :stop');
        $query->andwhere('cd IS NULL OR cd.isRemovingDate = false'); //Filter for avoiding removed dates        
        $query->andWhere('t.hourStart IS NOT NULL AND t.hourEnd IS NOT NULL AND t.hourStart <= :timenow AND t.hourEnd >= :timenow'); //Filters about routes and working hours
        //Filter forcing added dates on calendardates table or getting working days from calendarplan
        $query->andwhere('
            ( cd IS NOT NULL AND cd.isRemovingDate = false) 
            OR 
            (cp.startDate <= :today AND cp.endDate >= :today AND cp.' . $weekDay . ' = true) 
            ');
        $query->setParameter('stop', $stop)->setParameter('today', $todayText)->setParameter('timenow', $nowText);

        $query->groupBy('r.id');
        $query->orderBy('r.name', 'ASC');

        $trips = $query->getQuery()->getResult();

        return $trips;
    }

    protected function getWeekDay(\DateTime $date): string
    {
        $weekDay = (int)$date->format('N');
        return match ($weekDay) {
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday'
        };
    }

    // /**
    //  * @return Route[] Returns an array of Route objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Route
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
