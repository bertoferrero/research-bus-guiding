<?php
namespace App\Lib\Enum;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class StopRequestStatusEnum extends AbstractEnumType
{
    public const PENDING = 'PENDING';
    public const IN_PROGRESS = 'IN_PROGRESS';
    public const PROCESSED = 'PROCESSED';
    public const CANCELED = 'CANCELED';

    protected static array $choices = [
        self::PENDING => 'Pending',
        self::IN_PROGRESS => 'In progress',
        self::PROCESSED => 'Processed',
        self::CANCELED => 'Canceled',
    ];
}