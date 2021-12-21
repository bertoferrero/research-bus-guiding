<?php

namespace App\Controller\Cron;

use App\Lib\Components\ServiceData\ServiceDataSynchronizerFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
