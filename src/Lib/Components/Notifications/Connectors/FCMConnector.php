<?php

namespace App\Lib\Components\Notifications\Connectors;

class FCMConnector implements NotificationConnectorInterface
{
    public function sendMessage(array $devices, array $message)
    {
        //TODO include messaging

        //Devices are splited into subarrayes of 400 devices
        $subDevices = array_chunk($devices, 400);
        foreach($subDevices as $devicesSet){
            $this->sendMessageAsync($devicesSet, $message);
        }

    }
    public function sendMessageAsync(array $devices, array $message)
    {
        //https://firebase.google.com/docs/cloud-messaging/server#xmpp-request
        $body = [
            "to" => $devices,
            "message_id" => microtime(true),
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 60 //1 minute
        ];

        
    }
}
