<?php

namespace App\Http\Requests\Films;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Класс запроса для редактирования фильма.
 *
 * Валидирует данные, которые приходят при редактировании фильма через API.
 */
class UpdateFilmRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'poster_image' => ['sometimes', 'string', 'max:255'],
            'preview_image' => ['sometimes', 'string', 'max:255'],
            'background_image' => ['sometimes', 'string', 'max:255'],
            'background_color' => ['sometimes', 'string', 'max:9'],
            'video_link' => ['sometimes', 'string', 'max:255'],
            'preview_video_link' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:1000'],
            'director' => ['sometimes', 'string', 'max:255'],
            'starring' => ['sometimes', 'array'],
            'starring.*' => ['string'],
            'genre' => ['sometimes', 'array'],
            'genre.*' => ['string'],
            'run_time' => ['sometimes', 'integer'],
            'released' => ['sometimes', 'integer'],
            'imdb_id' => [
                'sometimes',
                'string',
                'regex:/^tt\d{7,}$/',
                Rule::unique('films', 'imdb_id')->ignore($this->route('id')),
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(['pending', 'on moderation', 'ready']),
            ],
        ];
    }
}
