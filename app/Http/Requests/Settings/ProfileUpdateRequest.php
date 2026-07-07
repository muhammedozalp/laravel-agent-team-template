<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->profileRules($this->user()->id);

        // Changing the account email is a takeover-grade action: a hijacked
        // session must not be able to swap it silently.
        if ($this->input('email') !== $this->user()->email) {
            $rules['current_password'] = ['required', 'current_password:web'];
        }

        return $rules;
    }
}
