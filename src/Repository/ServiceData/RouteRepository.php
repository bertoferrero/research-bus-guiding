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
