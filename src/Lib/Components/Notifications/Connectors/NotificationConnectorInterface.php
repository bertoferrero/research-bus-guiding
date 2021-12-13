<?php
namespace App\Lib\Components\Notifications\Connectors;

interface NotificationConnectorInterface{
    public function sendMessage(array $devices, array $message);
    public function sendMessageAsync(array $devices, array $message);
}