<?php

namespace Minigyima\Aurora\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Minigyima\Aurora\Models\AuroraStackTrace;
use Minigyima\Aurora\Models\AuroraValidationError;
use Minigyima\Aurora\Support\Response\AuroraResponseStatus;
use Throwable;
use function Minigyima\Aurora\Support\aurora_response;

/**
 * Trait for formatting exceptions
 * @package Minigyima\Aurora\Traits
 */
trait AuroraExceptionFormatter
{

    /**
     * Format the validation exception
     *
     * @param Request $request
     * @param ValidationException $exception
     * @return void
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $data = new AuroraValidationError($exception);
        return aurora_response(
            $data,
            $data->status,
            [],
            0,
            false,
            AuroraResponseStatus::FAIL,
            $exception->getMessage()
        );
    }

    /**
     * Format the exception
     *
     * @param Request $request
     * @param Throwable $e
     * @return void
     */
    protected function prepareJsonResponse($request, Throwable $exception)
    {
        $data = new AuroraStackTrace($exception);

        $headers = [];
        if (method_exists($exception, 'getHeaders')) {
            $headers = $exception->getHeaders();
        }

        return aurora_response(
            $data,
            $data->statusCode,
            $headers,
            0,
            false,
            AuroraResponseStatus::ERROR,
            $data->message
        );
    }
}
