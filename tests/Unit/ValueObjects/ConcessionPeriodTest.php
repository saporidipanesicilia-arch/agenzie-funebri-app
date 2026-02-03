<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\ConcessionPeriod;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConcessionPeriodTest extends TestCase
{
    /** @test */
    public function it_creates_valid_periods()
    {
        $period10 = ConcessionPeriod::fromYears(10);
        $period20 = ConcessionPeriod::fromYears(20);
        $period30 = ConcessionPeriod::fromYears(30);
        $period99 = ConcessionPeriod::fromYears(99);

        $this->assertEquals(10, $period10->years());
        $this->assertEquals(20, $period20->years());
        $this->assertEquals(30, $period30->years());
        $this->assertEquals(99, $period99->years());
    }

    /** @test */
    public function it_rejects_invalid_periods()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid concession period: 15 years');

        ConcessionPeriod::fromYears(15); // Not in valid list
    }

    /** @test */
    public function it_creates_standard_period()
    {
        $standard = ConcessionPeriod::standard();

        $this->assertEquals(20, $standard->years());
    }

    /** @test */
    public function it_creates_perpetual_period()
    {
        $perpetual = ConcessionPeriod::perpetual();

        $this->assertEquals(99, $perpetual->years());
        $this->assertTrue($perpetual->isPerpetual());
    }

    /** @test */
    public function it_calculates_expiration_for_standard_periods()
    {
        $period = ConcessionPeriod::fromYears(20);
        $startDate = new \DateTimeImmutable('2026-02-03');

        $expiration = $period->calculateExpiration($startDate);

        $this->assertEquals('2046-02-03', $expiration->format('Y-m-d'));
    }

    /** @test */
    public function it_returns_null_expiration_for_perpetual()
    {
        $period = ConcessionPeriod::perpetual();
        $startDate = new \DateTimeImmutable('2026-02-03');

        $expiration = $period->calculateExpiration($startDate);

        $this->assertNull($expiration);
    }

    /** @test */
    public function it_has_correct_string_representation()
    {
        $period20 = ConcessionPeriod::fromYears(20);
        $perpetual = ConcessionPeriod::perpetual();

        $this->assertEquals('20 years', (string) $period20);
        $this->assertEquals('Perpetual', (string) $perpetual);
    }
}
