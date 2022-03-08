<?php

namespace App\Lib\Components\Notifications;

use App\Entity\ServiceData\Trip;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\Notifications\TopicResolver;
use App\Lib\Components\Notifications\Connectors\NotificationConnectorFactory;
use App\Lib\Components\Notifications\Messages\NotificationMessageFactory;
use App\Lib\Enum\VehiclePositionStatusEnum;

class NotificationManager
{
    public function __construct(protected TopicResolver $topicResolver, protected NotificationConnectorFactory $connectorFactory, protected NotificationMessageFactory $notificationMessageFactory)
    {
    }

    /**
     * Sends the vehicle position notification related to topics generated from VehiclePosition entity
     *
     * @param VehiclePosition $entity
     * @return void
     */
    public function sendVehiclePositionNotification(VehiclePosition $entity, bool $changeStopId = false)
    {
        if ($entity->getCurrentStatus() === null || $entity->getschemaStopId() === null) {
            return;
        }

        //If a change stop ID has been detected, we force an in transit to message
        if($changeStopId && $entity->getCurrentStatus() != VehiclePositionStatusEnum::IN_TRANSIT_TO){
            $originalStatus = $entity->getCurrentStatus();
            $entity->setCurrentStatus(VehiclePositionStatusEnum::IN_TRANSIT_TO);
            $this->sendVehiclePositionNotification($entity);
            $entity->setCurrentStatus(($originalStatus));
        }

        //Lets get the notification topics and the devices tokens
        $topics = $this->topicResolver->getTopicsForVehiclePosition($entity);
        $tokens = $this->topicResolver->retrieveNotificationTokens($topics);

        //If there are tokens for this notification, prepare the message and send all
        if (!empty($tokens)) {
            $message = $this->notificationMessageFactory->composeVehiclePositionMessage($entity);
            $notificationConnector = $this->connectorFactory->getNotificationConnector();
            $notificationConnector->sendMessageAsync($tokens, $message->toArray());
        }
    }

    /**
     * Sends the stop notification to drivers related to VehiclePosition entity
     *
     * @param VehiclePosition $entity
     * @return void
     */
    public function sendStopNotification(VehiclePosition $entity)
    {
        $drivertoken = $entity->getDriver()?->getNotificationDeviceToken();
        if ($drivertoken == null) {
            return;
        }

        //Compose the message and send the notification
        $message = $this->notificationMessageFactory->composeStopRequestMessage($entity);
        $notificationConnector = $this->connectorFactory->getNotificationConnector();
        $notificationConnector->sendMessageAsync([$drivertoken], $message->toArray());
    }

    /**
     * Sends the dismiss stop notification to drivers related to VehiclePosition entity
     *
     * @param VehiclePosition $entity
     * @return void
     */
    public function sendDismissStopNotification(VehiclePosition $entity)
    {
        $drivertoken = $entity->getDriver()?->getNotificationDeviceToken();
        if ($drivertoken == null) {
            return;
        }

        //Compose the message and send the notification
        $message = $this->notificationMessageFactory->composeDismissStopRequestMessage($entity);
        $notificationConnector = $this->connectorFactory->getNotificationConnector();
        $notificationConnector->sendMessageAsync([$drivertoken], $message->toArray());
    }
}
