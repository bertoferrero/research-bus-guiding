<?php

namespace App\Lib\Components\Notifications\Messages;



class DismissStopRequestMessage extends AbstractNotificationMessage
{
    protected $vehicleId;
    protected $lineId;
    protected $status;
    protected $stopId;

    protected function getMessageType(): string{
        return "DismissStopRequest";
    }

    protected function toArrayParams(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'line_id' => $this->lineId,
            'status' => $this->status,
            'stop_id' => $this->stopId
        ];
    }

    /**
     * Get the value of vehicleId
     */ 
    public function getVehicleId()
    {
        return $this->vehicleId;
    }

    /**
     * Set the value of vehicleId
     *
     * @return  self
     */ 
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;

        return $this;
    }

    /**
     * Get the value of lineId
     */ 
    public function getLineId()
    {
        return $this->lineId;
    }

    /**
     * Set the value of lineId
     *
     * @return  self
     */ 
    public function setLineId($lineId)
    {
        $this->lineId = $lineId;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of stopId
     */ 
    public function getStopId()
    {
        return $this->stopId;
    }

    /**
     * Set the value of stopId
     *
     * @return  self
     */ 
    public function setStopId($stopId)
    {
        $this->stopId = $stopId;

        return $this;
    }
}
