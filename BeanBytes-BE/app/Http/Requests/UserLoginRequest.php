<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'emailPhone' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}
