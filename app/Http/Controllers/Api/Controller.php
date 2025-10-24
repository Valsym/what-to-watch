<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Validator;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\Success;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($data, ?int $code = Response::HTTP_OK)
    {
        return new Success($data, $code);
    }

    /**
     * Возвращает ошибку с сообщением и HTTP-статусом.
     */
    protected function error(
        string $message,
        array|Validator $errors = [],
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): ErrorResponse {
        return new ErrorResponse($message, $errors, $statusCode);
    }
}
