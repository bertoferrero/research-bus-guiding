<?php

namespace App\Lib\Components\Notifications\Messages;

use App\Entity\ServiceData\Trip;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\Notifications\Messages\VehiclePositionMessage;

class NotificationMessageFactory
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }



    public function composeVehiclePositionMessage(VehiclePosition $vehiclePosition): VehiclePositionMessage
    {
        $routeSchemaId = $vehiclePosition->getSchemaRouteId();
        $message = new VehiclePositionMessage();
        $message->setVehicleId($vehiclePosition->getschemaVehicleId());
        $message->setStatus($vehiclePosition->getCurrentStatus());
        $message->setStopId($vehiclePosition->getschemaStopId());
        if(!empty($routeSchemaId)){
            $message->setLineId($routeSchemaId);
        }

        return $message;
    }

    public function composeStopRequestMessage(VehiclePosition $vehiclePosition): StopRequestMessage
    {
        $routeSchemaId = $vehiclePosition->getSchemaRouteId();
        $message = new StopRequestMessage();
        $message->setVehicleId($vehiclePosition->getschemaVehicleId());
        $message->setStatus($vehiclePosition->getCurrentStatus());
        $message->setStopId($vehiclePosition->getschemaStopId());
        if(!empty($routeSchemaId)){
            $message->setLineId($routeSchemaId);
        }

        return $message;
    }

    public function composeDismissStopRequestMessage(VehiclePosition $vehiclePosition): DismissStopRequestMessage
    {
        $routeSchemaId = $vehiclePosition->getSchemaRouteId();
        $message = new DismissStopRequestMessage();
        $message->setVehicleId($vehiclePosition->getschemaVehicleId());
        $message->setStatus($vehiclePosition->getCurrentStatus());
        $message->setStopId($vehiclePosition->getschemaStopId());
        if(!empty($routeSchemaId)){
            $message->setLineId($routeSchemaId);
        }

        return $message;
    }
}
