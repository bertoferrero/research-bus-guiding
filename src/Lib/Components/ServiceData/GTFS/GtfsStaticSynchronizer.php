<?php

namespace App\Lib\Components\ServiceData\GTFS;

use App\Entity\ServiceData\Calendar;
use App\Entity\ServiceData\CalendarDates;
use App\Entity\ServiceData\CalendarPlan;
use App\Entity\ServiceData\Frequencies;
use DateTime;
use Exception;
use ZipArchive;
use Psr\Log\LoggerInterface;
use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\StopTime;
use Trafiklab\Gtfs\Model\GtfsArchive;
use App\Entity\ServiceData\Route as GtfsRoute;
use App\Entity\ServiceData\Shape;
use App\Entity\ServiceData\ShapePoint;
use App\Entity\ServiceData\ShapeRaw;
use App\Message\ServiceData\ShapeImportingInitMessage;
use App\Message\ServiceData\GTFSShapeImportingInitMessage;
use App\Lib\Components\ServiceData\AbstractServiceDataSynchronizer;
use App\Message\ServiceData\GTFSShapePointGenerateMessage;
use Doctrine\ORM\EntityManagerInterface;
use Trafiklab\Gtfs\Model\Entities\CalendarDate;
use Trafiklab\Gtfs\Model\Entities\Frequency;

class GtfsStaticSynchronizer extends AbstractServiceDataSynchronizer
{

    public function executeSync()
    {
        //https://github.com/trafiklab/gtfs-php-sdk
        //$feedUrl = "https://www.arcgis.com/sharing/rest/content/items/868df0e58fca47e79b942902dffd7da0/data"; //$this->params->get('app.gtfs.static.url');
        $feedUrl = $this->params->get('app.gtfs.static.url');

        /*$shapes = $this->em->getRepository(Shape::class)->findAll();
        foreach($shapes as $shape){
            $this->bus->dispatch(new GTFSShapePointGenerateMessage($shape));
        }
        return;*/

        //Vaciamos las tablas GTFS
        $this->clearGtfsTables();

        //No podemos descargar por url porque el burro ha puesto la ruta absoluta /tmp
        //$gtfsArchive = GtfsArchive::createFromUrl($feedUrl);

        //Descargamos nosotros
        $tmpGTFSFeed = tempnam(sys_get_temp_dir(), 'GTFS');
        file_put_contents($tmpGTFSFeed, file_get_contents($feedUrl));
        $gtfsArchive = GtfsArchive::createFromPath($tmpGTFSFeed);

        //Insert Calendar service information
        $this->insertCalendars($gtfsArchive);

        //Frequencies
        $this->insertFrequencies($gtfsArchive);

        //Insertamos las paradas
        $this->insertStops($gtfsArchive);

        //Las rutas
        $this->insertRoutes($gtfsArchive);

        //Los viajes
        $this->insertTrips($gtfsArchive);

        //Los tiempos de parada
        $this->insertStopTimes($gtfsArchive);

        //And now, define the hours range
        $this->defineTripsHoursRange();

        //And the md5 stop sequence
        $this->createTripMd5StopSequence();

        //Prepare shapes raw data
        $this->insertShapesRaw($gtfsArchive);

        //Generate the internal precalculated shapepoints        
        $this->createPrecalculatedShapes();
    }

    protected function clearGtfsTables()
    {
        $tables = [
            ShapePoint::class,
            StopTime::class,
            Stop::class,
            Trip::class,
            GtfsRoute::class,
            CalendarPlan::class,
            CalendarDates::class,
            Calendar::class,
            Frequencies::class,
            Shape::class
        ];
        foreach ($tables as $table) {
            $cmd = $this->em->getClassMetadata($table);
            $connection = $this->em->getConnection();
            $dbPlatform = $connection->getDatabasePlatform();
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            $connection->executeStatement($q);
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
            //$this->em->createQuery('DELETE FROM ' . $table)->execute();
            //$this->em->createQuery('ALTER TABLE ' . $table . ' AUTO_INCREMENT = 1')->execute();
        }
    }

    protected function insertCalendars(GtfsArchive $gtfsArchive)
    {
        $calendarRepo = $this->em->getRepository(Calendar::class);
        //First we take care of calendar plans
        $calendarPlans = $gtfsArchive->getCalendarFile();
        $hundredFlush = 100;
        $weekDays = [
            'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
        ];
        while ($calendarData = $calendarPlans->next()) {
            //Search the main calendar entity
            $calendar = $calendarRepo->findBySchemaId($calendarData->getServiceId(), true);
            $calendar->setSchemaId($calendarData->getServiceId());
            $this->em->persist($calendar);

            //Create the calendar plan
            $currentCalendarPlan = $calendar->getCalendarPlan();
            if ($currentCalendarPlan != null) {
                throw new Exception("There are two calendars with the same service id");
            }

            $calendarPlan = new CalendarPlan();
            $calendarPlan->setSchemaId($calendarData->getServiceId());
            foreach ($weekDays as $weekDay) {
                $calendarPlan->{"set$weekDay"}(($calendarData->{"get$weekDay"}() == 1));
            }
            $calendarPlan->setStartDate($calendarData->getStartDate());
            $calendarPlan->setEndDate($calendarData->getEndDate());
            $calendarPlan->setCalendar($calendar);
            $this->em->persist($calendarPlan);

            $calendar = null;
            $calendarData = null;
            $calendarPlan = null;

            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 100;
                $this->em->flush();
                $this->em->clear();
            }
        }
        $calendarPlans = null;
        $this->em->flush();
        $this->em->clear();

        //Now, calendar dates with exceptions and special service days
        $calendarDateRepo = $this->em->getRepository(CalendarDates::class);
        $calendarDates = $gtfsArchive->getCalendarDatesFile();
        $hundredFlush = 100;
        while ($calendarDateData = $calendarDates->next()) {
            //Search the main calendar entity
            $calendar = $calendarRepo->findBySchemaId($calendarDateData->getServiceId(), true);
            $calendar->setSchemaId($calendarDateData->getServiceId());
            $this->em->persist($calendar);

            //Find anti collision
            $calendarDate = $calendarDateRepo->findOneBy(['schemaId' => $calendarDateData->getServiceId(), 'date' => $calendarDateData->getDate()]);
            if ($calendarDate != null) {
                throw new Exception("There are two calendars dates with the same service id and date");
            }

            $calendarDate = new CalendarDates();
            $calendarDate->setSchemaId($calendarDateData->getServiceId());
            $calendarDate->setDate($calendarDateData->getDate());
            $calendarDate->setIsRemovingDate($calendarDateData->getExceptionType() == 2);
            $calendarDate->setCalendar($calendar);
            $this->em->persist($calendarDate);

            $calendar = null;
            $calendarDate = null;
            $calendarDateData = null;

            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 100;
                $this->em->flush();
                $this->em->clear();
            }
        }
        $calendarDates = null;
        $this->em->flush();
        $this->em->clear();
    }

    protected function insertFrequencies(GtfsArchive $gtfsArchive)
    {
        $frequencies = $gtfsArchive->getFrequenciesFile();
        $hundredFlush = 100;
        while ($frequencyData = $frequencies->next()) {

            //Create the frequency object
            $frequency = new Frequencies();

            $frequency->setSchemaTripId($frequencyData->getTripId());
            $frequency->setStartTime($frequencyData->getStartTime());
            $frequency->setEndTime($frequencyData->getEndTime());
            $frequency->setHeadwaySecs($frequencyData->getHeadwaySecs());
            $this->em->persist($frequency);

            $frequency = null;
            $frequencyData = null;

            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 100;
                $this->em->flush();
                $this->em->clear();
            }
        }
        $frequencies = null;
        $this->em->flush();
        $this->em->clear();
    }

    protected function insertStops(GtfsArchive $gtfsArchive)
    {
        $stops = $gtfsArchive->getStopsFile();
        $hundredFlush = 100;
        while ($stopData = $stops->next()) {
            $stop = new Stop();
            $stop->setschemaId($stopData->getStopId());
            $stop->setLatitude((float)$stopData->getStopLat());
            $stop->setLongitude((float)$stopData->getStopLon());
            $stop->setName($stopData->getStopName());
            $stop->setCode($stopData->getStopCode());
            $this->em->persist($stop);
            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 100;
                $this->em->flush();
                $this->em->clear();
            }
            $stop = null;
            $stopData = null;
        }
        $stops = null;
        $this->em->flush();
        $this->em->clear();
    }

    protected function insertRoutes(GtfsArchive $gtfsArchive)
    {

        $hundredFlush = 100;
        $routes = $gtfsArchive->getRoutesFile();
        while ($routeData = $routes->next()) {
            $route = new GtfsRoute();
            $route->setschemaId($routeData->getRouteId());
            $route->setName($routeData->getRouteLongName());
            $route->setColor($routeData->getRouteTextColor());
            $this->em->persist($route);
            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 100;
                $this->em->flush();
                $this->em->clear();
            }
            $routeData = null;
            $route = null;
        }
        $routes = null;
        $this->em->flush();
        $this->em->clear();
    }

    protected function insertTrips(GtfsArchive $gtfsArchive)
    {
        $hundredFlush = 100;
        $trips = $gtfsArchive->getTripsFile();
        $routeRepo = $this->em->getRepository(GtfsRoute::class);
        $calendarRepo = $this->em->getRepository(Calendar::class);
        $lastRouteId = '';
        $route = null;
        while ($tripData = $trips->next()) {
            $trip = new Trip();
            $trip->setschemaId($tripData->getTripId());
            $trip->setSchemaShapeId($tripData->getShapeId());
            $trip->setschemaRouteId($tripData->getRouteId());
            if ($tripData->getRouteId() != $lastRouteId) {
                $route = $routeRepo->findBySchemaId($tripData->getRouteId());
                $lastRouteId = $tripData->getRouteId();
            }
            $trip->setRoute($route);
            $calendar = $calendarRepo->findBySchemaId($tripData->getServiceId());
            if ($calendar == null) {
                throw new \Exception("Service id from trip cannot be found on calendar\'s table: " . $tripData->getServiceId());
            }
            $trip->setSchemaServiceId($tripData->getServiceId());
            $trip->setCalendar($calendar);
            $this->em->persist($trip);
            $trip = null;
            $tripData = null;
            $calendar = null;
            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 100;
                $this->em->flush();
                $this->em->clear();
                //After clearing, it is required to reload the cached route
                $lastRouteId = '';
            }
        }
        $trips = $route = null;
        $this->em->flush();
        $this->em->clear();
    }

    protected function insertStopTimes(GtfsArchive $gtfsArchive)
    {
        $hundredFlush = 1000;
        $stopTimes = $gtfsArchive->getStopTimesFile();
        $tripRepo = $this->em->getRepository(Trip::class);
        $stopRepo = $this->em->getRepository(Stop::class);
        $lastTripId = '';
        $trip = null;
        while ($stopTimeData = $stopTimes->next()) {
            $stopTime = new StopTime();
            $stopTime->setschemaTripId($stopTimeData->getTripId());
            if ($stopTimeData->getTripId() != $lastTripId) {
                $trip = $tripRepo->findBySchemaId($stopTimeData->getTripId());
                $lastTripId = $stopTimeData->getTripId();
            }
            $stopTime->setTrip($trip);
            $stop = $stopRepo->findBySchemaId($stopTimeData->getStopId());
            $stopTime->setStop($stop);
            $stopTime->setArrivalTime(new DateTime($stopTimeData
                ->getArrivalTime()));
            $stopTime->setDepartureTime(new DateTime($stopTimeData
                ->getDepartureTime()));
            $stopTime->setschemaStopId($stopTimeData->getStopId());
            $stopTime->setStopSequence((int)$stopTimeData->getStopSequence());
            $this->em->persist($stopTime);
            $stopTimeData = null;
            $stopTime = null;
            $stop = null;
            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 1000;
                $this->em->flush();
                $this->em->clear();
                //After clearing, it is required to reload the cached trip
                $lastTripId = '';
            }
        }
        $stopTimes = $trip = null;
        $this->em->flush();
        $this->em->clear();
    }

    protected function insertShapesRaw(GtfsArchive $gtfsArchive)
    {
        $shapes = $gtfsArchive->getShapesFile();
        $hundredFlush = 100;
        while ($shapeData = $shapes->next()) {
            $shape = new ShapeRaw();
            $shape->setSchemaId($shapeData->getShapeId());
            $shape->setLatitude($shapeData->getShapePtLat());
            $shape->setLongitude($shapeData->getShapePtLon());
            $shape->setSequence($shapeData->getShapePtSequence());
            $this->em->persist($shape);
            $hundredFlush--;
            if ($hundredFlush <= 0) {
                $hundredFlush = 100;
                $this->em->flush();
                $this->em->clear();
            }
            $shape = null;
            $shapeData = null;
        }
        $shapes = null;
        $this->em->flush();
        $this->em->clear();
    }

    protected function defineTripsHoursRange()
    {
        //First, hours from frequencies
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->update(Trip::class, 't');
        $queryBuilder->set('t.hourStart', '(SELECT MIN(fq.startTime) FROM ' . Frequencies::class . ' as fq WHERE fq.schemaTripId = t.schemaId)');
        $queryBuilder->set('t.hourEnd', '(SELECT MAX(fq2.endTime) FROM ' . Frequencies::class . ' as fq2 WHERE fq2.schemaTripId = t.schemaId)');
        $queryBuilder->getQuery()->execute();

        //Then, the not matched rows, from stop_times
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->update(Trip::class, 't');
        $queryBuilder->set('t.hourStart', '(SELECT MIN(st.arrivalTime) FROM ' . StopTime::class . ' as st WHERE st.trip = t)');
        $queryBuilder->set('t.hourEnd', '(SELECT MAX(st2.departureTime) FROM ' . StopTime::class . ' as st2 WHERE st2.trip = t)');
        $queryBuilder->where('t.hourStart IS NULL OR t.hourEnd IS NULL');
        $queryBuilder->getQuery()->execute();
    }

    protected function createTripMd5StopSequence()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->update(Trip::class, 't');
        $queryBuilder->set('t.md5StopSequence', '(SELECT MD5(CONCAT(trip.schemaRouteId,\'-\',group_concat(stopt.schemaStopId),\'-\',group_concat(stopt.stopSequence))) FROM ' . Trip::class . ' as trip
        INNER JOIN trip.stopTimes stopt 
        WHERE trip = t
        group by trip)');
        $queryBuilder->getQuery()->execute();
    }

    protected function createPrecalculatedShapes()
    {
        //First get all the trips
        $trips = $this->em->getRepository(Trip::class)->findAll();
        $shapeRepo = $this->em->getRepository(Shape::class);
        $hundredFlush = 100;
        foreach ($trips as $trip) {
            if (!empty($trip->getSchemaShapeId())) {
                $stopMd5 = $trip->getMd5StopSequence();
                if (empty($stopMd5)) {
                    throw new Exception('There are trips without md5stopsequence');
                }
                $shape = $shapeRepo->findOneBy(['md5StopSequence' => $stopMd5]);
                //If shape does not exists, is time to create the basic entity and order to generate all the points
                if ($shape == null) {
                    $shape = new Shape();
                    $shape->setSchemaId($trip->getSchemaShapeId());
                    $shape->setMd5StopSequence($stopMd5);
                    $shape->addTrip($trip);
                    $this->em->persist($shape);
                    $this->em->flush();
                    //Message to generate points
                    $this->bus->dispatch(new GTFSShapePointGenerateMessage($shape));
                    $hundredFlush = 100;
                } else {
                    $trip->setShape($shape);
                    $this->em->persist($trip);

                    $hundredFlush--;
                    if ($hundredFlush <= 0) {
                        $hundredFlush = 100;
                        $this->em->flush();
                    }
                }
                $shape = null;
                $trip = null;
            }
        }
        $trips = null;
        $this->em->flush();
        $this->em->clear();
    }
}

//CONCAT(trip.schemaRouteId,"-",group_concat(stopt.stop_id),"-",group_concat(stopt.stop_sequence))