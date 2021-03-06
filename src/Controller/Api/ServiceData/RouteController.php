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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/route')]
class RouteController extends AbstractController
{

    /**
     * Returns all the lines (or routes)
     */
    #[Route('', name: 'api_route_get_all', methods: ['GET'])]
    public function getAllAction(EntityManagerInterface $em): Response
    {
        $lines = $em->getRepository(ServiceDataRoute::class)->findAll();
        //Clean the response
        $linesArray = [];
        foreach ($lines as $line) {
            $linesArray[] = [
                'id' => $line->getSchemaId(),
                'name' => $line->getName(),
                'color' => $line->getColor()
            ];
        }

        return $this->json($linesArray);
    }

    #[Route('/{schema_id}/stops', name: 'api_route_stops_get', methods: ['GET'])]
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
    }
}
