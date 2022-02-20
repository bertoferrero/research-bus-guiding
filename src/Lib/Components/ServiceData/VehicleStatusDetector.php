<?php

namespace App\Lib\Components\ServiceData;

use App\Lib\GeoHelper;
use App\Entity\ServiceData\Route;
use App\Entity\ServiceData\ShapePoint;
use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Trip;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Enum\VehiclePositionStatusEnum;
use App\Lib\Helpers\DateTimeHelper;
use DateTimeZone;
use Doctrine\ORM\Query\Expr\Join;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VehicleStatusDetector
{
    protected $shapeMiddlePointsInterpolation;
    protected int $incoming_meters = 125; //TODO Configuración 350-> 30s, 125m -> 15s
    protected int $stopped_meters = 20; //TODO Configuración

    public function __construct(protected EntityManagerInterface $em, protected ParameterBagInterface $parameters, protected LoggerInterface $logger, protected DateTimeHelper $dateTimeHelper)
    {
        $this->shapeMiddlePointsInterpolation = (int)$this->parameters->get('app.component.servicedatasync.shape.middle_points_interpolation');
    }

    public function detectVehicleStopAndStatus(VehiclePosition $vehicle, bool $persist = true): VehiclePosition
    {
        //Step 1 - Get the nearest point
        $nearestPoint = null;
        if (($trip = $vehicle->getTrip()) != null) {
            $nearestPoint = $this->getNearestPointFromTrips($vehicle, [$trip], $this->shapeMiddlePointsInterpolation * 3);
        }
        if ($nearestPoint == null && ($route = $vehicle->getRoute()) != null) {
            $nearestPoint = $this->getNearestPointFromRoute($vehicle, $route);
        }
        if ($nearestPoint == null) {
            $this->logger->error("No nearest point is detected AT ALL", [$vehicle->getLatitude(), $vehicle->getLongitude(), $vehicle->getSchemaRouteId(), $vehicle->getschemaTripId(), $vehicle->getschemaVehicleId()]);
            return $vehicle;
            throw new \Exception("No nearest point is detected");
        }

        //2 - Calculate the distance to next stop and define the status
        $distanceToStop = $this->calculateDistanceUntilNextStop($vehicle, $nearestPoint);
        $vehicleStopStatus = null;
        if ($distanceToStop < $this->stopped_meters) {
            $vehicleStopStatus = VehiclePositionStatusEnum::STOPPED_AT;
        } elseif ($distanceToStop < $this->incoming_meters) {
            $vehicleStopStatus = VehiclePositionStatusEnum::INCOMING_AT;
        } else {
            $vehicleStopStatus = VehiclePositionStatusEnum::IN_TRANSIT_TO;
        }

        //3- Update the entity
        $vehicle->setPrevStop($nearestPoint->getPrevStopInRoute());
        $vehicle->setNextStop($nearestPoint->getNextStopInRoute());
        $vehicle->setCurrentStatus($vehicleStopStatus);

        if ($persist) {
            $this->em->persist($vehicle);
        }
        return $vehicle;
    }

    #region Nearest point detection

    protected function getNearestPointFromTrips(VehiclePosition $vehicle, array $trips, float $distanceLimit = 0): ?ShapePoint
    {
        if (count($trips) == 0) {
            return null;
        }

        //First, get related stops from vehicle history data
        $vehicleRelatedStops = [];
        if ($vehicle->getPrevStop() != null) {
            $vehicleRelatedStops[] = $vehicle->getPrevStop();
        }
        if ($vehicle->getNextStop() != null) {
            $vehicleRelatedStops[] = $vehicle->getNextStop();
        }

        $nearestPoint = null;
        $shapePointRepo = $this->em->getRepository(ShapePoint::class);
        $relatedStopsUseful = false;
        //If there are related stops, first, try to find the nearestpoint filtering by these shape sections
        if (count($vehicleRelatedStops)) {
            if (($nearestPoint = $shapePointRepo->findNearestPointFromTripSet($vehicle->getLatitude(), $vehicle->getLongitude(), $distanceLimit, $trips, $vehicleRelatedStops)) != null) {
                $relatedStopsUseful = true;
            }
        }
        //If finally the nearestpoint cannot be found, lets search on the whole shape
        if ($nearestPoint == null) {
            $nearestPoint = $shapePointRepo->findNearestPointFromTripSet($vehicle->getLatitude(), $vehicle->getLongitude(), $distanceLimit, $trips);
        }

        //Loggin moment
        $tripsLog = array_map(function ($x) {
            return [$x->getId(), $x->getSchemaId()];
        }, $trips);
        $relatedStopsLog = array_map(function ($x) {
            return [$x->getId(), $x->getSchemaId()];
        }, $vehicleRelatedStops);
        if ($nearestPoint == null) {
            $this->logger->debug("No nearest point is detected", [$tripsLog, $distanceLimit, $relatedStopsLog, $vehicle->getLatitude(), $vehicle->getLongitude(), $vehicle->getSchemaRouteId(), $vehicle->getschemaTripId(), $vehicle->getschemaVehicleId()]);
        } else {
            if ($relatedStopsUseful) {
                $this->logger->debug("Nearest point detected with related stops", [$tripsLog, $distanceLimit, $relatedStopsLog, $vehicle->getLatitude(), $vehicle->getLongitude(), $vehicle->getSchemaRouteId(), $vehicle->getschemaTripId(), $vehicle->getschemaVehicleId()]);
            } else {
                $this->logger->debug("Nearest point detected without related stops", [$tripsLog, $distanceLimit, [], $vehicle->getLatitude(), $vehicle->getLongitude(), $vehicle->getSchemaRouteId(), $vehicle->getschemaTripId(), $vehicle->getschemaVehicleId()]);
            }
        }
        return $nearestPoint;
    }

    protected function getNearestPointFromRoute(VehiclePosition $vehicle, Route $route): ?ShapePoint
    {

        $timeNow = $this->dateTimeHelper->getDateTimeFromServiceDataTime();

        $trips = $this->em->getRepository(Trip::class)->findByRouteAndWorkingDate($route, $timeNow);

        return $this->getNearestPointFromTrips($vehicle, $trips, $this->shapeMiddlePointsInterpolation * 3);
    }

    #endregion

    /**
     * Calculates the remaining distance until next stop
     *
     * @param VehiclePosition $vehicle
     * @param ShapePoint $nearestPoint
     * @return float distance in meters
     */
    protected function calculateDistanceUntilNextStop(VehiclePosition $vehicle, ShapePoint $nearestPoint): float
    {
        $distanceToStop = null;
        //We get the next point from nearest point, if the distance is less than the interpolation it means that the vehicle is going to it
        if ($nearestPoint->getStop() == null) { //Si el punto actual es parada le damos la mitad del margen de beneficio
            $nextNearestPoint = $nearestPoint->getNextPoint();
            if ($nextNearestPoint != null) {
                $distanceToStop = GeoHelper::vincentyGreatCircleDistance(
                    $vehicle->getLatitude(),
                    $vehicle->getLongitude(),
                    $nextNearestPoint->getLatitude(),
                    $nextNearestPoint->getLongitude()
                );
                if ($distanceToStop < $this->shapeMiddlePointsInterpolation) {
                    $nearestPoint = $nextNearestPoint;
                } else {
                    $distanceToStop = null;
                }
            }
        }

        if ($distanceToStop === null) {
            $distanceToStop = GeoHelper::vincentyGreatCircleDistance(
                $vehicle->getLatitude(),
                $vehicle->getLongitude(),
                $nearestPoint->getLatitude(),
                $nearestPoint->getLongitude()
            );
        }

        //We have the distance until the point, we must to add the distance until next stop
        $distanceToStop += (float)$nearestPoint->getNextStopRemainingDistance();

        return $distanceToStop;
    }
}
