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
        if ($status == VehiclePositionStatusEnum::STOPPED_AT) {
            return;
        }
        $this->userStopRequestsManager->invalidateOldRequests();

        $stopId = $entity->getschemaStopId();
        $vehicleId = $entity->getschemaVehicleId();
        $lineId = null;

        //Get vehicle line
        $lineId = $entity->getRoute()?->getSchemaId();

        //Search for requested stops
        $stops = $this->em->getRepository(StopRequest::class)->findPending($stopId, $vehicleId, $lineId);

        //Search for already sent stop signal and required now to dismiss the alert (stop changed)
        $inProgressStops = $this->em->getRepository(StopRequest::class)->findInProgressStopChanged($stopId, $vehicleId);

        if (count($stops) != 0) {
            //Set all stops as complete
            foreach ($stops as $stop) {
                $stop->setStatus(StopRequestStatusEnum::IN_PROGRESS);
                $stop->setDesignatedSchemaVehicleId($entity->getschemaVehicleId());
                $this->em->persist($stop);
            }
            $this->em->flush();

            //Send the notification 
            $this->notificationManager->sendStopNotification($entity);
        }

        if (count($inProgressStops) != 0) {
            //Set all stops as complete
            foreach ($inProgressStops as $stop) {
                $stop->setStatus(StopRequestStatusEnum::PROCESSED);
                $this->em->persist($stop);
            }
            $this->em->flush();

            //Send the notification only if no prev stop notificatino has been sent
            if (count($stops) == 0) {
                $this->notificationManager->sendDismissStopNotification($entity);
            }
        }
    }

    /**
     * Checks if there is a vehicle in transit to the stop requested.
     *
     * @param StopRequest $entity
     * @return void
     */
    public function sendNotificationFromRequestEntity(StopRequest $entity)
    {
        $vehicleRepo = $this->em->getRepository(VehiclePosition::class);
        //Check if there is a vehicle which the requested stop as objective
        if ($entity->GetSchemaVehicleId()) {
            $vehicle = $vehicleRepo->findOneBy(['schemaVehicleId' => $entity->getSchemaVehicleId(), 'schemaStopId' => $entity->getSchemaStopId(), 'currentStatus' => [VehiclePositionStatusEnum::IN_TRANSIT_TO, VehiclePositionStatusEnum::INCOMING_AT]]);
        } else {
            $vehicle = $vehicleRepo->findOneBy(['schemaRouteId' => $entity->getSchemaRouteId(), 'schemaStopId' => $entity->getSchemaStopId(), 'currentStatus' => [VehiclePositionStatusEnum::IN_TRANSIT_TO, VehiclePositionStatusEnum::INCOMING_AT]]);
        }
        if ($vehicle == null) {
            return;
        }

        //Set the request as completed and send the notification
        $entity->setStatus(StopRequestStatusEnum::IN_PROGRESS);
        $entity->setDesignatedSchemaVehicleId($vehicle->getschemaVehicleId());
        $this->em->persist($entity);
        $this->em->flush();

        $this->notificationManager->sendStopNotification($vehicle);
    }
}
