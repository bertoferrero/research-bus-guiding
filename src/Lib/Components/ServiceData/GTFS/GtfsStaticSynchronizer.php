<?php
namespace App\Lib\Components\ServiceData\GTFS;


use DateTime;
use App\Entity\Gtfs\Stop;
use App\Entity\Gtfs\Trip;
use App\Entity\Gtfs\StopTime;
use Trafiklab\Gtfs\Model\GtfsArchive;
use App\Entity\Gtfs\Route as GtfsRoute;
use App\Lib\Components\ServiceData\AbstractServiceDataSynchronizer;

class GtfsStaticSynchronizer extends AbstractServiceDataSynchronizer{
    public function executeSync()
    {
        //https://github.com/trafiklab/gtfs-php-sdk
        $feedUrl = $this->params->get('app.gtfs.static.url');

        //Vaciamos las tablas GTFS
        $this->clearGtfsTables();

        //No podemos descargar por url porque el burro ha puesto la ruta absoluta /tmp
        //$gtfsArchive = GtfsArchive::createFromUrl($feedUrl);

        //Descargamos nosotros
        $tmpGTFSFeed = tempnam(sys_get_temp_dir(), 'GTFS');
        file_put_contents($tmpGTFSFeed, file_get_contents($feedUrl));
        $gtfsArchive = GtfsArchive::createFromPath($tmpGTFSFeed);

        //Insertamos las paradas
        $stops = $gtfsArchive->getStopsFile()->getStops();
        foreach ($stops as $stopData) {
            $stop = new Stop();
            $stop->setGtfsId((int)$stopData->getStopId());
            $stop->setLatitude((float)$stopData->getStopLat());
            $stop->setLongitude((float)$stopData->getStopLon());
            $stop->setName($stopData->getStopName());
            $stop->setCode($stopData->getStopCode());
            $this->em->persist($stop);
        }
        $this->em->flush();

        //Las rutas
        $routes = $gtfsArchive->getRoutesFile()->getRoutes();
        foreach ($routes as $routeData) {
            $route = new GtfsRoute();
            $route->setGtfsId($routeData->getRouteId());
            $route->setName($routeData->getRouteLongName());
            $route->setColor($routeData->getRouteTextColor());
            $this->em->persist($route);
        }
        $this->em->flush();

        //Los viajes
        $trips = $gtfsArchive->getTripsFile()->getTrips();
        $routeRepo = $this->em->getRepository(GtfsRoute::class);
        foreach($trips as $tripData){
            $trip = new Trip();
            $trip->setGtfsId($tripData->getTripId());
            $trip->setGtfsRouteId($tripData->getRouteId());
            $route = $routeRepo->findOneBy(['gtfsId' => $tripData->getRouteId()]);
            $trip->setRoute($route);
            $this->em->persist($trip);
        }
        $this->em->flush();

        //Los tiempos de parada
        $stopTimes = $gtfsArchive->getStopTimesFile()->getStopTimes();
        $tripRepo = $this->em->getRepository(Trip::class);
        $stopRepo = $this->em->getRepository(Stop::class);
        foreach($stopTimes as $stopTimeData){
            $stopTime = new StopTime();
            $stopTime->setGtfsTripId($stopTimeData->getTripId());
            $trip = $tripRepo->findOneBy(['gtfsId' => $stopTimeData->getTripId()]);
            $stopTime->setTrip($trip);
            $stopTime->setArrivalTime(new DateTime($stopTimeData
            ->getArrivalTime()));
            $stopTime->setDepartureTime(new DateTime($stopTimeData
            ->getDepartureTime()));
            $stopTime->setGtfsStopId($stopTimeData->getStopId());
            $stop = $stopRepo->findOneBy(['gtfsId' => $stopTimeData->getStopId()]);
            $stopTime->setStop($stop);
            $stopTime->setStopSequence((int)$stopTimeData->getStopSequence());
            $this->em->persist($stopTime);
        }
        $this->em->flush();
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
}