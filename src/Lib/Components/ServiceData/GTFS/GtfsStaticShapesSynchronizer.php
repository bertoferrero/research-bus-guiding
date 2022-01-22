<?php
namespace App\Lib\Components\ServiceData\GTFS;

use App\Entity\ServiceData\Shape;
use App\Entity\ServiceData\Trip;
use Doctrine\ORM\EntityManagerInterface;

class GtfsStaticShapesSynchronizer{

    public function __construct(protected EntityManagerInterface $em)
    {
        
    }

    /**
     * This method will replace the shape database information with the data in the data array
     *
     * @param array $data
     * @return void
     */
    public function synchronizeFromData(array $data){
        dump($data);die();
        //We will need the search for the shape used on each trip. Each one will be processed individually
        $trips = $this->em->getRepository(Trip::class);
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