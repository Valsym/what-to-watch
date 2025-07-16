<?php
namespace App\Http\Responses;

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
