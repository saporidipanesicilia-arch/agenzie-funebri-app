<?php

namespace App\Domain\ValueObjects;

/**
 * Ceremony Type Value Object (Enum)
 *
 * Valid types:
 * - burial: Traditional burial in cemetery
 * - cremation: Cremation of remains
 * - entombment: Placement in above-ground crypt/mausoleum
 *
 * Immutable.
 */
enum CeremonyType: string
{
    case BURIAL = 'burial';
    case CREMATION = 'cremation';
    case ENTOMBMENT = 'entombment';

    /**
     * Check if ceremony type requires cemetery grave
     */
    public function requiresGrave(): bool
    {
        return $this === self::BURIAL;
    }

    /**
     * Check if ceremony type requires crematorium
     */
    public function requiresCrematorium(): bool
    {
        return $this === self::CREMATION;
    }

    /**
     * Check if ceremony type requires mausoleum/crypt
     */
    public function requiresMausoleum(): bool
    {
        return $this === self::ENTOMBMENT;
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::BURIAL => 'Sepoltura',
            self::CREMATION => 'Cremazione',
            self::ENTOMBMENT => 'Tumulazione',
        };
    }

    /**
     * Get typical products associated with this ceremony type
     */
    public function typicalProducts(): array
    {
        return match ($this) {
            self::BURIAL => ['coffin', 'burial_permit', 'grave_opening'],
            self::CREMATION => ['coffin', 'cremation_permit', 'urn'],
            self::ENTOMBMENT => ['coffin', 'crypt_opening'],
        };
    }
}
