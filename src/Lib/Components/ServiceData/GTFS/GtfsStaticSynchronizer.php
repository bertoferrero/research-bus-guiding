<?php

namespace App\Lib\Components\ServiceData\GTFS;


use DateTime;
use Exception;
use ZipArchive;
use Psr\Log\LoggerInterface;
use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\StopTime;
use Trafiklab\Gtfs\Model\GtfsArchive;
use App\Entity\ServiceData\Route as GtfsRoute;
use App\Lib\Components\ServiceData\AbstractServiceDataSynchronizer;

class GtfsStaticSynchronizer extends AbstractServiceDataSynchronizer
{

    public function executeSync()
    {
        //https://github.com/trafiklab/gtfs-php-sdk
        $feedUrl = "https://www.arcgis.com/sharing/rest/content/items/868df0e58fca47e79b942902dffd7da0/data"; //$this->params->get('app.gtfs.static.url');

        //Vaciamos las tablas GTFS
        $this->clearGtfsTables();

        //No podemos descargar por url porque el burro ha puesto la ruta absoluta /tmp
        //$gtfsArchive = GtfsArchive::createFromUrl($feedUrl);

        //Descargamos nosotros
        $tmpGTFSFeed = tempnam(sys_get_temp_dir(), 'GTFS');
        file_put_contents($tmpGTFSFeed, file_get_contents($feedUrl));
        $gtfsArchive = GtfsArchive::createFromPath($tmpGTFSFeed);

        //Insertamos las paradas
        $this->insertStops($gtfsArchive);

        //Las rutas
        $this->insertRoutes($gtfsArchive);

        //Los viajes
        $this->insertTrips($gtfsArchive);

        //Los tiempos de parada
        $this->insertStopTimes($gtfsArchive);
        
        return;

        //And now the shapes, which will be imported on the background thus their long calculation process time
        $shapes = $gtfsArchive->getShapesFile()->getAllDataRows();
        if (!empty($shapes)) { //Shapes are optional
            //Check every trip and 
            dump($shapes);
            die();
        }
    }

    protected function clearGtfsTables()
    {
        $tables = [
            StopTime::class,
            Stop::class,
            Trip::class,
            GtfsRoute::class,
        ];
        foreach ($tables as $table) {
            $this->em->createQuery('DELETE FROM ' . $table)->execute();
            //$this->em->createQuery('ALTER TABLE ' . $table . ' AUTO_INCREMENT = 1')->execute();
        }
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
        $lastRouteId = '';
        $route = null;
        while ($tripData = $trips->next()) {
            $trip = new Trip();
            $trip->setschemaId($tripData->getTripId());
            $trip->setschemaRouteId($tripData->getRouteId());
            if ($tripData->getRouteId() != $lastRouteId) {
                $route = $routeRepo->findBySchemaId($tripData->getRouteId());
                $lastRouteId = $tripData->getRouteId();
            }
            $trip->setRoute($route);
            $this->em->persist($trip);
            $trip = null;
            $tripData = null;
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

    protected function insertStopTimes(GtfsArchive $gtfsArchive){
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
}
