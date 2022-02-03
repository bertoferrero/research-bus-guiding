<?php

namespace App\EventListener;


use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\ServiceData\VehiclePosition;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Lib\Components\Notifications\NotificationManager;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class NotificationVehiclePositionSubscriber implements EventSubscriberInterface
{

    private static array $entitiesToNotify = [];

    public function __construct(protected NotificationManager $notificationManager)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::postPersist,
            Events::postFlush,
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof VehiclePosition) {
            return;
        }
        //Filter 2, only when changed status or stopid values
        if (!($args->hasChangedField('currentStatus') || $args->hasChangedField('schemaStopId'))) {
            return;
        }

        if ($entity->getCurrentStatus() != null && $entity->getschemaStopId() != null) {
            if (!isset(static::$entitiesToNotify[$entity->getId()])) {
                static::$entitiesToNotify[$entity->getId()] = $entity;
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof VehiclePosition) {
            return;
        }
        if ($entity->getCurrentStatus() != null && $entity->getschemaStopId() != null) {
            //Persist is the creation process, there is no changes on fields because all fields are just created
            if (!isset(static::$entitiesToNotify[$entity->getId()])) {
                static::$entitiesToNotify[$entity->getId()] = $entity;
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        //Block loop callbacks
        if (count(static::$entitiesToNotify) > 0) {
            $entitiesToNotify = static::$entitiesToNotify;
            static::$entitiesToNotify = [];
            foreach ($entitiesToNotify as $entity) {
                $this->notificationManager->sendVehiclePositionNotification($entity);
            }
        }
    }
}
