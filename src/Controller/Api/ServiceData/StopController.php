<?php

namespace App\Controller\Api\ServiceData;

use App\Entity\User;
use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Trip;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ServiceData\Route as ServiceDataRoute;
use App\Lib\Helpers\DateTimeHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/stop')]
class StopController extends AbstractController
{

    /**
     * Returns all the stops
     */
    #[Route('', name: 'api_stop_get_all', methods: ['GET'])]
    public function getAllAction(EntityManagerInterface $em): Response
    {
        $stops = $em->getRepository(Stop::class)->findAll();
        //Clean the response
        $stopsArray = [];
        foreach ($stops as $stop) {
            $stopsArray[] = [
                'id' => $stop->getSchemaId(),
                'lat' => $stop->getLatitude(),
                'lng' => $stop->getLongitude(),
                'name' => $stop->getName(),
                'code' => $stop->getCode()
            ];
        }

        return $this->json($stopsArray);
    }

    /**
     * Gets one stop
     */
    #[Route('/{schema_id}', name: 'api_stop_get_one', methods: ['GET'])]
    public function getOneAction(string $schema_id, EntityManagerInterface $em, Request $request, DateTimeHelper $dateTimeHelper): Response
    {
        $stop = $em->getRepository(Stop::class)->findBySchemaId($schema_id);
        if (empty($stop)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $stop->getSchemaId(),
            'lat' => $stop->getLatitude(),
            'lng' => $stop->getLongitude(),
            'name' => $stop->getName(),
            'code' => $stop->getCode()
        ];
        if ($request->query->get('withRoutes', 0) == 1) {
            $data['routes'] = $this->getRoutesForStop($stop, $em, $dateTimeHelper);
        }
        return $this->json($data);
    }
    
    #[Route('/stopcode/{stop_code}', name: 'api_stop_get_one_stop_code', methods: ['GET'])]
    public function getOneStopCodeAction(string $stop_code, EntityManagerInterface $em, Request $request, DateTimeHelper $dateTimeHelper): Response
    {
        $stop = $em->getRepository(Stop::class)->findOneBy(['code' => $stop_code]);
        if (empty($stop)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $stop->getSchemaId(),
            'lat' => $stop->getLatitude(),
            'lng' => $stop->getLongitude(),
            'name' => $stop->getName(),
            'code' => $stop->getCode()
        ];
        if ($request->query->get('withRoutes', 0) == 1) {
            $data['routes'] = $this->getRoutesForStop($stop, $em, $dateTimeHelper);
        }
        return $this->json($data);
    }

    /**
     * Gets one stop by latitude and longitude
     */
    #[Route('/nearest/{latitude}/{longitude}', name: 'api_stop_get_one_gps', methods: ['GET'])]
    public function getOneGpsAction(float $latitude, float $longitude, EntityManagerInterface $em, Request $request, DateTimeHelper $dateTimeHelper): Response
    {
        $distance = 0;
        $stop = $em->getRepository(Stop::class)->findByLatitudeLongitude($latitude, $longitude, 0, $distance);
        if (empty($stop)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $stop->getSchemaId(),
            'lat' => $stop->getLatitude(),
            'lng' => $stop->getLongitude(),
            'name' => $stop->getName(),
            'code' => $stop->getCode(),
            'distance' => $distance
        ];        
        if ($request->query->get('withRoutes', 0) == 1) {
            $data['routes'] = $this->getRoutesForStop($stop, $em, $dateTimeHelper);
        }
        return $this->json($data);
    }

    /**
     * Gets the rotes available for one stop
     */
    #[Route('/{schema_id}/routes', name: 'api_stop_get_routes', methods: ['GET'])]
    public function getRoutesAction(string $schema_id, EntityManagerInterface $em, DateTimeHelper $dateTimeHelper): Response
    {
        $stop = $em->getRepository(Stop::class)->findBySchemaId($schema_id);
        if (empty($stop)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->getRoutesForStop($stop, $em, $dateTimeHelper));
    }

    /**
     * Returns all the routes for a stop properly formatted 
     *
     * @param Stop $stop
     * @param EntityManagerInterface $em
     * @param DateTimeHelper $dateTimeHelper
     * @return array
     */
    protected function getRoutesForStop(Stop $stop, EntityManagerInterface $em, DateTimeHelper $dateTimeHelper): array
    {
        //Get all the trips working on this stop
        $timeNow = $dateTimeHelper->getDateTimeFromServiceDataTime();
        //Using this trips we get all the routes
        $routes = $em->getRepository(ServiceDataRoute::class)->findByStopAndWorkingDate($stop, $timeNow);

        $linesArray = [];
        foreach ($routes as $line) {
            $linesArray[] = [
                'id' => $line->getSchemaId(),
                'name' => $line->getName(),
                'color' => $line->getColor()
            ];
        }

        return $linesArray;
    }

    /*#[Route('/{schema_id}/stops', name: 'api_route_stops_get', methods: ['GET'])]
    public function getStops(EntityManagerInterface $em, string $schema_id, DateTimeHelper $dateTimeHelper): Response
    {
        //Get the line (route)
        $route = $em->getRepository(ServiceDataRoute::class)->findOneBy(['schemaId' => $schema_id]);
        if (empty($route)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        //Get all the trips for now
        $timeNow = $dateTimeHelper->getDateTimeFromServiceDataTime();
        $trips = $em->getRepository(Trip::class)->findByRouteAndWorkingDate($route, $timeNow);

        //Using this trips we get all the stops
        $stops = $em->getRepository(Stop::class)->findByTrips($trips);

        
        $stopsArray = [];
        foreach ($stops as $stop) {
            $stopsArray[] = [
                'id' => $stop->getSchemaId(),
                'lat' => $stop->getLatitude(),
                'lng' => $stop->getLongitude(),
                'name' => $stop->getName(),
                'code' => $stop->getCode()
            ];
        }



        return $this->json($stopsArray);
    }*/
}
