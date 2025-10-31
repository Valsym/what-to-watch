<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $this->getUniqRule(),
            ],
            'password' => [
                $this->getPasswordRequiredRule(),
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed'
            ],
            'file' => 'nullable|file|image|max:10240',
        ];

        return $rules;
    }

    private function getUniqRule()
    {
        $rule = Rule::unique(User::class);

        if ($this->isMethod('patch') && auth()->check()) {
            return $rule->ignore(auth()->id());
        }

        return $rule;
    }

    private function getPasswordRequiredRule(): string
    {
        return $this->isMethod('patch') ? 'sometimes' : 'required';
    }
}
