<?php

namespace App\Controller;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use App\Lib\Enum\VehiclePositionStatusEnum;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Lib\Components\Notifications\NotificationManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @Route("/testssignaldriver")
     */
    public function signaldriver(Request $request, NotificationManager $notificationManager): Response
    {
        //Create the form
        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('vehiclePosition', EntityType::class, [
                'class' => VehiclePosition::class, 
                'choice_label' => 'schemaVehicleId', 
                'label' => 'Vehicle - Vehículo',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('v')
                        ->orderBy('v.schemaVehicleId', 'ASC');
                }
            ]);
        $formBuilder->add('action', ChoiceType::class, [
            'choices'  => [
                'Simulate stop request. Simular solicitud de parada.' => 1,
                'Dismiss stop request. Desactivar solicitud de parada.' => 2,
            ],
            'label' => 'Action - Acción'
        ]);
        $formBuilder->add('send', SubmitType::class, [
            'label' => 'Send - Enviar'
        ]);
        $form = $formBuilder->getForm();

        //Process it
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $action = $data['action'];
            $vehiclePosition = $data['vehiclePosition'];
            $processed = true;
            match($action){
                1 => $notificationManager->sendStopNotification($vehiclePosition),
                2 => $notificationManager->sendDismissStopNotification($vehiclePosition),
                default => $processed = false
            };
            if($processed){
                $this->addFlash('success', 'Message send - Mensaje enviado');
            }
            else{
                $this->addFlash('danger', 'Error');
            }
        }
        return $this->render('devsignaldriver.html.twig', ['form' => $form->createView()]);
    }
}
