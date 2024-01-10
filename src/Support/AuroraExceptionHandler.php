<?php

namespace Minigyima\Aurora\Support;

use Illuminate\Foundation\Exceptions\Handler;
use Minigyima\Aurora\Traits\AuroraExceptionFormatter;
use Throwable;

/**
 * AuroraExceptionHandler - Exception handler for Aurora
 *  - The stock Laravel implementation, but with the AuroraExceptionFormatter trait
 * @package Minigyima\Aurora\Support
 */
class AuroraExceptionHandler extends Handler
{
    use AuroraExceptionFormatter;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
