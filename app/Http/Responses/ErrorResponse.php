<?php

namespace App\Http\Responses;

use AllowDynamicProperties;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Validator;
use Override;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

#[AllowDynamicProperties]
class ErrorResponse extends Base
{
    protected string $message;
    public int $statusCode = Response::HTTP_BAD_REQUEST;

    /**
     * @param string                    $message
     * @param array|Arrayable|Validator $errors
     * @param int                       $statusCode
     */
    public function __construct(
        string $message,
        protected array|Arrayable|Validator $errors = [],
        int $statusCode = Response::HTTP_BAD_REQUEST,
    ) {
        parent::__construct([], $statusCode);
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * Формирование содержимого ответа
     *
     * @return array
     */
    #[Override]
    protected function makeResponseData(): array
    {
        return [
            'message' => $this->message,
            'errors' => $this->errors ?: new stdClass(),
        ];
    }
}
