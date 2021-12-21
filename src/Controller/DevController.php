<?php
namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
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
        $vehiclePosition = $em->find(VehiclePosition::class, 736);
        $vehiclePosition->setCurrentStatus(VehiclePositionStatusEnum::STOPPED_AT);
        $em->persist($vehiclePosition);
        $em->flush();
        $vehiclePosition->setCurrentStatus(VehiclePositionStatusEnum::IN_TRANSIT_TO);
        $em->persist($vehiclePosition);
        $em->flush();
        return new Response('ok');
    }
}
