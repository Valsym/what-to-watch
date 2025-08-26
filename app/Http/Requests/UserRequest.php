<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserRequest extends FormRequest
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

    public  function rules(): array
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
                'string',
                'min:8'
            ],
            'password_confirmation' => [
                $this->getPasswordRequiredRule(),
                'string',
                'min:8'
            ],
            'avatar' => 'nullable|file|image|max:10240',
//            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg|max:10240'
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
