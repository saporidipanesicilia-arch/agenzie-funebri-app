<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Italian Tax Code (Codice Fiscale) Value Object
 *
 * Format: RSSMRA45L01F205Z (16 characters)
 * - 6 letters: surname + name
 * - 2 digits: birth year
 * - 1 letter: birth month
 * - 2 digits: birth day + gender
 * - 4 chars: municipality code + check digit
 *
 * Immutable and self-validating.
 */
final readonly class TaxCode
{
    private const PATTERN = '/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/';

    private function __construct(
        private string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (!preg_match(self::PATTERN, $normalized)) {
            throw new InvalidArgumentException(
                "Invalid Italian tax code format: {$value}. " .
                "Expected format: RSSMRA45L01F205Z (16 alphanumeric characters)"
            );
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
