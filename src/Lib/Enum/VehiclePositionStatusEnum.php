<?php
namespace App\Lib\Enum;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class VehiclePositionStatusEnum extends AbstractEnumType
{
    public const INCOMING_AT = 'INCOMING_AT';
    public const STOPPED_AT = 'STOPPED_AT';
    public const IN_TRANSIT_TO = 'IN_TRANSIT_TO';

    protected static array $choices = [
        self::INCOMING_AT => 'INCOMING_AT',
        self::STOPPED_AT => 'STOPPED_AT',
        self::IN_TRANSIT_TO => 'IN_TRANSIT_TO',
    ];
}