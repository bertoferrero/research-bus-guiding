<?php

namespace App\Controller\Cron;

use App\Entity\Gtfs\Route as GtfsRoute;
use App\Entity\Gtfs\Stop;
use App\Entity\Gtfs\StopTime;
use App\Entity\Gtfs\Trip;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Trafiklab\Gtfs\Model\GtfsArchive;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GtfsStaticImportController extends AbstractController
{
    /**
     * @Route("/cron/gtfs/static/import", name="cron_gtfs_static_import")
     */
    public function index(ParameterBagInterface $params, EntityManagerInterface $em): Response
    {
        //https://github.com/trafiklab/gtfs-php-sdk
        $feedUrl = $params->get('app.gtfs.static.url');

        //Vaciamos las tablas GTFS
        $this->clearGtfsTables($em);

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
            $em->persist($stop);
        }
        $em->flush();

        //Las rutas
        $routes = $gtfsArchive->getRoutesFile()->getRoutes();
        foreach ($routes as $routeData) {
            $route = new GtfsRoute();
            $route->setGtfsId($routeData->getRouteId());
            $route->setName($routeData->getRouteLongName());
            $route->setColor($routeData->getRouteTextColor());
            $em->persist($route);
        }
        $em->flush();

        //Los viajes
        $trips = $gtfsArchive->getTripsFile()->getTrips();
        $routeRepo = $em->getRepository(GtfsRoute::class);
        foreach($trips as $tripData){
            $trip = new Trip();
            $trip->setGtfsId($tripData->getTripId());
            $trip->setGtfsRouteId($tripData->getRouteId());
            $route = $routeRepo->findOneBy(['gtfsId' => $tripData->getRouteId()]);
            $trip->setRoute($route);
            $em->persist($trip);
        }
        $em->flush();

        //Los tiempos de parada
        $stopTimes = $gtfsArchive->getStopTimesFile()->getStopTimes();
        $tripRepo = $em->getRepository(Trip::class);
        $stopRepo = $em->getRepository(Stop::class);
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
            $em->persist($stopTime);
        }
        $em->flush();

        return new Response('ok');
    }

    protected function clearGtfsTables(EntityManagerInterface $em)
    {
        $tables = [
            StopTime::class,
            Stop::class,
            Trip::class,
            GtfsRoute::class,
        ];
        foreach ($tables as $table) {
            $em->createQuery('DELETE FROM ' . $table)->execute();
            //$em->createQuery('ALTER TABLE ' . $table . ' AUTO_INCREMENT = 1')->execute();
        }
    }
}
