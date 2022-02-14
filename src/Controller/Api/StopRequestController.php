<?php

namespace App\Controller\Api;

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

#[Route('/stoprequest')]
class StopRequestController extends AbstractController
{
    #[Route('', name: 'stoprequest_get', methods: ['GET'])]
    public function getAction(#[CurrentUser] ?User $user, UserStopRequestsManager $userStopRequestsManager): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request = $userStopRequestsManager->getUserCurrentRequest($user);
        $return = null;
        if ($request != null) {
            $return = [
                'vehicle' => $request->getSchemaVehicleId(),
                'line' => $request->getSchemaLineId(),
                'stop' => $request->getSchemaStopId(),
                'requestdate' => $request->getDateAdd()
            ];
        }

        return $this->json($return);
    }

    #[Route('', name: 'stoprequest_putpost', methods: ['PUT', 'POST'])]
    public function putPostAction(#[CurrentUser] ?User $user, Request $request, UserStopRequestsManager $userStopRequestsManager): Response
    {
        try {
            if (null === $user) {
                return $this->json([
                    'message' => 'missing credentials',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                throw new NotFoundHttpException('Excpecting mandatory parameters!');
            }

            //Check the data
            if (!isset($data['stop_id']) || (!isset($data['vehicle_id']) && !isset($data['line_id']))) {
                throw new NotFoundHttpException('Excpecting mandatory parameters!');
            }

            //Set Data
            $userStopRequestsManager->setUserRequest($user, (int)$data['stop_id'], $data['vehicle_id'] ?? null, $data['line_id'] ?? null, $this->isGranted('ROLE_DEV'));

            //return the same get action result
            return $this->getAction($user, $userStopRequestsManager);
        } catch (InvalidArgumentException $ex) {
            return $this->json([
                'error_code' => "SR-" . $ex->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'stoprequest_delete', methods: ['DELETE'])]
    public function deleteAction(#[CurrentUser] ?User $user, Request $request, UserStopRequestsManager $userStopRequestsManager): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userStopRequestsManager->invalidateCurrenUserRequests($user);

        return $this->json('done');
    }
}
