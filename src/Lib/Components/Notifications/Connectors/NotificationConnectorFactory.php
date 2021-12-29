<?php
namespace App\Lib\Components\Notifications\Connectors;

use Symfony\Component\Messenger\MessageBusInterface;
use App\Lib\Components\Notifications\Connectors\FCMConnector;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;

class NotificationConnectorFactory{
    public function __construct(protected ParameterBagInterface $params, protected MessageBusInterface $bus, protected EntityManagerInterface $em)
    {
        
    }

    public function getNotificationConnector(){
        $connector = $this->params->get('app.component.notifications.connector');
        return match($connector){
            'FCM' => new FCMConnector($this->params->get('app.FCM.project'), $this->bus, $this->em),
            default => null  
        };
    }
}