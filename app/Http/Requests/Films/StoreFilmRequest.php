<?php

namespace App\Http\Requests\Films;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 *
 * Класс запроса для создания нового фильма.
 *
 * Валидирует данные, которые приходят при создании фильма через API.
 */
class StoreFilmRequest extends FormRequest
{
    /**
     * Разрешает всем пользователям делать данный запрос.
     *  Правила доступа регулируются миддлварами и гейтами в роутах
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации входящих данных.
     *
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'imdb_id' => ['required', 'string', 'unique:films,imdb_id'],
        ];
    }

    /**
     * Подготовка данных перед валидацией.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->replace([
            'imdb_id' => $this->input('imdb_id')
        ]);
    }

    /**
     * Возвращаем только разрешенные данные.
     *
     * @param $key
     * @param $default
     * @return array
     */
    public function validated($key = null, $default = null): array
    {
        return [
            'imdb_id' => $this->input('imdb_id'),
            'status' => 'pending'
        ];
    }

    /**
     * Сообщения об ошибках валидации.
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'imdb_id.required' => 'IMDb ID обязателен для заполнения',
            'imdb_id.unique' => 'Фильм с таким IMDb ID уже существует',
            'imdb_id.regex' => 'Некорректный формат IMDb ID (должен начинаться с tt и содержать 7-8 цифр)'
        ];
    }
}
