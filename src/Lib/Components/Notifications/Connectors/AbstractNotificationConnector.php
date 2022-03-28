<?php
namespace App\Lib\Components\Notifications\Connectors;

use App\Entity\NotificationLog;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\NotificationConnectorMessage;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractNotificationConnector{

    
    public function __construct(protected string $project, protected MessageBusInterface $bus, protected EntityManagerInterface $em)
    {
    }
    
    public function sendMessageAsync(array $devices, array $message){
        foreach ($devices as $device) {
            /*$this->sendMessage($device, $message);
            continue;*/
            $this->bus->dispatch(new NotificationConnectorMessage($device, $message));
        }
    }

    public function sendMessage(string $device, array $message){
                //Insert the notification outcoming log
                $utcTime = \DateTime::createFromFormat('U.u', (string)microtime(true), new \DateTimeZone("UTC"));
                $notificationLog = new NotificationLog();
                $notificationLog->setDateSend($utcTime);
                $notificationLog->setDeviceToken($device);
                $notificationLog->setMessage($message);
                $this->em->persist($notificationLog);
                $this->em->flush();
                //Store the just created log id
                $message['log_id'] = $notificationLog->getId();
                //Send the message
                $this->executeMessageSending($device, $message);
    }

    abstract protected function executeMessageSending(string $device, array $message);
}