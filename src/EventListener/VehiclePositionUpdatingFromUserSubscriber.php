<?php

namespace App\EventListener;


use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\ServiceData\VehicleStatusDetector;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VehiclePositionUpdatingFromUserSubscriber implements EventSubscriberInterface
{

    private static array $entitiesToProcess = [];

    public function __construct(protected ParameterBagInterface $params)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::postFlush,
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        //Filter 1, only users updates
        $entity = $args->getObject();
        if (!$entity instanceof User) {
            return;
        }
        //Filter 2, only when changed desired values
        if (!($args->hasChangedField('driverLatitude') || $args->hasChangedField('driverLongitude'))) {
            return;
        }
        //Filter 3, only locationmode 2
        $locationMode = (int)$this->params->get('app.component.servicedatasync.vehicle_location_mode');
        if ($locationMode < 2) {
            return;
        }

        //
        //Check if all required components are not empty
        $latitude = $entity->getDriverLatitude();
        $longitude = $entity->getDriverLongitude();
        $route = $entity->getDriverRoute();
        if ($latitude == null || $longitude == null || $route == null) {
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
                $latitude = $entity->getDriverLatitude();
                $longitude = $entity->getDriverLongitude();
                $route = $entity->getDriverRoute();

                //Retrieve vehiclePosition entity
                $vehiclePosition = $entity->getVehiclePosition();
                if ($vehiclePosition == null) {
                    $vehicleId = $entity->getDriverVehicleId();
                    if ($vehicleId == null) {
                        return;
                    }
                    $vehiclePosition = $em->getRepository(VehiclePosition::class)->findOneBy(['schemaVehicleId' => $vehicleId]);
                    if ($vehiclePosition == null) {
                        $vehiclePosition = new VehiclePosition();
                        $vehiclePosition->setschemaVehicleId($vehicleId);
                    }
                }

                //Set data and persist
                $vehiclePosition->setLatitude($latitude);
                $vehiclePosition->setLongitude($longitude);
                $vehiclePosition->setRoute($route);
                $em->persist($vehiclePosition); //FIXME esto no funcionará aquí, utilizar onflush
            }
            $em->flush();
        }
    }
}
