<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Cemetery Concession Period Value Object
 *
 * Valid periods (in years):
 * - 10 years (standard short)
 * - 20 years (standard medium)
 * - 30 years (standard long)
 * - 99 years (perpetual/indefinite)
 *
 * Immutable.
 */
final readonly class ConcessionPeriod
{
    private const VALID_PERIODS = [10, 20, 30, 99];

    private function __construct(
        private int $years
    ) {
    }

    public static function fromYears(int $years): self
    {
        if (!in_array($years, self::VALID_PERIODS, true)) {
            throw new InvalidArgumentException(
                "Invalid concession period: {$years} years. " .
                "Valid periods: 10, 20, 30, 99 (perpetual)"
            );
        }

        return new self($years);
    }

    public static function standard(): self
    {
        return new self(20); // Default 20 years
    }

    public static function perpetual(): self
    {
        return new self(99);
    }

    public function years(): int
    {
        return $this->years;
    }

    public function isPerpetual(): bool
    {
        return $this->years === 99;
    }

    /**
     * Calculate expiration date from given start date
     *
     * @return \DateTimeImmutable|null Null if perpetual
     */
    public function calculateExpiration(\DateTimeImmutable $startDate): ?\DateTimeImmutable
    {
        if ($this->isPerpetual()) {
            return null; // No expiration
        }

        return $startDate->modify("+{$this->years} years");
    }

    public function equals(self $other): bool
    {
        return $this->years === $other->years;
    }

    public function __toString(): string
    {
        return $this->isPerpetual() ? 'Perpetual' : "{$this->years} years";
    }
}
