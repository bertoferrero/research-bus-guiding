<?php

namespace App\Repository\ServiceData;

use App\Entity\ServiceData\Route;
use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\StopTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findBySchemaId(string $schemaId){
        $query = $this->createQueryBuilder('entity');
        $query->andWhere('entity.schemaId = :schemaid')->setParameter('schemaid', $schemaId);
        return $query->getQuery()->getOneOrNullResult();
    }

    public function findByRoute(Route $route){
        //TODO This only could work if there is one trip and one stoptime between route and stops... if this changes, for example, with a different timeshift on weekend, this will fail
        $query = $this->createQueryBuilder('s');
        $query->innerJoin('s.stopTimes','st');
        $query->innerJoin('st.trip','t');
        $query->andWhere('t.route = :route')->setParameter('route', $route);
        $query->orderBy('st.stopSequence','ASC');
        return $query->getQuery()->getResult();
    }

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
