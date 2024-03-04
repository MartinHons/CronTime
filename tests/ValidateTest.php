<?php

declare(strict_types=1);

use MartinHons\CronTime;
use MartinHons\Exceptions\InvalidArgumentException;
use MartinHons\Exceptions\OutOfRangeException;
use PHPUnit\Framework\TestCase;

final class ValidateTest extends TestCase
{
    public function testEmptyCronTime(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CronTime('');
    }

    public function testFewParameters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CronTime('* * *');
    }

    public function testTooManyParameters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CronTime('* * * * * *');
    }

    public function testCorrectParametersCount(): void
    {
        $this->assertInstanceOf(CronTime::class, new CronTime('* * * * *'));
        $this->assertInstanceOf(CronTime::class, new CronTime('1 1 1 1 1'));
    }

    public function testOutOfRange(): void
    {
        $this->expectException(OutOfRangeException::class);
        new CronTime('70 * * * *');
    }



}