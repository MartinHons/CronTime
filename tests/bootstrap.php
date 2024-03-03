<?php

declare(strict_types=1);

use MartinHons\CronTime;

require __DIR__ . '/../vendor/autoload.php';
new CronTime('* * * * *');
