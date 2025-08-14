<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'text' => 'required|string|min:50|max:400',
            'rate' => 'integer|min:1|max:10',
            'comment_id' => 'nullable|exists:comments,id'
        ];
    }

    public function messages(): array
    {
        return [
            'comment_id.exists' => 'Родительский комментарий не найден'
        ];
    }
}
