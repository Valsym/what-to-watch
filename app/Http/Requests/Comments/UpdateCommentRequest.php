<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'text' => 'required|string|min:50|max:400',
            'rating' => 'sometimes|integer|min:1|max:10',
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
            'rate.min' => 'Оценка должна быть не менее 1',
            'rate.max' => 'Оценка должна быть не более 10',
        ];
    }
}
