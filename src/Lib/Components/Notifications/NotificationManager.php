<?php

namespace App\Lib\Components\Notifications;

use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\Notifications\Connectors\NotificationConnectorInterface;
use TopicResolver;

class NotificationManager
{
    public function __construct(protected TopicResolver $topicResolver, protected NotificationConnectorFactory $connectorFactory)
    {
        
    }

    public function sendVehiclePositionNotification(VehiclePosition $entity)
    {
        if ($entity->getCurrentStatus() === null || $entity->getschemaStopId() === null) {
            return;
        }

        //Lets get the notification topics and the devices tokens
        $topics = $this->topicResolver->getTopicsForVehiclePosition($entity);
        $tokens = $this->topicResolver->retrieveNotificationTokens($topics);

        //If there are tokens for this notification, prepare the message and send all
        $notificationConnector = $this->connectorFactory->getNotificationConnector();
        if(!empty($tokens)){
            $notificationConnector->sendMessage($tokens, ["TODO"]);
        }
    }
}
