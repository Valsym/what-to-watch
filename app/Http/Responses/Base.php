<?php
//namespace App\Http\Responses;
//
//use Illuminate\Contracts\Support\Arrayable;
//use Illuminate\Contracts\Support\Responsable;
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Request;
//use JsonSerializable;
//use Override;
//use Symfony\Component\HttpFoundation\Response;

//abstract class Base implements Responsable
//{
//    public function __construct(protected mixed $data = [], public int $statusCode = Response::HTTP_OK)
//    {
//    }
//
//    /**
//     * Преобразует объект ответа в HTTP-ответ в формате JSON.
//     *
//     * @param Request $request
//     *
//     * @return JsonResponse
//     */
//    #[Override]
//    public function toResponse($request): JsonResponse|Response
//    {
//        return response()->json($this->makeResponseData(), $this->statusCode);
//    }
//
//    /**
//     * Преобразование данных к массиву
//     *
//     * @return array
//     */
//    protected function prepareData(): array
//    {
//        if ($this->data instanceof Arrayable) {
//            return $this->data->toArray();
//        }
//
//        if ($this->data instanceof JsonSerializable) {
//            return $this->data->jsonSerialize();
//        }
//
//        return $this->data;
//    }
//
//    /**
//     * Формирование содержимого ответа
//     *
//     * @return array|null
//     */
//    abstract protected function makeResponseData(): ?array;
//}
namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use JsonSerializable;

abstract class Base implements Responsable
{
    public function __construct(
        protected mixed $data = [],
        public int $statusCode = Response::HTTP_OK,
    ) {
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        return response()->json($this->makeResponseData(), $this->statusCode);
    }

    /**
     * Преобразование возвращаемых данных к массиву.
     *
     * @return array
     */
    protected function prepareData(): array|string
    {
        if ($this->data instanceof Arrayable) {
            return $this->data->toArray();
        }

        if ($this->data instanceof JsonSerializable) {
            return $this->data->jsonSerialize();
        }

        return $this->data;
    }

    /**
     * Формирование содержимого ответа.
     *
     * @return array|null
     */
    abstract protected function makeResponseData(): ?array;
}
