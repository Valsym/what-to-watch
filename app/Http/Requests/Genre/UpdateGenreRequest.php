<?php

namespace App\Http\Requests\Genre;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isModerator();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:genres,name,' . $this->route('genre'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название жанра обязательно для заполнения',
            'name.string' => 'Название жанра должно быть строкой',
            'name.max' => 'Название жанра не должно превышать 255 символов',
            'name.unique' => 'Жанр с таким названием уже существует',
        ];
    }
}
