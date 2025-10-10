<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $uniqueEmailRule = Rule::unique('users', 'email');
        return [
            'email' => ['required', 'email:rfc,dns', 'max:255', $uniqueEmailRule],
            'password' => 'required|min:6|pwned|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/', // at-least 1 Uppercase, 1 Lowercase, 1 Numeric and 1 special character
            'password_confirmation' => 'required|min:6',
            'name' => 'required|max:255',
        ];
    }
}
