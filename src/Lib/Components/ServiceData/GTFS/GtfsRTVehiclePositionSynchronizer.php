<?php
namespace App\Lib\Components\ServiceData\GTFS;


use App\Entity\GtfsRT\VehiclePosition;
use Google\Transit\Realtime\FeedMessage;
use App\Lib\Enum\VehiclePositionStatusEnum;
use App\Lib\Components\ServiceData\AbstractServiceDataSynchronizer;


class GtfsRTVehiclePositionSynchronizer extends AbstractServiceDataSynchronizer
{
    public function executeSync()
    {

        //https://github.com/trafiklab/gtfs-php-sdk
        $feedUrl = $this->params->get('app.gtfs.rt.url');

        $pbfContent = file_get_contents($feedUrl);
        $feedMessage = new FeedMessage();
        $feedMessage->mergeFromString($pbfContent);

        //Recorremos cada elemento
        $entities = $feedMessage->getEntity();
        $vehiclePositionRepo = $this->em->getRepository(VehiclePosition::class);
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
            $this->em->persist($vehicleEntity);
            $workingVehicles[] = $vehicleId;
        }
        $this->em->flush();

        //Borramos los vehiculos que ya no están en funcionamiento, por limpieza
        $query = $this->em->createQueryBuilder();
        $query->delete(VehiclePosition::class, 'v')->andWhere('v.gtfsVehicleId NOT IN (:vehicles)')->setParameter('vehicles', $workingVehicles)->getQuery()->execute();
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
