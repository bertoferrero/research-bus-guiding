<?php

namespace App\Message\ServiceData;

class GTFSShapeImportingInitMessage
{

    private $data;

    public function __construct(array $shapeInformation)
    {
        $this->data = gzcompress(json_encode($shapeInformation), 9);
    }

    public function getData(): array
    {
        return json_decode(gzuncompress($this->data));
    }
}
