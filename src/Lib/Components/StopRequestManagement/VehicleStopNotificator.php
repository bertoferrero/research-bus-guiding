<?php

namespace App\Lib\Components\StopRequestManagement;

use App\Entity\StopRequest;
use App\Entity\ServiceData\Trip;
use App\Lib\Enum\StopRequestStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Enum\VehiclePositionStatusEnum;
use App\Lib\Components\Notifications\NotificationManager;

class VehicleStopNotificator
{
    public function __construct(protected EntityManagerInterface $em, protected UserStopRequestsManager $userStopRequestsManager, protected NotificationManager $notificationManager)
    {
        
    }

    public function sendVehicleStopNotification(VehiclePosition $entity)
    {
        if ($entity->getCurrentStatus() === null || $entity->getschemaStopId() === null) {
            return;
        }

        $status = $entity->getCurrentStatus();
        if ($status != VehiclePositionStatusEnum::IN_TRANSIT_TO) {
            return;
        }
        $this->userStopRequestsManager->invalidateOldRequests();

        $stopId = $entity->getschemaStopId();
        $vehicleId = $entity->getschemaVehicleId();
        $lineId = null;

        //Get vehicle line
        $trip = $this->em->getRepository(Trip::class)->findOneBy(['schemaId' => $entity->getschemaTripId()]);
        $lineId = $trip?->getRoute()?->getSchemaId();

        //Search for requested stops
        $stops = $this->em->getRepository(StopRequest::class)->findPending($stopId, $vehicleId, $lineId);
        if(count($stops) == 0){
            return;
        }

        //Set all stops as complete
        foreach($stops as $stop){
            $stop->setStatus(StopRequestStatusEnum::PROCESSED);
            $this->em->persist($stop);
        }
        $this->em->flush();

        //Send the notification 
        $this->notificationManager->sendStopNotification($entity);
    }
}
