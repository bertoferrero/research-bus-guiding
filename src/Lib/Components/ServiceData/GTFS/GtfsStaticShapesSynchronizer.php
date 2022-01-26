<?php
namespace App\Lib\Components\ServiceData\GTFS;

use App\Entity\ServiceData\Shape;
use App\Entity\ServiceData\Trip;
use Doctrine\ORM\EntityManagerInterface;

class GtfsStaticShapesSynchronizer{

    public function __construct(protected EntityManagerInterface $em)
    {
        
    }

    
    public function generateShapePoints(Shape $shape){
        dump($shape);die();
    }


    protected function clearGtfsTable()
    {
        $tables = [
            Shape::class,
        ];
        foreach ($tables as $table) {
            $this->em->createQuery('DELETE FROM ' . $table)->execute();
            $this->em->createQuery('ALTER TABLE ' . $table . ' AUTO_INCREMENT = 1')->execute();
        }
    }

}