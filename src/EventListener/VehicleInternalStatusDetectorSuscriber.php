<?php

namespace App\EventListener;


use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\ServiceData\VehiclePosition;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Lib\Components\ServiceData\VehicleStatusDetector;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VehicleInternalStatusDetectorSuscriber implements EventSubscriberInterface
{


    public function __construct(protected ParameterBagInterface $params, protected VehicleStatusDetector $locator)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof VehiclePosition) {
            return;
        }
        //Filter 2, only when changed status or stopid values
        if (!($args->hasChangedField('latitude') || $args->hasChangedField('longitude'))) {
            return;
        }

        $entity = $this->doCommonWork($entity, $args);
        if ($entity != null) {
            $em = $args->getEntityManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof VehiclePosition) {
            return;
        }

        $this->doCommonWork($entity, $args);
    }

    protected function doCommonWork(VehiclePosition $entity, LifecycleEventArgs $args): ?VehiclePosition
    {
        $locationMode = (int)$this->params->get('app.component.servicedatasync.vehicle_location_mode');
        if ($locationMode == 0) {
            return null;
        }

        $entity = $this->locator->detectVehicleStopAndStatus($entity, false);

        return $entity;
    }
}
