<?php
namespace App\MessageHandler\ServiceData;

use App\Lib\Components\ServiceData\GTFS\GtfsStaticShapesSynchronizer;
use App\Message\ServiceData\GTFSShapeImportingInitMessage;
use App\Lib\Components\ServiceData\GTFS\GtfsStaticSynchronizer;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class GTFSShapeImportingInitHandler implements MessageHandlerInterface{

    public function __construct(protected GtfsStaticShapesSynchronizer $synchronizer)
    {
        
    }
    
    public function __invoke(GTFSShapeImportingInitMessage $message)
    {
        $data = $message->getData();
        $this->synchronizer->synchronizeFromData($data);
    }

}