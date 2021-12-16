<?php

namespace App\Lib\Components\ServiceData\GTFS;


use Google\Transit\Realtime\FeedMessage;
use App\Entity\ServiceData\VehiclePosition;
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
        $userRepo = $this->em->getRepository(User::class);
        $workingVehicles = [];
        foreach ($entities as $entity) {
            //Recogemos el vehicle y procesamos la entidad
            $vehicle = $entity->getVehicle();
            $vehicleId = $vehicle->getVehicle()->getId();
            $vehicleEntity = $vehiclePositionRepo->findOneBy(['schemaVehicleId' => $vehicleId]);
            if ($vehicleEntity == null) {
                $vehicleEntity = new VehiclePosition();
                $vehicleEntity->setschemaVehicleId($vehicleId);
            }
            $vehicleEntity->setLatitude((float)$vehicle->getPosition()->getLatitude());
            $vehicleEntity->setLongitude((float)$vehicle->getPosition()->getLongitude());
            $vehicleEntity->setschemaTripId($vehicle->getTrip()->getTripId());
            $vehicleEntity->setschemaStopId($vehicle->getStopId());
            $vehicleEntity->setCurrentStatus($this->transformCurrentStatus($vehicle->getCurrentStatus()));
            
            $driver = $userRepo->findOneBy(['driverVehicleId' => $vehicleId]);
            $vehicleEntity->setDriver($driver);

            $this->em->persist($vehicleEntity);
            $workingVehicles[] = $vehicleId;
        }
        $this->em->flush();

        //Borramos los vehiculos que ya no están en funcionamiento, por limpieza
        $query = $this->em->createQueryBuilder();
        $query->delete(VehiclePosition::class, 'v')->andWhere('v.schemaVehicleId NOT IN (:vehicles)')->setParameter('vehicles', $workingVehicles)->getQuery()->execute();
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
