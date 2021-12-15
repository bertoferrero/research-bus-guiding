<?php

namespace App\Lib\Components\Notifications;

use App\Entity\ServiceData\Trip;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\Notifications\TopicResolver;
use App\Lib\Components\Notifications\Connectors\NotificationConnectorFactory;
use App\Lib\Components\Notifications\Messages\NotificationMessageFactory;

class NotificationManager
{
    public function __construct(protected TopicResolver $topicResolver, protected NotificationConnectorFactory $connectorFactory, protected NotificationMessageFactory $notificationMessageFactory)
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
        $message = $this->notificationMessageFactory->composeVehiclePositionMessage($entity);
        $notificationConnector = $this->connectorFactory->getNotificationConnector();
        if(!empty($tokens)){
            $notificationConnector->sendMessage($tokens, $message->toArray());
        }
    }
}
