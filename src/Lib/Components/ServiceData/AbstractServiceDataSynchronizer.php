<?php
namespace App\Lib\Components\ServiceData;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractServiceDataSynchronizer{
    

    public function __construct(protected EntityManagerInterface $em, protected ParameterBagInterface $params, protected LoggerInterface $logger, protected MessageBusInterface $bus)
    {
        
    }

    abstract function executeSync();

}