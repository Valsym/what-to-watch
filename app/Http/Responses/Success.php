<?php
namespace App\Http\Responses;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

//final class Success extends Base
//{
//    /**
//     * Формирование содержимого ответа
//     *
//     * @return ((mixed)[]|int|null|string)[]
//     *
//     * @psalm-return array{data: array<TValue|mixed>, current_page?: int, first_page_url?: string, next_page_url?: null|string, prev_page_url?: null|string, per_page?: int, total?: int}
//     */
//    #[Override]
//    protected function makeResponseData(): ?array
//    {
//        /**
//         * @var JsonResource|LengthAwarePaginator|array $items
//         */
//        if ($this->data instanceof LengthAwarePaginator) {
//            $items =
//                $this->data->items();
//
//            return [
//                'data' => $items,
//                'current_page' => $this->data->currentPage(),
//                'first_page_url' => $this->data->url(1),
//                'next_page_url' => $this->data->nextPageUrl(),
//                'prev_page_url' => $this->data->previousPageUrl(),
//                'per_page' => $this->data->perPage(),
//                'total' => $this->data->total(),
//            ];
//        }
//
//        if ($this->data instanceof JsonResource) {
//            return [
//                'data' => $this->data->resolve(),
//            ];
//        }
//
//        return [
//            'data' => $this->prepareData(),
//        ];
//    }
//}

class Success extends Base
{
    /**
     * Формирование содержимого ответа
     *
     * @return array|null
     */
    protected function makeResponseData(): ?array
    {
        return $this->data ? [
            'data' => $this->prepareData()
        ] : null;

    }
}
