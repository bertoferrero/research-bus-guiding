<?php
namespace App\Lib\Components\ServiceData;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractServiceDataSynchronizer{
    
    public function __construct(protected EntityManagerInterface $em, protected ParameterBagInterface $params)
    {
        
    }

    abstract function executeSync();

}