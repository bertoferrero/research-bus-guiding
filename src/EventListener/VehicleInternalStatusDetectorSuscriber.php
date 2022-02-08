<?php

namespace App\EventListener;


use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\ServiceData\VehicleStatusDetector;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VehicleInternalStatusDetectorSuscriber implements EventSubscriberInterface
{

    private static array $entitiesToProcess = [];

    public function __construct(protected ParameterBagInterface $params, protected VehicleStatusDetector $locator)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::prePersist,
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
        if (!($args->hasChangedField('latitude') || $args->hasChangedField('longitude'))) {
            return;
        }

        $entity = $this->doCommonWork($entity);
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof VehiclePosition) {
            return;
        }

        $this->doCommonWork($entity);
    }

    protected function doCommonWork(VehiclePosition $entity): void
    {
        $locationMode = (int)$this->params->get('app.component.servicedatasync.vehicle_location_mode');
        if ($locationMode == 0) {
            return;
        }

        static::$entitiesToProcess[$entity->getId()] = $entity;
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        //Block loop callbacks
        if (count(static::$entitiesToProcess) > 0) {
            $entitiesToProcess = static::$entitiesToProcess;
            static::$entitiesToProcess = [];
            $em = $args->getEntityManager();
            foreach ($entitiesToProcess as $entity) {
                $entity = $this->locator->detectVehicleStopAndStatus($entity, true);
            }
            $em->flush();
        }
    }
}
