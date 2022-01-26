<?php
namespace App\MessageHandler\ServiceData;

use App\Entity\ServiceData\Shape;
use App\Message\ServiceData\GTFSShapePointGenerateMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Lib\Components\ServiceData\GTFS\GtfsStaticShapesSynchronizer;
use Doctrine\ORM\EntityManagerInterface;

class GTFSShapePointGenerateMessageHandler implements MessageHandlerInterface{

    public function __construct(protected GtfsStaticShapesSynchronizer $synchronizer, protected EntityManagerInterface $em)
    {
        
    }
    
    public function __invoke(GTFSShapePointGenerateMessage $message)
    {
        $entityId = $message->getShapeEntityId();
        $shape = $this->em->find(Shape::class, $entityId);
        if($shape == null){
            throw new \Exception("Shape id does not exist: ".$entityId);
        }
        $this->synchronizer->generateShapePoints($shape);
    }

}