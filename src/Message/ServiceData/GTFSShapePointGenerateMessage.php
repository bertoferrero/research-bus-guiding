<?php

namespace App\Message\ServiceData;

use App\Entity\ServiceData\Shape;

class GTFSShapePointGenerateMessage
{

    protected int $shapeEntityId;

    public function __construct(Shape $shape)
    {
        $this->shapeEntityId = $shape->getId();
    }

    public function getShapeEntityId(): int
    {
        return $this->shapeEntityId;
    }
}
