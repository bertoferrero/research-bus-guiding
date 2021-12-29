<?php

namespace App\Controller\Api;

use App\Entity\NotificationLog;
use App\Entity\User;
use InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\UserNotificationTopicSubscription;
use App\Lib\Components\UsersManagement\TopicSubscriptor;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Lib\Components\StopRequestManagement\UserStopRequestsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/notificationlog')]
class NotificationLogController extends AbstractController
{

    #[Route('/{notificationlogId}', name: 'notificationlog_putpost', methods: ['PUT', 'POST'])]
    public function putPostAction(#[CurrentUser] ?User $user, int $notificationlogId, Request $request, EntityManagerInterface $em): Response
    {
        try {
            if (null === $user) {
                return $this->json([
                    'message' => 'missing credentials',
                ], Response::HTTP_UNAUTHORIZED);
            }

            //Load the notification log and check it
            $notificationLog = $em->find(NotificationLog::class, $notificationlogId);
            if(empty($notificationLog)){
                return $this->json(['message' => 'not found'], Response::HTTP_NOT_FOUND);
            }
            if($notificationLog->getDeviceToken() != $user->getNotificationDeviceToken()){
                return $this->json([
                    'message' => 'unauthorized',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                throw new NotFoundHttpException('Excpecting mandatory parameters!');
            }

            //Check the data
            if (!isset($data['delivery_date'])) {
                throw new NotFoundHttpException('Excpecting mandatory parameters!');
            }

            $delivery_date = $data['delivery_date'];
            $deliveryDateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $delivery_date, new \DateTimeZone("UTC"));

            $notificationLog->setDateDelivered($deliveryDateTime);
            $em->persist($notificationLog);
            $em->flush();

            //return the same get action result
            return $this->json(["result" => "done"]);
        } catch (InvalidArgumentException $ex) {
            return $this->json([
                'error_code' => "SR-" . $ex->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
