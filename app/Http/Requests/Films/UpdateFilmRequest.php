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
     * Разрешает только модераторам делать данный запрос.
     * Правила доступа регулируются также миддлварами и гейтами в роутах
     *
     * @return bool
     */
    public function authorize(): bool
    {
//        return true;
        return $this->user() && $this->user()->isModerator();
    }

    /**
     * Правила валидации входящих данных.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $filmId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
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
//            'genre.*' => ['string'],
            'genre.*' => ['exists:genres,id'],
//            'run_time' => ['sometimes', 'integer'],
//            'released' => ['sometimes', 'integer'],
//            'imdb_id' => [
//                'sometimes',
//                'string',
//                'regex:/^tt\d{7,}$/',
//                Rule::unique('films', 'imdb_id')->ignore($this->route('id')),
//            ],
//            'status' => [
//                'sometimes',
//                'string',
//                Rule::in(['pending', 'on moderation', 'ready']),
//            ],

            'run_time' => ['sometimes', 'integer', 'min:1'],
            'released' => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'imdb_id' => [
                'sometimes',
                'string',
                'regex:/^tt\d{7,8}$/',
                Rule::unique('films')->ignore($filmId)
            ],
            'status' => ['sometimes', 'string', 'in:pending,moderate,ready'],
        ];
    }

    public function messages(): array
    {
        return [
            'imdb_id.required' => 'IMDB ID обязателен для заполнения',
            'imdb_id.regex' => 'Неверный формат IMDB ID',
            'imdb_id.unique' => 'Фильм с таким IMDB ID уже существует',
            'status.in' => 'Статус должен быть одним из: pending, moderate, ready',
            'genre.*.exists' => 'Указанный жанр не существует',
        ];
    }
}
