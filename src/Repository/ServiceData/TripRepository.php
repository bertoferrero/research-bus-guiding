<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\Route;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Trip|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trip|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trip[]    findAll()
 * @method Trip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    public function findBySchemaId(string $schemaId){
        $query = $this->createQueryBuilder('entity');
        $query->andWhere('entity.schemaId = :schemaid')->setParameter('schemaid', $schemaId);
        return $query->getQuery()->getOneOrNullResult();
    }

    public function findByRouteAndWorkingDate(Route $route, \DateTime $workingDate):array{
        //Get today information
       // $timeNow = $workingDate;
        //$today = clone ($timeNow);
        //$today->setTime(0, 0);
        $weekDay = $this->getWeekDay($workingDate);

        //In many database configuration, the direct using of datetime in dql generates a non valid sql query
        $todayText = $workingDate->format('Y-m-d');
        $nowText = $workingDate->format('H:i:s');

        $query = $this->createQueryBuilder('t');
        $query->innerJoin('t.calendar', 'c');
        $query->innerJoin('c.calendarPlan', 'cp');
        $query->leftJoin('c.calendarDates', 'cd', Join::WITH, 'cd.date = :today');
        $query->andwhere('cd IS NULL OR cd.isRemovingDate = false'); //Filter for avoiding removed dates        
        $query->andWhere('t.route = :route AND t.hourStart IS NOT NULL AND t.hourEnd IS NOT NULL AND t.hourStart <= :timenow AND t.hourEnd >= :timenow'); //Filters about routes and working hours
        //Filter forcing added dates on calendardates table or getting working days from calendarplan
        $query->andwhere('
            ( cd IS NOT NULL AND cd.isRemovingDate = false) 
            OR 
            (cp.startDate <= :today AND cp.endDate >= :today AND cp.' . $weekDay . ' = true) 
            ');
        $query->setParameter('route', $route)->setParameter('today', $todayText)->setParameter('timenow', $nowText);

        $query->groupBy('t.id');

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
    //  * @return Trip[] Returns an array of Trip objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trip
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
