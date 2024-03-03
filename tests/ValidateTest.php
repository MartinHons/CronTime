<?php

declare(strict_types=1);

use MartinHons\CronTime;
use MartinHons\Exceptions\InvalidException;
use PHPUnit\Framework\TestCase;

final class ValidateTest extends TestCase
{
    public function testEmpty(): void
    {
        $this->expectException(InvalidException::class);
        new CronTime('');
    }

    public function testFewParameters(): void
    {
        $this->expectException(InvalidException::class);
        new CronTime('* * *');
    }

    public function testTooManyParameters(): void
    {
        $this->expectException(InvalidException::class);
        new CronTime('* * * * * *');
    }

    public function testCorrectParametersCount(): void
    {
        $this->assertInstanceOf(CronTime::class, new CronTime('* * * * *'));
    }

    public function testOnlyNumbers(): void
    {
        $this->assertInstanceOf(CronTime::class, new CronTime('10 10 10 10 5'));
    }
}