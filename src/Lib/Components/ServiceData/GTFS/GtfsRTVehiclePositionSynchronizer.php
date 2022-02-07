<?php

namespace App\Lib\Components\ServiceData\GTFS;

use App\Entity\User;
use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\Route;
use App\Entity\ServiceData\Stop;
use Google\Transit\Realtime\FeedMessage;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Enum\VehiclePositionStatusEnum;
use App\Lib\Components\ServiceData\AbstractServiceDataSynchronizer;


class GtfsRTVehiclePositionSynchronizer extends AbstractServiceDataSynchronizer
{
    public function executeSync(): void
    {
        $locationMode = (int)$this->params->get('app.component.servicedatasync.vehicle_location_mode');
        if ($locationMode == 2) { //Mode 2 menas ignore all data from GTFS-RT
            return;
        }

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
            //Comprobamos que tenemos todo
            if ($entity->getVehicle() == null) {
                //TODO error
            }
            $vehicle = $entity->getVehicle();
            if ($vehicle->getVehicle() == null) {
                //TODO error
            }
            if ($vehicle->getTrip() == null || (empty($vehicle->getTrip()->getTripId()) && empty($vehicle->getTrip()->getRouteId()))) {
                //TODO error
            }
            if ($locationMode == 0 && empty($vehicle->getStopId())) {
                //TODO error
            }
            //Recogemos el vehicle y procesamos la entidad
            $vehicleId = $vehicle->getVehicle()->getId();
            $vehicleEntity = $vehiclePositionRepo->findOneBy(['schemaVehicleId' => $vehicleId]);
            if ($vehicleEntity == null) {
                $vehicleEntity = new VehiclePosition();
                $vehicleEntity->setschemaVehicleId($vehicleId);
            }
            //GPS data
            $vehicleEntity->setLatitude((float)$vehicle->getPosition()->getLatitude());
            $vehicleEntity->setLongitude((float)$vehicle->getPosition()->getLongitude());
            //Trip or route information
            if (!empty($vehicle->getTrip()->getTripId())) {
                $trip = $this->em->getRepository(Trip::class)->findOneBy(['schemaId' => $vehicle->getTrip()->getTripId()]);
                if($trip == null){
                    //TODO error
                }
                $vehicleEntity->setRoute(null);
                $vehicleEntity->setTrip($trip);
            } else {
                $route = $this->em->getRepository(Route::class)->findOneBy(['schemaId' => $vehicle->getTrip()->getRouteId()]);
                if($route == null){
                    //TODO error
                }
                $vehicleEntity->setTrip(null);
                $vehicleEntity->setRoute($route);
            }
            //Vehicle location status (next stop and status)
            if ($locationMode == 0) {
                $stop = $this->em->getRepository(Stop::class)->findOneBy(['schemaId' => $vehicle->getStopId()]);
                if($stop == null){
                    //TODO error
                }
                $vehicleEntity->setNextStop($stop);
                $vehicleEntity->setCurrentStatus($this->transformCurrentStatus($vehicle->getCurrentStatus()));
            }

            $driver = $userRepo->findOneBy(['driverVehicleId' => $vehicleId]);
            $vehicleEntity->setDriver($driver);

            $this->em->persist($vehicleEntity);
            $workingVehicles[] = $vehicleId;
        }
        $this->em->flush();

        //Se desactiva porque no siempre vienen todos los vehículos en las actualizaciones
        //Borramos los vehiculos que ya no están en funcionamiento, por limpieza
        //$query = $this->em->createQueryBuilder();
        //$query->delete(VehiclePosition::class, 'v')->andWhere('v.schemaVehicleId NOT IN (:vehicles)')->setParameter('vehicles', $workingVehicles)->getQuery()->execute();
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
