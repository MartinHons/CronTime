<?php

declare(strict_types=1);

use MartinHons\CronTime;
use PHPUnit\Framework\TestCase;

final class MatchTest extends TestCase
{
    public function testAllStars(): void
    {
        $this->assertSame(true, (new CronTime('* * * * *'))->match(new DateTimeImmutable));
    }

    public function testRange(): void
    {
        $cronTime = new CronTime('10-15 * * * *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:09')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 00:10')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 00:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:16')));
    }

    public function testStarSteps(): void
    {
        $cronTime = new CronTime('*/15 */6 */7 */3 *');
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 00:00')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-06-14 06:15')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-09-21 12:30')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-12-28 18:45')));

        $cronTime = new CronTime('* * * * */2');
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-05 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-06 00:00')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-08 00:00')));
    }


    public function testBeginSteps(): void
    {
        $cronTime = new CronTime('16/15 * * * *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:15')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 00:30')));

        $cronTime = new CronTime('* 7/6 * * *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 06:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 07:00')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 12:00')));

        $cronTime = new CronTime('* * 8/7 * *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-01 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-08 00:00')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-14 00:00')));

        $cronTime = new CronTime('* * * 3/2 *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-01-01 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-01 00:00')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-04-01 00:00')));

        $cronTime = new CronTime('* * * * 3/2');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-05 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-06 00:00')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 00:00')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-08 00:00')));

        $cronTime = new CronTime('* * * * 2/2');
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-05 00:00')));
    }


    public function testRangeSteps(): void
    {
        $cronTime = new CronTime('11-19/5 * * * *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:05')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:10')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 00:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:20')));

        $cronTime = new CronTime('11-19/5 9-14/5 * * *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 00:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 09:15')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-07 10:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-07 15:15')));

        $cronTime = new CronTime('11-19/5 9-14/5 9-23/8 * *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-08 10:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-09 10:15')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-03-16 10:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-24 10:15')));

        $cronTime = new CronTime('11-19/5 9-14/5 9-23/8 4-8/3 *');
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-03-16 10:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-04-16 10:15')));
        $this->assertSame(true, $cronTime->match(new DateTimeImmutable('2024-06-16 10:15')));
        $this->assertSame(false, $cronTime->match(new DateTimeImmutable('2024-09-16 10:15')));
    }
}