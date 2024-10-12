<?php

namespace Minigyima\Aurora\Models;

use Illuminate\Validation\ValidationException;
use JsonSerializable;
use Override;

readonly class AuroraValidationError implements JsonSerializable
{
    public array $errors;
    public int $status;

    public function __construct(ValidationException $exception)
    {
        $this->errors = $exception->errors();
        $this->status = $exception->status;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return $this->errors;
    }
}
