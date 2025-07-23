<?php

namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExceptionResponse extends Fail
{

    public int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * ExceptionResponse constructor.
     *
     * @param Throwable $exception
     * @param int|null $code
     */
    public function __construct(protected Throwable $exception, int $code = null)
    {
        parent::__construct([], $exception->getMessage(), $code ?? $this->getCode());
    }

    /**
     * Преобразование возвращаемых данных к массиву.
     *
     * @return array
     */
    protected function prepareData(): array
    {
        return [
            'exception' => [
                'name' => $this->getExceptionClassName(),
            ],
        ];
    }

    /**
     * Получение имени класса.
     *
     * @return string
     * @throws \ReflectionException
     */
    private function getExceptionClassName()
    {
        return (new \ReflectionClass($this->exception))->getShortName();
    }

    private function getCode()
    {
        return method_exists($this->exception, 'getStatusCode') ? $this->exception->getStatusCode() : $this->statusCode;
    }
}
