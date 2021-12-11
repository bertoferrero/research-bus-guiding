<?php

namespace App\Controller\Cron;

use DateTime;
use App\Entity\Gtfs\Stop;
use App\Entity\Gtfs\Trip;
use App\Entity\Gtfs\StopTime;
use Trafiklab\Gtfs\Model\GtfsArchive;
use App\Entity\Gtfs\Route as GtfsRoute;
use App\Entity\GtfsRT\VehiclePosition;
use App\Lib\Components\ServiceData\ServiceDataSynchronizerFactory;
use App\Lib\Enum\VehiclePositionStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Google\Transit\Realtime\FeedMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ServiceDataSyncController extends AbstractController
{
    /**
     * @Route("/cron/servicedatasync/static", name="cron_servicedatasync_static")
     */
    public function static(ServiceDataSynchronizerFactory $serviceDataFactory): Response
    {
        $synchronizer = $serviceDataFactory->getStaticSynchronizer();
        if($synchronizer != null){
            $synchronizer->executeSync();
        }
        
        return new Response('ok');
    }

    /**
     * @Route("/cron/servicedatasync/vehicleposition", name="cron_servicedatasync_vehicleposition")
     */
    public function vehicleposition(ServiceDataSynchronizerFactory $serviceDataFactory): Response
    {
        $synchronizer = $serviceDataFactory->getVehiclePositionSynchronizer();
        if($synchronizer != null){
            $synchronizer->executeSync();
        }
        
        return new Response('ok');
    }
}
