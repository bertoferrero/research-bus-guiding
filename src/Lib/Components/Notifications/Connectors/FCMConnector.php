<?php

namespace App\Lib\Components\Notifications\Connectors;

use App\Message\NotificationConnectorMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class FCMConnector implements NotificationConnectorInterface
{
    public function __construct(protected string $project, protected MessageBusInterface $bus)
    {
    }


    public function sendMessageAsync(array $devices, array $message)
    {
        //TODO include messaging
        //Why topics cannot be use into this project https://firebase.google.com/docs/cloud-messaging/android/topic-messaging
        //Curl implementation does not allow multitoken sending... ¬¬

        //Devices are splited into subarrayes of 400 devices
        //
        /*$subDevices = array_chunk($devices, 400);
        foreach ($subDevices as $devicesSet) {
            $this->sendMessageAsync($devicesSet, $message);
        }*/
        foreach ($devices as $device) {
            $this->bus->dispatch(new NotificationConnectorMessage([$device], $message));
            //$this->sendMessage([$device], $message);
        }
    }
    public function sendMessage(array $devices, array $message)
    {
        $message = array_map('strval', $message);
        //https://firebase.google.com/docs/cloud-messaging/server#xmpp-request
        /*$body = [
            "to" => $devices,
            "message_id" => microtime(true),
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 60 //1 minute
        ];
*/
        //https://firebase.google.com/docs/cloud-messaging/send-message#send-messages-to-multiple-devices
        //https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
        //Messages in boot mode https://firebase.google.com/docs/cloud-messaging/android/receive#receive_fcm_messages_in_direct_boot_mode
        /*$body = [
            "tokens" => $devices,
            "data" => $message,
            "time_to_live" => 60 //1 minute
        ];*/

        //https://firebase.google.com/docs/cloud-messaging/migrate-v1

        $body = [
            "message" => [
                "token" => $devices[0],
                "data" => $message,
                "android" => [
                    "direct_boot_ok" => true,
                ],

            ]
        ];

        //https://gist.github.com/Repox/64ac4b3582f8ac42a6a1b41667db7440
        //https://github.com/googleapis/google-api-php-client
        $client = new \Google\Client();
        $client->setAuthConfig(__DIR__ . '/fcmauth.json');
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $httpClient = $client->authorize();
        $response = $httpClient->post("https://fcm.googleapis.com/v1/projects/{$this->project}/messages:send", ['json' => $body]);
        //$content = $response->getContent();
        $body = (string)$response->getBody();
    }
}
