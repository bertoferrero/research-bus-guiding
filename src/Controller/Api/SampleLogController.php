<?php

namespace App\Controller\Api;

use App\Entity\SampleLog;
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

#[Route('/samplelog')]
class SampleLogController extends AbstractController
{

    #[Route('', name: 'samplelog_putpost', methods: ['PUT', 'POST'])]
    public function putPostAction(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $em): Response
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
            if (!isset($data['sample_type']) || !isset($data['sample_date'])) {
                throw new NotFoundHttpException('Excpecting mandatory parameters!');
            }

            $sample_type = $data['sample_type'];
            $sample_date = $data['sample_date'];

            $sampleDateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $sample_date, new \DateTimeZone("UTC"));

            $sampleLog = new SampleLog();
            $sampleLog->setType($sample_type);
            $sampleLog->setSampleDateTime($sampleDateTime);
            $sampleLog->setExtraData(trim($data['extra_data'] ?? ""));
            $em->persist($sampleLog);
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
