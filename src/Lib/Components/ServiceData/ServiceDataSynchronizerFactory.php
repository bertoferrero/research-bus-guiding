<?php
namespace App\Lib\Components\ServiceData;

use App\Lib\Components\ServiceData\GTFS\GtfsRTVehiclePositionSynchronizer;
use App\Lib\Components\ServiceData\GTFS\GtfsStaticSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ServiceDataSynchronizerFactory{
    
    public function __construct(protected EntityManagerInterface $em, protected ParameterBagInterface $params)
    {
        
    }

    public function getStaticSynchronizer(){
        $synchronizer = $this->params->get('app.component.servicedatasync.static');
        return match($synchronizer){
            'GTFS' => new GtfsStaticSynchronizer($this->em, $this->params),
            default => null
        };
    }

    public function getVehiclePositionSynchronizer(){
        $synchronizer = $this->params->get('app.component.servicedatasync.vehicleposition');
        return match($synchronizer){
            'GTFS' => new GtfsRTVehiclePositionSynchronizer($this->em, $this->params),
            default => null
        };
    }

}