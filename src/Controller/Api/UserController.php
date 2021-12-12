<?php

namespace App\Controller\Api;

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
    #[Route('/', name: 'api_user', methods: ['GET'])]
    public function loginAction(#[CurrentUser] ?User $user): Response
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

        $data = json_decode($request->getContent(),true);
        if(!is_array($data)){
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

        $data = json_decode($request->getContent(),true);
        if(!is_array($data)){
            throw new NotFoundHttpException('Excpecting mandatory parameters!');
        }

        $topicSubscriptor->unsubscribe($user, $data);

        return $this->json($topicSubscriptor->subscribed($user));
    }

    #endregion
}
