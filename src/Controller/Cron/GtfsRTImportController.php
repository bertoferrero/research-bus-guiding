<?php

namespace App\Controller\Cron;

use DateTime;
use App\Entity\Gtfs\Stop;
use App\Entity\Gtfs\Trip;
use App\Entity\Gtfs\StopTime;
use Trafiklab\Gtfs\Model\GtfsArchive;
use App\Entity\Gtfs\Route as GtfsRoute;
use App\Entity\GtfsRT\VehiclePosition;
use App\Lib\Enum\VehiclePositionStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Google\Transit\Realtime\FeedMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GtfsRTImportController extends AbstractController
{
    /**
     * @Route("/cron/gtfs/rt/vehicleposition", name="cron_gtfs_rt_vehicleposition")
     */
    public function index(ParameterBagInterface $params, EntityManagerInterface $em): Response
    {
        //https://github.com/trafiklab/gtfs-php-sdk
        $feedUrl = $params->get('app.gtfs.rt.url');

        $pbfContent = file_get_contents($feedUrl);
        $feedMessage = new FeedMessage();
        $feedMessage->mergeFromString($pbfContent);

        //Recorremos cada elemento
        $entities = $feedMessage->getEntity();
        $vehiclePositionRepo = $em->getRepository(VehiclePosition::class);
        $workingVehicles = [];
        foreach ($entities as $entity) {
            //Recogemos el vehicle y procesamos la entidad
            $vehicle = $entity->getVehicle();
            $vehicleId = $vehicle->getVehicle()->getId();
            $vehicleEntity = $vehiclePositionRepo->findOneBy(['gtfsVehicleId' => $vehicleId]);
            if ($vehicleEntity == null) {
                $vehicleEntity = new VehiclePosition();
                $vehicleEntity->setGtfsVehicleId($vehicleId);
            }
            $vehicleEntity->setLatitude((float)$vehicle->getPosition()->getLatitude());
            $vehicleEntity->setLongitude((float)$vehicle->getPosition()->getLongitude());
            $vehicleEntity->setGtfsTripId($vehicle->getTrip()->getTripId());
            $vehicleEntity->setGtfsStopId($vehicle->getStopId());
            $vehicleEntity->setCurrentStatus($this->transformCurrentStatus($vehicle->getCurrentStatus()));
            $em->persist($vehicleEntity);
            $workingVehicles[] = $vehicleId;
        }
        $em->flush();

        //Borramos los vehiculos que ya no están en funcionamiento, por limpieza
        $query = $em->createQueryBuilder();
        $query->delete(VehiclePosition::class, 'v')->andWhere('v.gtfsVehicleId NOT IN (:vehicles)')->setParameter('vehicles', $workingVehicles)->getQuery()->execute();


        return new Response('ok');
    }

    protected function transformCurrentStatus(int $currentStatus): string
    {
        return match ($currentStatus) {
            0 => VehiclePositionStatusEnum::INCOMING_AT,
            1 => VehiclePositionStatusEnum::STOPPED_AT,
            2 => VehiclePositionStatusEnum::IN_TRANSIT_TO
        };
    }
}
