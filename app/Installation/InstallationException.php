<?php

namespace App\Installation;

use RuntimeException;

class InstallationException extends RuntimeException
{
    public function __construct(public readonly string $reason = 'unavailable', public readonly int $status = 503)
    {
        parent::__construct($reason);
    }
}
