<?php

declare(strict_types=1);

namespace MartinHons;


use DateTimeImmutable;
use MartinHons\Exceptions\InvalidException;

final class CronTime
{
    private string $cronTime;

    public function __construct(string $cronTime)
    {
        $this->validate($cronTime);
        $this->cronTime = $cronTime;
        return $this;
    }

    private function validate(string $cronTime): bool
    {
        $cron = $this->explodeCronTime($cronTime);
        /*
        if(substr_count($cronTime, ' ') !== 4) {
            return false;
        }
        */

        return true;
    }

    public function match(DateTimeImmutable $dateTime): bool
    {
        return false;
    }

    private function explodeCronTime(string $cronTime): array
    {
        $cronValues = explode(' ', $cronTime);
        $valuesCount = count($cronValues);
        if ($valuesCount !== 5) {
            throw new InvalidException('CronTime must have exactly 5 values separated by spaces. This has '.$valuesCount.' values.');
        }
        return [
            'min' => $cronValues[0],
            'hrs' => $cronValues[1],
            'day' => $cronValues[2],
            'mnt' => $cronValues[3],
            'dow' => $cronValues[4],
        ];
    }
}

new CronTime('* * * * *');