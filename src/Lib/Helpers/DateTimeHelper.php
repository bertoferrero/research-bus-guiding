<?php

namespace App\Lib\Helpers;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DateTimeHelper
{

    public function __construct(protected ParameterBagInterface $params)
    {
    }

    /**
     * Returns a DateTime instance from a specific time zone but using default timezone without altering date or time
     *
     * @param string $time
     * @param string|null $timezone
     * @return \DateTime
     */
    public function getDateTimeFromServiceDataTime(string $time = "now", string $timezone = null): \DateTime
    {
        if($timezone == null){
            $timezone = $this->params->get('app.component.servicedatasync.timezone');
        }
        $dateTime = new \DateTime($time, new \DateTimeZone($timezone));
        $time = $dateTime->format("Y-m-d H:i:s");
        $finalDateTime = new \DateTime($time);
        return $finalDateTime;
    }
}
