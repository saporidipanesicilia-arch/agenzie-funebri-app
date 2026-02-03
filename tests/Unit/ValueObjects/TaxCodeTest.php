<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\TaxCode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TaxCodeTest extends TestCase
{
    /** @test */
    public function it_creates_valid_tax_code()
    {
        $taxCode = TaxCode::fromString('RSSMRA45L01F205Z');

        $this->assertEquals('RSSMRA45L01F205Z', $taxCode->value());
        $this->assertEquals('RSSMRA45L01F205Z', (string) $taxCode);
    }

    /** @test */
    public function it_normalizes_lowercase_to_uppercase()
    {
        $taxCode = TaxCode::fromString('rssmra45l01f205z');

        $this->assertEquals('RSSMRA45L01F205Z', $taxCode->value());
    }

    /** @test */
    public function it_trims_whitespace()
    {
        $taxCode = TaxCode::fromString('  RSSMRA45L01F205Z  ');

        $this->assertEquals('RSSMRA45L01F205Z', $taxCode->value());
    }

    /** @test */
    public function it_rejects_invalid_format()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Italian tax code format');

        TaxCode::fromString('INVALID');
    }

    /** @test */
    public function it_rejects_too_short()
    {
        $this->expectException(InvalidArgumentException::class);

        TaxCode::fromString('RSSMRA45L01F205'); // 15 chars instead of 16
    }

    /** @test */
    public function it_rejects_too_long()
    {
        $this->expectException(InvalidArgumentException::class);

        TaxCode::fromString('RSSMRA45L01F205ZZ'); // 17 chars
    }

    /** @test */
    public function it_checks_equality()
    {
        $taxCode1 = TaxCode::fromString('RSSMRA45L01F205Z');
        $taxCode2 = TaxCode::fromString('rssmra45l01f205z'); // Lowercase
        $taxCode3 = TaxCode::fromString('VRDLGU50M01F205X');

        $this->assertTrue($taxCode1->equals($taxCode2));
        $this->assertFalse($taxCode1->equals($taxCode3));
    }
}
