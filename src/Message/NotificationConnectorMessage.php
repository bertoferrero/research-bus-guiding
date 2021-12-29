<?php
namespace App\Message;

class NotificationConnectorMessage{

    public function __construct(private string $device, private array $message)
    {
        
    }

    /**
     * Get the value of devices
     */ 
    public function getDevice(): string
    {
        return $this->device;
    }

    /**
     * Set the value of devices
     *
     * @return  self
     */ 
    public function setDevice(string $device)
    {
        $this->device = $device;

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