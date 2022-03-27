<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Components\Notifications\NotificationManager;
use App\Lib\Enum\VehiclePositionStatusEnum;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/dev")
 */
class DevController extends AbstractController
{
    /**
     * @Route("/teststopsignal")
     */
    public function stopsignal(EntityManagerInterface $em): Response
    {
        $vehiclePosition = $em->find(VehiclePosition::class, 1);
        $vehiclePosition->setCurrentStatus(VehiclePositionStatusEnum::STOPPED_AT);
        $em->persist($vehiclePosition);
        $em->flush();
        $vehiclePosition->setCurrentStatus(VehiclePositionStatusEnum::IN_TRANSIT_TO);
        $em->persist($vehiclePosition);
        $em->flush();
        return new Response('ok');
    }

    /**
     * @Route("/teststopsignaldriver/{vehicle_id}")
     */
    public function stopsignaldriver(EntityManagerInterface $em, NotificationManager $notificationManager, $vehicle_id = null): Response
    {
        $vehiclePosition = null;
        if ($vehicle_id !== null) {
            $vehiclePosition = $em->getRepository(VehiclePosition::class)->findOneBy(['schemaVehicleId' => trim($vehicle_id)]);
        } else {
            $vehiclePosition = $em->find(VehiclePosition::class, 1);
        }
        if ($vehiclePosition === null) {
            return new Response('Vehiculo id no encontrado');
        }
        $notificationManager->sendStopNotification($vehiclePosition);
        return new Response('ok');
    }
    /**
     * @Route("/testdismissstopsignaldriver/{vehicle_id}")
     */
    public function dismissstopsignaldriver(EntityManagerInterface $em, NotificationManager $notificationManager, $vehicle_id = null): Response
    {
        $vehiclePosition = null;
        if ($vehicle_id !== null) {
            $vehiclePosition = $em->getRepository(VehiclePosition::class)->findOneBy(['schemaVehicleId' => trim($vehicle_id)]);
        } else {
            $vehiclePosition = $em->find(VehiclePosition::class, 1);
        }
        if ($vehiclePosition === null) {
            return new Response('Vehiculo id no encontrado');
        }
        $notificationManager->sendDismissStopNotification($vehiclePosition);
        return new Response('ok');
    }
}
