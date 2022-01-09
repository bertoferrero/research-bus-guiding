<?php

namespace App\Controller\Api\ServiceData;

use App\Entity\User;
use App\Entity\ServiceData\Stop;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ServiceData\Route as ServiceDataRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/line')]
class LineController extends AbstractController
{

    /**
     * Returns all the lines (or routes)
     */
    #[Route('', name: 'api_line_get_all', methods: ['GET'])]
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

    #[Route('/{schema_id}', name: 'api_line_get', methods: ['GET'])]
    public function getAction(EntityManagerInterface $em, string $schema_id): Response
    {
        //Get the line (route)
        $route = $em->getRepository(ServiceDataRoute::class)->findOneBy(['schemaId' => $schema_id]);
        if (empty($route)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $stops = $em->getRepository(Stop::class)->findByRoute($route);
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



        return $this->json([
            'id' => $route->getSchemaId(),
            'name' => $route->getName(),
            'color' => $route->getColor(),
            'stops' => $stopsArray
        ]);
    }
}
