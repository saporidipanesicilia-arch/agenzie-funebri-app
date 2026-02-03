<?php

namespace App\Domain\Exceptions;

use Exception;

/**
 * Thrown when date validation fails.
 *
 * Enforces chronological consistency rules for funeral dates.
 */
class InvalidFuneralDateException extends Exception
{
    public static function ceremonyBeforeDeath(): self
    {
        return new self(
            'Ceremony date cannot be before the death date.'
        );
    }

    public static function deathInFuture(): self
    {
        return new self(
            'Death date cannot be in the future.'
        );
    }
}
