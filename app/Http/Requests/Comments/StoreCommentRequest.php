<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'text' => 'required|string|min:50|max:400',
            'rating' => 'required|min:1|max:10',
            'comment_id' => 'nullable|exists:comments,id'
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
            'text.required' => 'Текст комментария обязателен',
            'text.min' => 'Комментарий должен содержать не менее 50 символов',
            'text.max' => 'Комментарий должен содержать не более 400 символов',
            'rating.required' => 'Оценка обязательна',
            'rating.min' => 'Оценка должна быть не менее 1',
            'rating.max' => 'Оценка должна быть не более 10',
            'comment_id.exists' => 'Родительский комментарий не найден',
            'film_id.required' => 'Не указан фильм',
            'film_id.exists' => 'Указанный фильм не найден'
        ];
    }
}
