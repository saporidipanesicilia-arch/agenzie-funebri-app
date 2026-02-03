<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\FuneralCode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FuneralCodeTest extends TestCase
{
    /** @test */
    public function it_creates_valid_funeral_code()
    {
        $code = FuneralCode::fromString('FUN-2026-001');

        $this->assertEquals('FUN-2026-001', $code->value());
        $this->assertEquals(2026, $code->year());
        $this->assertEquals(1, $code->sequence());
    }

    /** @test */
    public function it_generates_funeral_code()
    {
        $code = FuneralCode::generate(2026, 42);

        $this->assertEquals('FUN-2026-042', $code->value());
        $this->assertEquals(2026, $code->year());
        $this->assertEquals(42, $code->sequence());
    }

    /** @test */
    public function it_rejects_invalid_format()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid funeral code format');

        FuneralCode::fromString('INVALID-CODE');
    }

    /** @test */
    public function it_rejects_invalid_year_in_generation()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Year must be between 2000 and 2100');

        FuneralCode::generate(1999, 1);
    }

    /** @test */
    public function it_rejects_invalid_sequence_in_generation()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sequence must be between 1 and 999');

        FuneralCode::generate(2026, 1000); // Too large
    }

    /** @test */
    public function it_extracts_year_correctly()
    {
        $code = FuneralCode::fromString('FUN-2026-123');

        $this->assertEquals(2026, $code->year());
    }

    /** @test */
    public function it_extracts_sequence_correctly()
    {
        $code = FuneralCode::fromString('FUN-2026-007');

        $this->assertEquals(7, $code->sequence()); // Leading zeros removed
    }

    /** @test */
    public function it_checks_equality()
    {
        $code1 = FuneralCode::fromString('FUN-2026-001');
        $code2 = FuneralCode::generate(2026, 1);
        $code3 = FuneralCode::fromString('FUN-2026-002');

        $this->assertTrue($code1->equals($code2));
        $this->assertFalse($code1->equals($code3));
    }

    /** @test */
    public function it_handles_max_sequence()
    {
        $code = FuneralCode::generate(2026, 999);

        $this->assertEquals('FUN-2026-999', $code->value());
        $this->assertEquals(999, $code->sequence());
    }
}
