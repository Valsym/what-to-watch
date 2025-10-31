<?php

namespace App\Http\Requests\Films;

use Illuminate\Foundation\Http\FormRequest;

class SetPromoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isModerator();
    }

    public function rules(): array
    {
        return [
            // Валидация не нужна, так как ID передается в URL
        ];
    }

    public function messages(): array
    {
        return [
            // Сообщения об ошибках
        ];
    }
}
