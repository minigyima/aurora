<?php

namespace Minigyima\Aurora\Support\Response;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use JsonSerializable;
use stdClass;

/**
 * AuroraResponse - Response class for Aurora
 *  - Loosely follows the JSend specification
 * @package Minigyima\Aurora\Support\Response
 */
class AuroraResponse extends JsonResponse
{
    /**
     * The message of the response
     * @var string
     */
    private string $message = '';

    /**
     * The status of the response
     * @var AuroraResponseStatus
     */
    private AuroraResponseStatus $status;

    /**
     * The data of the response
     * @var array
     */
    private array $_data = [];

    /**
     * AuroraResponse constructor.
     * @param array|stdClass|Jsonable|JsonSerializable|Arrayable|string|Spatie\QueryBuilder\QueryBuilder $data
     * @param int $statusCode
     * @param array $headers
     * @param int $encodingOptions
     * @param bool $json
     * @param AuroraResponseStatus $status
     * @param string $message
     */
    public function __construct(
        array|stdClass|Jsonable|JsonSerializable|Arrayable|string|LengthAwarePaginator $data = [],
        int $statusCode = 200,
        array $headers = [],
        int $encodingOptions = 0,
        bool $json = false,
        AuroraResponseStatus $status = AuroraResponseStatus::SUCCESS,
        string $message = 'Success',
        private int|null $currentPage = null,
        private int|null $perPage = null,
        private int|null $totalRecords = null,
        private int|null $pages = null
    ) {
        $this->encodingOptions = $encodingOptions;

        if ($json) {
            $data = json_decode($data, true);
        }

        $this->_data = $this->transformData($data);

        if ($status === AuroraResponseStatus::FAIL) {
            $body = [
                'status' => $status,
                'message' => $message,
                'errors' => $this->_data,
            ];
        } else {
            $body = [
                'status' => $status,
                'message' => $message,
                'data' => $this->_data,
            ];
        }

        if ($this->currentPage !== null) {
            $body['page'] = $this->currentPage;
        }

        if ($this->perPage !== null) {
            $body['perPage'] = $this->perPage;
        }

        if ($this->totalRecords !== null) {
            $body['totalRecords'] = $this->totalRecords;
        }

        if ($this->pages !== null) {
            $body['pages'] = $this->pages;
        }

        parent::__construct($body, $statusCode, $headers, false);

        $this->message = $message;
        $this->status = $status;
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    /**
     * Transform the data into an array
     * @param array|stdClass|Jsonable|JsonSerializable|Arrayable|LengthAwarePaginator $data
     * @return array
     */
    private function transformData(
        array|stdClass|Jsonable|JsonSerializable|Arrayable|LengthAwarePaginator $data
    ): array {
        if (
            !is_array($data) &&
            !($data instanceof Jsonable) &&
            !($data instanceof JsonSerializable) &&
            !($data instanceof Arrayable) &&
            !($data instanceof stdClass) &&
            !($data instanceof LengthAwarePaginator)
        ) {
            throw new InvalidArgumentException(
                'Data must be an array, an instance of Jsonable, JsonSerializable, Arrayable, or stdClass'
            );
        }

        if ($data instanceof LengthAwarePaginator) {
            $this->currentPage = $data->currentPage();
            $this->perPage = $data->perPage();
            $this->totalRecords = $data->total();
            $this->pages = $data->lastPage();
            return $data->items();
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof Jsonable) {
            return json_decode($data->toJson(), true);
        }

        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        return (array) $data;
    }

    /**
     * Set the status of the response
     * @param AuroraResponseStatus $status
     * @return $this
     */
    public function json_status(AuroraResponseStatus $status): AuroraResponse
    {
        $this->status = $status;
        $this->sync();

        return $this;
    }

    /**
     * Synchronize the data with the response
     * @return void
     */
    private function sync(): void
    {
        if ($this->status === AuroraResponseStatus::FAIL) {
            $this->setData([
                'status' => $this->status,
                'message' => $this->message,
                'errors' => $this->_data,
            ]);
        } else {
            $this->setData([
                'status' => $this->status,
                'message' => $this->message,
                'data' => $this->_data,
            ]);
        }
    }

    /**
     * Set the message of the response
     * @param string $message
     * @return $this
     */
    public function message(string $message): AuroraResponse
    {
        $this->message = $message;
        $this->sync();

        return $this;
    }
}
