<?php
namespace App\MessageHandler;

use App\Lib\Components\Notifications\Connectors\NotificationConnectorFactory;
use App\Message\NotificationConnectorMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotificationConnectorHandler implements MessageHandlerInterface{

    public function __construct(protected NotificationConnectorFactory $notificationConnectorFactory)
    {
        
    }
    
    public function __invoke(NotificationConnectorMessage $message)
    {
        //Load the connector
        $connector = $this->notificationConnectorFactory->getNotificationConnector();
        //Launch the message
        $connector->sendMessage($message->getDevices(), $message->getMessage());
    }

}