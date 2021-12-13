<?php
namespace App\Lib\Components\Notifications;

use App\Lib\Components\Notifications\Connectors\FCMConnector;
use App\Lib\Components\Notifications\Connectors\NotificationConnectorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NotificationConnectorFactory{
    public function __construct(protected ParameterBagInterface $params)
    {
        
    }

    public function getNotificationConnector(): ?NotificationConnectorInterface{
        $connector = $this->params->get('app.component.notifications.connector');
        return match($connector){
            'FCM' => new FCMConnector(),
            default => null  
        };
    }
}