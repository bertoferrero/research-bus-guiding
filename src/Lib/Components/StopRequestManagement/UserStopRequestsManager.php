<?php

namespace App\Lib\Components\StopRequestManagement;

use App\Entity\User;
use App\Entity\StopRequest;
use InvalidArgumentException;
use App\Entity\ServiceData\Stop;
use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\Route;
use App\Lib\Enum\StopRequestStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ServiceData\VehiclePosition;
use DateTime;

class UserStopRequestsManager
{

    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * Returns currently pending stop request for this user, null if there is not any
     *
     * @param User $user
     * @return StopRequest|null
     */
    public function getUserCurrentRequest(User $user): ?StopRequest
    {
        $this->invalidateOldRequests();
        $repo = $this->em->getRepository(StopRequest::class)->findOneBy(['user' => $user, 'status' => StopRequestStatusEnum::PENDING]);
        return $repo;
    }

    /**
     * Returns all the pending stop request for a user. Only devs can order a set of requests, riders will have only one pending request
     *
     * @param User $user
     * @return array|StopRequest[]
     */
    public function getUserCurrentRequests(User $user): array
    {
        $this->invalidateOldRequests();
        $repo = $this->em->getRepository(StopRequest::class)->findBy(['user' => $user, 'status' => StopRequestStatusEnum::PENDING]);
        return $repo;
    }

    /**
     * Sets a new request for this user invalidating the previous pending one, if there is
     *
     * @param User $user
     * @param integer $schemaStopId
     * @param string|null $schemaVehicleId
     * @param string|null $schemaLineId
     * @return void
     */
    public function setUserRequest(User $user, int $schemaStopId, ?string $schemaVehicleId, ?string $schemaLineId, bool $allowMultiplePendingRequests = false)
    {
        $this->invalidateOldRequests();
        $stop = $vehicle = $vehicleLine = $line = null;
        //Validate the existence of the stop, vehicle and line ids
        $stop = $this->em->getRepository(Stop::class)->findOneBy(['schemaId' => $schemaStopId]);
        if ($stop == null) {
            throw new InvalidArgumentException("Stop does not exist", 1);
        }
        if ($schemaVehicleId != null) {
            $vehicle = $this->em->getRepository(VehiclePosition::class)->findOneBy(['schemaVehicleId' => $schemaVehicleId]);
            if ($vehicle == null) {
                throw new InvalidArgumentException("Vehicle does not exist", 2);
            }
            //Check if vehicle achieves the stop
            $vehicleLine = $vehicle->getRoute();
            if (!$this->em->getRepository(Route::class)->checkRouteAndStop($vehicleLine, $stop)) {
                throw new InvalidArgumentException("Vehicle does not achieve the stop", 5);
            }
        }
        if ($schemaLineId != null) {
            $line = $this->em->getRepository(Route::class)->findOneBy(['schemaId' => $schemaLineId]);
            if ($line == null) {
                throw new InvalidArgumentException("Line does not exist", 4);
            }
            //Check if line achieves the stop
            if (!$this->em->getRepository(Route::class)->checkRouteAndStop($line, $stop)) {
                throw new InvalidArgumentException("Line does not achieve the stop", 5);
            }
        }
        if ($vehicleLine != null && $line != null && $line->getId() != $vehicleLine->getId()) {
            throw new InvalidArgumentException("Line and vehicle information are different", 6);
        }

        //If there is any previous request, cancel it
        if (!$allowMultiplePendingRequests) {
            $this->invalidateCurrenUserRequests($user);
        }

        //IF both line and vehicle are null, there is nothing to do
        if ($schemaLineId == null && $schemaVehicleId == null) {
            return;
        }

        $stopRequest = new StopRequest();
        $stopRequest->setSchemaVehicleId($schemaVehicleId);
        $stopRequest->setSchemaLineId($schemaLineId);
        $stopRequest->setSchemaStopId($schemaStopId);
        $stopRequest->setUser($user);
        $this->em->persist($stopRequest);
        $this->em->flush();
    }

    /**
     * Invalidates current pending stop request for this user if there is
     *
     * @param User $user
     * @return void
     */
    public function invalidateCurrenUserRequests(User $user)
    {
        $this->invalidateOldRequests();
        $previousRequests = $this->getUserCurrentRequests($user);
        foreach ($previousRequests as $previousRequest) {
            $previousRequest->setStatus(StopRequestStatusEnum::CANCELED);
            $this->em->persist($previousRequest);
        }
        $this->em->flush();
    }

    /**
     * Invalidates all pending requests older than 1 hour
     *
     * @return void
     */
    public function invalidateOldRequests()
    {
        $limitTime = new DateTime('-1 hour');
        $requests = $this->em->getRepository(StopRequest::class)->findPendingToCancel($limitTime);
        foreach ($requests as $request) {
            $request->setStatus(StopRequestStatusEnum::CANCELED);
            $this->em->persist($request);
        }
        $this->em->flush();
    }
}
