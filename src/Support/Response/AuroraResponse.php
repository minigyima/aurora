<?php

namespace Minigyima\Aurora\Support\Response;

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
        array|stdClass|Jsonable|JsonSerializable|Arrayable|string|Spatie\QueryBuilder\QueryBuilder $data = [],
        int                                                       $statusCode = 200,
        array                                                     $headers = [],
        int                                                       $encodingOptions = 0,
        bool                                                      $json = false,
        AuroraResponseStatus                                      $status = AuroraResponseStatus::SUCCESS,
        string                                                    $message = 'Success',
        private int|null                                          $currentPage = null,
        private int|null                                          $perPage = null,
        private int|null                                          $totalRecords = null,
        private int|null                                          $pages = null,


    ) {
        $this->encodingOptions = $encodingOptions;

        if ($json) {
            $data = json_decode($data, true);
        }

        $this->_data = $this->transformData($data);

        $body = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];

        if ($currentPage !== null) {
            $body['page'] = $currentPage;
        }

        if ($perPage !== null) {
            $body['perPage'] = $perPage;
        }

        if ($totalRecords !== null) {
            $body['totalRecords'] = $totalRecords;
        }

        if ($pages !== null) {
            $body['pages'] = $pages;
        }

        parent::__construct($body, $statusCode, $headers, false);

        $this->message = $message;
        $this->status = $status;
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    /**
     * Transform the data into an array
     * @param array|stdClass|Jsonable|JsonSerializable|ArrayablSpatie\QueryBuilder\QueryBuilder $data
     * @return array
     */
    private function transformData(array|stdClass|Jsonable|JsonSerializable|Arrayable|Spatie\QueryBuilder\QueryBuilder $data): array
    {
        if (
            ! is_array($data) &&
            ! ($data instanceof Jsonable) &&
            ! ($data instanceof JsonSerializable) &&
            ! ($data instanceof Arrayable) &&
            ! ($data instanceof stdClass)
        ) {
            throw new InvalidArgumentException(
                'Data must be an array, an instance of Jsonable, JsonSerializable, Arrayable, or stdClass',
            );
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

        if ($data instanceof Spatie\QueryBuilder\QueryBuilder) {
            $this->currentPage = $data->currentPage();
            $this->perPage = $data->perPage();
            $this->totalRecords = $data->total();
            $this->pages = $data->lastPage();
            return $data->items();
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
        $this->setData([
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->_data,
        ]);
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
