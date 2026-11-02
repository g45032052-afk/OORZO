<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

/**
 * Thrown while materialising a surge rule when the dates it would occupy
 * overlap an existing surge in the same zone.
 *
 * Caught by SurgePriceController, which rolls the transaction back and returns
 * HTTP 409 — the status the create/edit form's AJAX handler listens for to pop
 * the "Surge Price setup Overlap!" modal. It is an expected validation outcome,
 * not a fault, so it is kept out of the error log.
 */
class SurgeConflictException extends Exception implements ShouldntReport
{
    protected $code = 409;

    public function report(): bool
    {
        return false;
    }
}
