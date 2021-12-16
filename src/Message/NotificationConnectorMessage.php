<?php
namespace App\Message;

class NotificationConnectorMessage{

    public function __construct(private array $devices, private array $message)
    {
        
    }

    /**
     * Get the value of devices
     */ 
    public function getDevices(): array
    {
        return $this->devices;
    }

    /**
     * Set the value of devices
     *
     * @return  self
     */ 
    public function setDevices(array $devices)
    {
        $this->devices = $devices;

        return $this;
    }

    /**
     * Get the value of message
     */ 
    public function getMessage(): array
    {
        return $this->message;
    }

    /**
     * Set the value of message
     *
     * @return  self
     */ 
    public function setMessage(array $message)
    {
        $this->message = $message;

        return $this;
    }
}