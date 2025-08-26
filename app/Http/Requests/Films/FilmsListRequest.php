<?php

namespace App\Http\Requests\Films;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Класс запроса для получения списка фильмов.
 *
 * Валидирует данные, которые приходят при запросе списка фильмов через API.
 */
class FilmsListRequest extends FormRequest
{
    /**
     * Разрешает всем пользователям делать данный запрос.
     * Правила доступа регулируются миддлварами и гейтами в роутах
     *
     * @return bool
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации входящих данных.
     *
     * @return array<string, ValidationRule|array|string>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'genre' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string', 'in:pending,on moderation,ready'],
            'order_by' => ['sometimes', 'string', 'in:released,rating'],
            'order_to' => ['sometimes', 'string', 'in:asc,desc'],
            'search' => ['sometimes', 'string'],
        ];
    }
}
