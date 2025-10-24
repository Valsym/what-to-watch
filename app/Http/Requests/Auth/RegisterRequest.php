<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Сообщения об ошибках валидации.
     *
     * @return array
     */
    public  function rules(): array
    {

        $rules = [
            'name' => 'string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $this->getUniqRule(),
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'file' => 'nullable|file|image|max:10240',
        ];

        return $rules;
    }

    private function getUniqRule()
    {
        $rule = Rule::unique(User::class);

        if ($this->isMethod('PATCH') && Auth::check()) {
            return $rule->ignore(Auth::user());
        }

        return $rule;
    }

    private function getPasswordRequiredRule() : string
    {
        return $this->isMethod('PATCH') ? 'sometimes' : 'required';
    }
}
