<?php

namespace App\Repository;

use App\Entity\UserNotificationTopicSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserNotificationTopicSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserNotificationTopicSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserNotificationTopicSubscription[]    findAll()
 * @method UserNotificationTopicSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserNotificationTopicSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNotificationTopicSubscription::class);
    }

    // /**
    //  * @return UserNotificationTopicSubscription[] Returns an array of UserNotificationTopicSubscription objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserNotificationTopicSubscription
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
