<?php

namespace App\Controller\Api;

use App\Entity\ServiceData\Route as ServiceDataRoute;
use App\Entity\ServiceData\VehiclePosition;
use App\Entity\User;
use App\Entity\UserNotificationTopicSubscription;
use App\Lib\Components\UsersManagement\TopicSubscriptor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('', name: 'api_user', methods: ['GET'])]
    public function userGetAction(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user'  => $user->getUserIdentifier()
        ]);
    }

    #[Route('', name: 'api_user_put', methods: ['PUT'])]
    public function userPutAction(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new NotFoundHttpException('Excpecting mandatory parameters!');
        }

        $returnData = [];

        //Notification token updating
        if (isset($data['notification_token'])) {
            $notificationToken = trim(strip_tags($data['notification_token']));
            $user->setNotificationDeviceToken($notificationToken);
            $em->persist($user);
            $returnData['notification_token'] = $user->getNotificationDeviceToken();
        }
        //Driver data
        if ($this->isGranted('ROLE_DRIVER')) {
            //Driver vehicle id
            if (isset($data['vehicle_id'])) {
                $vehicleId = trim(strip_tags($data['vehicle_id']));
                $user->setDriverVehicleId($vehicleId);
                //Search vehiclePosition matching
                $vehiclePositionRepo = $em->getRepository(VehiclePosition::class);
                $vehiclePosition = $vehiclePositionRepo->findOneBy(['schemaVehicleId' => $vehicleId]);
                $user->setVehiclePosition($vehiclePosition);
                $em->persist($user);
            }

            if(isset($data['route_id'])){
                $routeId = trim(strip_tags($data['route_id']));
                $route = $em->getRepository(ServiceDataRoute::class)->findOneBy(['schemaId' => $routeId]);
                if(empty($route)){
                    return $this->json(['message' => 'route not found'], Response::HTTP_NOT_FOUND);
                }
                $user->setDriverRoute($route);
                $em->persist($user);
            }

            if(isset($data['lat']) && isset($data['lon'])){
                $latitude = (float)trim(strip_tags($data['lat']));
                $longitude = (float)trim(strip_tags($data['lon']));
                $user->setDriverLatitude($latitude);
                $user->setDriverLongitude($longitude);
                $em->persist($user);
            }
        }


        $em->flush();

        return $this->json($returnData);
    }

    #region Topics

    #[Route('/notificationtopics', name: 'api_user_topic_subscription_get', methods: ['GET'])]
    public function topicSubscriptionGetAction(#[CurrentUser] ?User $user, TopicSubscriptor $topicSubscriptor): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($topicSubscriptor->subscribed($user));
    }

    #[Route('/notificationtopics', name: 'api_user_topic_subscription_post', methods: ['POST'])]
    public function topicSubscriptionPostAction(#[CurrentUser] ?User $user, Request $request, TopicSubscriptor $topicSubscriptor): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new NotFoundHttpException('Excpecting mandatory parameters!');
        }

        $topicSubscriptor->subscribe($user, $data);

        return $this->json($topicSubscriptor->subscribed($user));
    }

    #[Route('/notificationtopics', name: 'api_user_topic_subscription_delete', methods: ['DELETE'])]
    public function topicSubscriptionDeleteAction(#[CurrentUser] ?User $user, Request $request, TopicSubscriptor $topicSubscriptor): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new NotFoundHttpException('Excpecting mandatory parameters!');
        }

        $topicSubscriptor->unsubscribe($user, $data);

        return $this->json($topicSubscriptor->subscribed($user));
    }

    #endregion
}
