<?php

namespace App\Lib\Components\ServiceData\GTFS;

use App\Lib\GeoHelper;
use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\Shape;
use App\Entity\ServiceData\ShapeRaw;
use App\Entity\ServiceData\ShapePoint;
use App\Entity\ServiceData\StopTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GtfsStaticShapesSynchronizer
{

    protected $shapeMiddlePointsInterpolation;

    public function __construct(protected EntityManagerInterface $em, ParameterBagInterface $parameters)
    {
        $this->shapeMiddlePointsInterpolation = (int)$parameters->get('app.component.servicedatasync.shape.middle_points_interpolation');
    }


    public function generateShapePoints(Shape $shape, bool $clearExistingPoints = true)
    {
        //Check if there are existing points
        if ($clearExistingPoints) {
            $this->em->createQuery('DELETE FROM ' . ShapePoint::class . ' as point where point.shape = ' . $shape->getId())->execute();
        } elseif (!empty($shape->getShapePoints())) {
            throw new \Exception("This shape currently has points");
        }

        //Step 1 - Basic points with middle points interpolated filling the gaps
        $this->generateBasicShapePoints($shape);

        //Step 2 - Match stops
        $this->matchStops($shape);

        //Finally - Generate the distance between points into each segment
        $this->generateShapeSegmentsAndDistances($shape);
    }

    /**
     * Generates de basic shape structure from GTFS raw shape points. It adds interpolated points for filling gaps among points
     *
     * @param Shape $shape
     * @return void
     */
    protected function generateBasicShapePoints(Shape $shape)
    {
        //get Raw points from schema ID and insert them 
        $shapeRaws = $this->em->getRepository(ShapeRaw::class)->findBy(['schemaId' => $shape->getSchemaId()], ['sequence' => 'ASC']);
        if (empty($shapeRaws)) {
            throw new \Exception("There is not raw shape points for schema: " . $shape->getSchemaId());
        }
        $hundredFlush = 1000;

        $prevRawPoint = null;
        $prevEntity = null;
        foreach ($shapeRaws as $shapeRawPoint) {
            //Check the distance with the previous point for know if there is a gap to be filled.
            if ($prevRawPoint != null) {
                $distance = GeoHelper::vincentyGreatCircleDistance($prevRawPoint->getLatitude(), $prevRawPoint->getLongitude(), $shapeRawPoint->getLatitude(), $shapeRawPoint->getLongitude());
                if ($distance >= $this->shapeMiddlePointsInterpolation) {
                    //TODO esto es mejorable, hay que tener en cuenta la curvatura de la tierra
                    //Calculate the difference
                    $latD = $shapeRawPoint->getLatitude() - $prevRawPoint->getLatitude();
                    $lnD = $shapeRawPoint->getLongitude() - $prevRawPoint->getLongitude();
                    //Calculate the increment on each step
                    $latInc = $latD / ($distance / $this->shapeMiddlePointsInterpolation);
                    $lnInc = $lnD / ($distance / $this->shapeMiddlePointsInterpolation);
                    //Create the first incremented point
                    $lat = $prevRawPoint->getLatitude() + $latInc;
                    $lng = $prevRawPoint->getLongitude() + $lnInc;
                    do {
                        //Interpolated point entity creation
                        $shapePoint = new ShapePoint();
                        $shapePoint->setShape($shape);
                        $shapePoint->setLatitude($lat);
                        $shapePoint->setLongitude($lng);
                        if ($prevEntity != null) {
                            $shapePoint->setPrevPoint($prevEntity);
                        }
                        $this->em->persist($shapePoint);

                        $prevEntity = $shapePoint;
                        //Increment the point
                        $lat += $latInc;
                        $lng += $lnInc;
                    } while (abs($lat - $shapeRawPoint->getLatitude()) > abs($latInc) && abs($lng - $shapeRawPoint->getLongitude()) > abs($lnInc));
                }
            }
            $prevRawPoint = $shapeRawPoint;

            //Point creation
            $shapePoint = new ShapePoint();
            $shapePoint->setShape($shape);
            $shapePoint->setLatitude($prevRawPoint->getLatitude());
            $shapePoint->setLongitude($prevRawPoint->getLongitude());
            if ($prevEntity != null) {
                $shapePoint->setPrevPoint($prevEntity);
            }

            $this->em->persist($shapePoint);

            $prevEntity = $shapePoint;
            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $this->em->flush();
                $hundredFlush = 1000;
            }
        }
        $this->em->flush();
    }

    /**
     * Matches each stop in route with its nearest shape point
     *
     * @param Shape $shape
     * @return void
     */
    protected function matchStops(Shape $shape)
    {
        //We need one trip at least
        $trips = $shape->getTrips();
        if (empty($trips)) {
            throw new \Exception("For matching shapepints and stops it is needed a trip. Shape id: " . $shape->getId());
        }
        $trip = $trips[0];
        $shapePointRepo = $this->em->getRepository(ShapePoint::class);
        $hundredFlush = 100;
        //Get the stop sequence
        $stopSequence = $this->em->getRepository(StopTime::class)->findBy(['trip' => $trip], ['stopSequence' => 'ASC']);
        $processedStops = [];
        $firstStop = true;
        $lastStopPoint = null;
        foreach ($stopSequence as $stopSequenceRow) {
            $busStop = $stopSequenceRow->getStop();
            if (in_array($busStop->getId(), $processedStops)) {
                continue;
            }
            $processedStops[] = $busStop->getId();
            //From the stop we get the nearest point
            $nearestPoint = $shapePointRepo->findNearestPoint($busStop->getLatitude(), $busStop->getLongitude(), $shape);
            //If it exists, we associate it with the stop
            if (empty($nearestPoint)) {
                throw new \Exception("Empty point for stop :" . $busStop->getId() . " and shape :" . $shape->getId());
            }
            $nearestPoint->setStop($busStop);
            $lastStopPoint = $nearestPoint;
            $this->em->persist($nearestPoint);

            if ($firstStop) {
                //If it is the first stop, we must "cut" and delete previous points on the route
                $prevPoint = $nearestPoint->getPrevPoint();
                if ($prevPoint != null) {
                    //Unconnect from the shape chain
                    $prevPoint->setNextPoint(null);
                    $this->em->persist($prevPoint);
                    //Delete, all previous points will be cascade deleted
                    $this->em->remove($prevPoint);
                }
                $firstStop = false;
            }
            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $this->em->flush();
                $hundredFlush = 100;
            }
        }
        //Now, remove all the points far away from the last stop
        $nextPoint = $lastStopPoint->getNextPoint();
        if ($nextPoint != null) {
            //Disconect
            $lastStopPoint->setNextPoint(null);
            $this->em->persist($lastStopPoint);
            //And cascade delete
            $this->em->remove($nextPoint);
        }
        $this->em->flush();
    }

    protected function generateShapeSegmentsAndDistances(Shape $shape)
    {
        $points = $shape->getShapePoints();
        $currentStop = $prevShapePoint = null;
        $hundredFlush = 100;
        for ($x = count($points) - 1; $x >= 0; $x--) {
            $point = $points[$x];
            //Store the stop for this segment
            if ($point->getStop() != null) {
                if($currentStop != null){
                    $this->em->flush();
                    $updateQuery = $this->em->createQueryBuilder()->update(ShapePoint::class, 'r')->set('r.prevStopInRoute', $point->getStop()->getId())->andWhere('r.nextStopInRoute = :stop')->andWhere('r.shape = :shape')->andWhere('r.prevStopInRoute IS NULL')->setParameter('stop', $currentStop)->setParameter('shape', $shape);
                    $updateQuery->getQuery()->execute();
                }
                $currentStop = $point->getStop();
                $prevShapePoint = $point;
            }
            if ($currentStop != null) {
                //Set the stop of this chain segment
                $point->setNextStopInRoute($currentStop);
                //Calculate the distance between this and the last point from this segment
                $routePointsDistance = GeoHelper::vincentyGreatCircleDistance(
                    $point->getLatitude(),
                    $point->getLongitude(),
                    $prevShapePoint->getLatitude(),
                    $prevShapePoint->getLongitude()
                ) + $prevShapePoint->getNextStopRemainingDistance();
                $point->setNextStopRemainingDistance($routePointsDistance);

                $this->em->persist($point);
                $hundredFlush--;
                if ($hundredFlush <= 0) {
                    $this->em->flush();
                    $hundredFlush = 100;
                }
            }
            $prevShapePoint = $point;
        }
        $this->em->flush();
    }


    protected function clearGtfsTable()
    {
        $tables = [
            Shape::class,
        ];
        foreach ($tables as $table) {
            $this->em->createQuery('DELETE FROM ' . $table)->execute();
            $this->em->createQuery('ALTER TABLE ' . $table . ' AUTO_INCREMENT = 1')->execute();
        }
    }
}
