<?php
namespace App\EventListener;


use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\Notifications\NotificationManager;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\PreUpdateEventArgs;

class VehicleStatusStopUpdateSubscriber implements EventSubscriberInterface
{

    public function __construct(protected NotificationManager $notificationManager)
    {
        
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::postPersist
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

        $this->notificationManager->sendVehiclePositionNotification($entity);
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof VehiclePosition) {
            return;
        }
        //Persist is the creation process, there is no changes on fields because all fields are just created
        $this->notificationManager->sendVehiclePositionNotification($entity);
    }
}
