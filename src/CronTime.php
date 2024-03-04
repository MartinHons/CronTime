<?php

declare(strict_types=1);

namespace MartinHons;


use DateTimeImmutable;
use MartinHons\Exceptions\InvalidArgumentException;
use MartinHons\Exceptions\OutOfRangeException;
use Throwable;

final class CronTime
{
    private array $limits;

    public function __construct(string $cronTime)
    {
        $this->limits = $this->findLimits($cronTime);
        return $this;
    }

    public function match(DateTimeImmutable $datetime): bool
    {
        foreach($this->explodeDatetime($datetime) as $type => $dateVal) {
            if(!in_array($dateVal, $this->limits[$type])) {
                return false;
            }
        }
        return true;
    }

    private function findLimits(string $cronTime): array
    {
        $result = [];
        foreach($this->explodeCrontime($cronTime) as $type => $cronVal) {
            $typeRange = $this->getTypeRange($type);
            $allowedValues = [];

            if ($cronVal === '*') {
                // Hvězdička - do $allowedValues dáme celý $typeRange
                $allowedValues = $typeRange;
            }
            elseif(preg_match('/^[0-9]+$/', $cronVal)) {
                // Číslo - v allowedValues bude jediná hodnota
                array_push($allowedValues, $this->getNumberValue((int)$cronVal, $typeRange));
            }
            else {
                // Procházíme výrazy oddělené čárkou
                foreach(explode(',',$cronVal) as $subExpr) {
                    if ($cronVal === '*') {
                        // Hvězdička - do $allowedValues dáme celý $typeRange a vyskočíme z cyklu - nemá smysl ho dále procházet
                        $allowedValues = $typeRange;
                        break;
                    }
                    elseif(preg_match('/^[0-9]+$/', $subExpr)) {
                        // Číslo oddělené čárkou
                        array_push($allowedValues, $this->getNumberValue((int)$subExpr, $typeRange));
                    }
                    else {
                        if (str_contains($subExpr, '/')) {
                            if (str_contains($subExpr, '-')) {
                                // Krok */15 nebo krok s počátem 20/15 nebo krok s rozsahem 10-40/5
                                array_push($allowedValues, ...$this->getStepValues($subExpr, $typeRange));
                            }
                        }
                        elseif(str_contains($subExpr, '-')) {
                            // Rozsah dvou čísel 1-2
                            array_push($allowedValues, ...$this->getRangeValues($subExpr, $typeRange));
                        }
                        else {
                            throw new InvalidArgumentException('Neočekávaná chyba '.$cronTime);
                        }
                    }
                }
            }
            $result[$type] = array_unique($allowedValues);
        }
        return $result;
    }

    private function getNumberValue(int $number, array $typeRange): int
    {
        if (in_array($number, $typeRange)) {
            return $number;
        }
        $min = min($typeRange);
        $max = max($typeRange);
        throw new OutOfRangeException('Value '.$number.' is out of range: '.$min.'-'.$max);
    }

    private function getRangeValues(string $rangeString, array $typeRange): array
    {
        $range = explode('-', $rangeString);
        if (count($range) !== 2) {
            throw new InvalidArgumentException('Rozsah '.$rangeString.' není platný');
        }
        else {
            try {
                $rangeFrom = (int)$range[0];
                $rangeTo = (int)$range[1];
            }
            catch(Throwable) {
                throw new InvalidArgumentException('Rozsah '.$rangeString.' není platný');
            }
            return array_filter($typeRange, fn($value) => ($value >= $rangeFrom && $value <= $rangeTo));
        }
    }

    private function getStepValues(string $stepString, array $typeRange): array
    {
        $step = explode('/', $stepString);
        $begin = min($typeRange);
        $end = max($typeRange);
        if (count($step) !== 2) {
            throw new InvalidArgumentException('Rozsah '.$stepString.' není platný');
        }
        else {
            try {
                if ($step[0] !== '*') {
                    if (str_contains($step[0], '-')) {
                        $range = explode('-', $step[0]);
                        if(count($range) !== 2) {
                            throw new InvalidArgumentException('Chyba');
                        }
                        else {
                            $begin = (int)$range[0];
                            $end = (int)$range[1];
                        }
                    }
                    else {
                        $begin = (int)$step[0];
                    }
                }
                $step = (int)$step[1];
            }
            catch(Throwable) {
                throw new InvalidArgumentException('Krok '.$stepString.' není platný');
            }
            return array_filter($typeRange, fn($value) => $value >= $begin && $value <= $end && $value % $step === 0);
        }
    }

    private function getTypeRange(string $type): array
    {
        $ranges = [
            'min' => ['min' => 0, 'max' => 59],
            'hrs' => ['min' => 0, 'max' => 23],
            'day' => ['min' => 1, 'max' => 31],
            'mth' => ['min' => 1, 'max' => 12],
            'dow' => ['min' => 0, 'max' => 6],
        ];
        $min = $ranges[$type]['min'];
        $max = $ranges[$type]['max'];
        return array_keys(array_fill($min, $max - $min + 1, null));
    }

    private function explodeCronTime(string $cronTime): array
    {
        $cronValues = explode(' ', $cronTime);
        $valuesCount = count($cronValues);
        echo $valuesCount;
        if ($valuesCount !== 5) {
            throw new InvalidArgumentException('CronTime must have exactly 5 values separated by spaces. This has '.$valuesCount.' values.');
        }
        return [
            'min' => $cronValues[0],
            'hrs' => $cronValues[1],
            'day' => $cronValues[2],
            'mth' => $cronValues[3],
            'dow' => in_array($cronValues[4], ['07','7']) ? '0' : $cronValues[4],
        ];
    }

    private function explodeDatetime(DateTimeImmutable $datetime): array
    {
        return [
            'min' => (int)$datetime->format('i'),
            'hrs' => (int)$datetime->format('G'),
            'day' => (int)$datetime->format('j'),
            'mth' => (int)$datetime->format('n'),
            'dow' => (int)$datetime->format('N') % 7
        ];
    }
}