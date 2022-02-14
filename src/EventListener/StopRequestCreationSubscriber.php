<?php

namespace App\EventListener;

use App\Entity\ServiceData\VehiclePosition;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostFlushEventArgs;
use App\Entity\StopRequest;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Lib\Components\StopRequestManagement\VehicleStopNotificator;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class StopRequestCreationSubscriber implements EventSubscriberInterface
{

    private static array $entitiesToNotify = [];

    public function __construct(protected VehicleStopNotificator $vehicleStopNotificator)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postFlush,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof StopRequest) {
            return;
        }

        if (!isset(static::$entitiesToNotify[$entity->getId()])) {
            static::$entitiesToNotify[$entity->getId()] = $entity;
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        //Block loop callbacks
        if (count(static::$entitiesToNotify) > 0) {
            $entitiesToNotify = static::$entitiesToNotify;
            static::$entitiesToNotify = [];
            foreach ($entitiesToNotify as $entity) {
                $this->vehicleStopNotificator->sendNotificationFromRequestEntity($entity);
            }
        }
    }
}
