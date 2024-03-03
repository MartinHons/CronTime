<?php

declare(strict_types = 1);

namespace MartinHons\CronTime;

class CronTime
{
    public function __construct(string $crontime)
    {
        echo $crontime;
    }
}