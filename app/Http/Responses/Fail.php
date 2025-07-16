<?php
namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\Response;

class Fail extends Base
{
    public int $statusCode = Response::HTTP_BAD_REQUEST;

    /**
     * @param mixed $data
     * @param string|null $message
     * @param $code
     */
    public function __construct(
        protected mixed $data = [], protected ?string $message = null,
        $code = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct([], $code);
    }

    /**
     * Формирование содержимого ответа
     *
     * @return array
     */
    protected function makeResponseData(): array
    {
        return [
            'message' => $this->message,
            'error' => $this->prepareData()
        ];

    }
}
