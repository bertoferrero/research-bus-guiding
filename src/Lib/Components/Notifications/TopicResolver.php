<?php

use App\Entity\ServiceData\Trip;
use App\Entity\ServiceData\VehiclePosition;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TopicResolver
{

    public function __construct(protected EntityManagerInterface $em, protected LoggerInterface $logger)
    {
    }

    /**
     * Returns all the notification topics related to a vehicleposition entity
     *
     * @param VehiclePosition $vehiclePosition
     * @return array
     */
    public function getTopicsForVehiclePosition(VehiclePosition $vehiclePosition): array
    {
        $topics = [];
        //Vehicle
        $topics[] = $this->composeTopic(['vehicle', $vehiclePosition->getschemaVehicleId(), $vehiclePosition->getCurrentStatus(), $vehiclePosition->getschemaStopId()]);
        $topics[] = $this->composeTopic(['vehicle', $vehiclePosition->getschemaVehicleId(), $vehiclePosition->getCurrentStatus(), 0]);
        $topics[] = $this->composeTopic(['vehicle', 0, $vehiclePosition->getCurrentStatus(), $vehiclePosition->getschemaStopId()]);
        $topics[] = $this->composeTopic(['vehicle', 0, $vehiclePosition->getCurrentStatus(), 0]);

        //Line
        $trip = $this->em->getRepository(Trip::class)->findOneBy(['schemaId' => $vehiclePosition->getschemaTripId()]);
        if ($trip != null) {
            $topics[] = $this->composeTopic(['line', $trip->getRoute()->getSchemaRouteId(), $vehiclePosition->getCurrentStatus(), $vehiclePosition->getschemaStopId()]);
            $topics[] = $this->composeTopic(['line', $trip->getRoute()->getSchemaRouteId(), $vehiclePosition->getCurrentStatus(), 0]);
        } else {
            $this->logger->error("TopicResolver - No se localiza Trip para el id indicado: " . $vehiclePosition->getschemaTripId(), [$vehiclePosition]);
        }
        $topics[] = $this->composeTopic(['line', 0, $vehiclePosition->getCurrentStatus(), $vehiclePosition->getschemaStopId()]);
        $topics[] = $this->composeTopic(['line', 0, $vehiclePosition->getCurrentStatus(), 0]);

        return $topics;
    }

    /**
     * Undocumented function
     *
     * @param array $topics
     * @return array
     */
    public function retrieveNotificationTokens(array $topics):array{
        return $this->em->getRepository(User::class)->findNotificationTokensByTopics($topics);
    }

    /**
     * Merges the array elements to compose the topic string
     *
     * @param array $topicElements
     * @return void
     */
    public function composeTopic(array $topicElements)
    {
        return implode('.', $topicElements);
    }
}
