<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
//    public function authorize(): bool
//    {
//        return false;
//    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'text' => 'sometimes|string|min:10|max:400',
            'rate' => 'integer|min:1|max:10',
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
            'text.min' => 'Комментарий должен содержать не менее 50 символов',
            'text.max' => 'Комментарий должен содержать не более 400 символов',
            'rate.min' => 'Оценка должна быть не менее 1',
            'rate.max' => 'Оценка должна быть не более 10',
            'comment_id.exists' => 'Родительский комментарий не найден',
        ];
    }
}
