<?php
namespace App\Lib\Components\ServiceData;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Lib\Components\ServiceData\GTFS\GtfsStaticSynchronizer;
use App\Lib\Components\ServiceData\GTFS\GtfsRTVehiclePositionSynchronizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ServiceDataSynchronizerFactory{
    
    public function __construct(protected EntityManagerInterface $em, protected ParameterBagInterface $params, protected LoggerInterface $logger, protected MessageBusInterface $bus)
    {
        
    }

    public function getStaticSynchronizer(){
        $synchronizer = $this->params->get('app.component.servicedatasync.static');
        return match($synchronizer){
            'GTFS' => new GtfsStaticSynchronizer($this->em, $this->params, $this->logger, $this->bus),
            default => null
        };
    }

    public function getVehiclePositionSynchronizer(){
        $synchronizer = $this->params->get('app.component.servicedatasync.vehicleposition');
        return match($synchronizer){
            'GTFS' => new GtfsRTVehiclePositionSynchronizer($this->em, $this->params, $this->logger, $this->bus),
            default => null
        };
    }

}