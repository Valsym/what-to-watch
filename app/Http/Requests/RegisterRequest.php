<?php

namespace App\Http\Requests;

use App\Models\User;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

    public  function rules(): array
    {

        $rules = [
            'name' => 'string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
//                self::getUniqRule(),
                $this->getUniqRule(),
            ],
            'password' => [
                'required',
                'string',
                'min:8'
            ],
            'file' => 'nullable|file|image|max:10240',
        ];

        return $rules;
    }

    private function getUniqRule()
    {
        $t = 99;
        $t++;
        $rule = Rule::unique(User::class);

//        if (self::isMethod('PATCH') && Auth::check()) {
//            return $rule->ignore(Auth::user());
//        }

        return $rule;
    }

    private static function getPasswordRequiredRule() : string
    {
        $t = 9;
        $t++;
        return self::isMethod('PATCH') ? 'sometimes' : 'required';
    }
}
