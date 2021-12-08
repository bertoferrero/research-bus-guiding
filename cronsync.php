<?php

$cron_url = 'http://127.0.0.1:8001/cron/gtfs/rt/vehicleposition';
$waitTime = 10;

while (true) {
    $startTime = time();
    file_get_contents($cron_url);
    $processWaitTime = $waitTime - (time() - $startTime);
    if ($processWaitTime > 0) {
        sleep($processWaitTime);
    }
    echo "ejecuto";
}
