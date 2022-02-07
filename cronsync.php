<?php

$cron_url = 'http://127.0.0.1:8001/cron/servicedatasync/vehicleposition';
//$cron_url = 'https://research.busguiding.bertoferrero.com/cron/servicedatasync/vehicleposition';
$waitTime = 15;

while (true) {
    $startTime = time();
    file_get_contents($cron_url);
    $processWaitTime = $waitTime - (time() - $startTime);
    if ($processWaitTime > 0) {
        sleep($processWaitTime);
    }
    echo "ejecuto";
}
