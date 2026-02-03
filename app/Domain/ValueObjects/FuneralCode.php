<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Funeral Code Value Object
 *
 * Format: FUN-YYYY-NNN
 * - FUN: Fixed prefix
 * - YYYY: Year (4 digits)
 * - NNN: Sequential number (3 digits, zero-padded)
 *
 * Example: FUN-2026-001, FUN-2026-042
 *
 * Immutable and self-validating.
 */
final readonly class FuneralCode
{
    private const PATTERN = '/^FUN-\d{4}-\d{3}$/';

    private function __construct(
        private string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (!preg_match(self::PATTERN, $normalized)) {
            throw new InvalidArgumentException(
                "Invalid funeral code format: {$value}. " .
                "Expected format: FUN-YYYY-NNN (e.g., FUN-2026-001)"
            );
        }

        return new self($normalized);
    }

    public static function generate(int $year, int $sequence): self
    {
        if ($year < 2000 || $year > 2100) {
            throw new InvalidArgumentException("Year must be between 2000 and 2100");
        }

        if ($sequence < 1 || $sequence > 999) {
            throw new InvalidArgumentException("Sequence must be between 1 and 999");
        }

        $code = sprintf('FUN-%d-%03d', $year, $sequence);
        return new self($code);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function year(): int
    {
        // Extract year from FUN-2026-001
        return (int) substr($this->value, 4, 4);
    }

    public function sequence(): int
    {
        // Extract sequence from FUN-2026-001
        return (int) substr($this->value, 9, 3);
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
