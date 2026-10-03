<?php

namespace App\Exceptions;

use RuntimeException;

class BusinessRuleException extends RuntimeException
{
    public const EMAIL_TAKEN = 'email_taken';
    public const VEHICLE_UNAVAILABLE = 'vehicle_unavailable';
    public const INVALID_TRANSITION = 'invalid_transition';
    public const RENTAL_CLOSED = 'rental_closed';
    public const INVALID_PERIOD = 'invalid_period';
    public const INVALID_REASON = 'invalid_reason';
    public const NOT_FOUND = 'not_found';
    public const FORBIDDEN = 'forbidden';

    public function __construct(
        private readonly string $domainCode,
        string $message = '',
    ) {
        parent::__construct($message);
    }

    public function domainCode(): string
    {
        return $this->domainCode;
    }
}
