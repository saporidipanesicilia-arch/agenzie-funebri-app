<?php

namespace App\Domain\Exceptions;

use Exception;

/**
 * Thrown when attempting to create a funeral without timeline templates.
 *
 * Timeline templates are required to initialize the workflow.
 * Each agency must have at least one active timeline step.
 */
class TimelineTemplateNotFoundException extends Exception
{
    public function __construct(int $agencyId)
    {
        parent::__construct(
            "No timeline templates found for agency {$agencyId}. " .
            "Please configure timeline steps before creating funerals."
        );
    }
}
