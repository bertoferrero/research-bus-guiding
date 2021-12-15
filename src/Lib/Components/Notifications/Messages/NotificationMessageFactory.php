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
        $trip = $this->em->getRepository(Trip::class)->findOneBy(['schemaId' => $vehiclePosition->getschemaTripId()]);
        $message = new VehiclePositionMessage();
        $message->setVehicleId($vehiclePosition->getschemaVehicleId());
        $message->setStatus($vehiclePosition->getCurrentStatus());
        $message->setStopId($vehiclePosition->getschemaStopId());
        if($trip!=null){
            $message->setLineId($trip->getRoute()->getSchemaId());
        }

        return $message;
    }
}
