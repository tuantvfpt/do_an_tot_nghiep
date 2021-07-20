<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'user_account' => [
                'required',
                Rule::unique('users')->ignore($this->id)
            ],
            'email' => [
                'required',
                Rule::unique('users')->ignore($this->id)
            ],
        ];
    }
    public function messages()
    {
        return [

            'user_account.required' => "Nhập tên user_account",
            'user_account.unique' => "User_account đã tồn tại ",
            'email.required' => "Nhập tên email",
            'logo.unique' => "email đã tồn tại"
        ];
    }
}
