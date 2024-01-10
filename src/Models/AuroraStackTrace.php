<?php

namespace Minigyima\Aurora\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonSerializable;
use Override;
use Throwable;

/**
 * AuroraStackTrace - Model for Aurora's stack trace
 * @package Minigyima\Aurora\Models
 */
readonly class AuroraStackTrace implements JsonSerializable
{
    public string $file;
    public int $line;
    public string $function;
    public Collection $trace;
    public string $message;
    public string $exceptionClass;
    public int $statusCode;
    public int $code;

    /**
     * AuroraStackTrace constructor.
     * @param Throwable $exception
     */
    public function __construct(public Throwable $exception)
    {
        $this->file = $exception->getFile();
        $this->exceptionClass = get_class($exception);
        $this->line = $exception->getLine();
        $this->function = $exception->getTrace()[0]['function'];
        $this->trace = collect($exception->getTrace())->map(fn($trace) => Arr::except($trace, ['args']));
        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
        if (method_exists($exception, 'getStatusCode')) {
            $this->statusCode = $exception->getStatusCode();
        } else {
            $this->statusCode = 500;
        }
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'message' => $this->message,
            'exception' => $this->exceptionClass,
            'file' => $this->file,
            'line' => $this->line,
            'function' => $this->function,
            'code' => $this->code,
            'trace' => $this->trace,
        ];
    }
}
