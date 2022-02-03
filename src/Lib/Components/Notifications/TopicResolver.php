<?php
namespace App\Lib\Components\Notifications;

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
        $routeSchemaId = $vehiclePosition->getSchemaRouteId();
        if ($routeSchemaId != null) {
            $topics[] = $this->composeTopic(['line', $routeSchemaId, $vehiclePosition->getCurrentStatus(), $vehiclePosition->getschemaStopId()]);
            $topics[] = $this->composeTopic(['line', $routeSchemaId, $vehiclePosition->getCurrentStatus(), 0]);
        } else {
            $this->logger->error("TopicResolver - El vehiculo no tiene route id: ", [$vehiclePosition]);
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
