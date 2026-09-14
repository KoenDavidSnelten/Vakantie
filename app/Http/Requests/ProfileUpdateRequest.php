<?php

namespace App\Http\Requests;

use App\Enums\AvatarColor;
use App\Enums\AvatarFrame;
use App\Enums\AvatarSymbol;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Nullable en niet required: wie deze velden niet meestuurt houdt
            // gewoon de avatar die hij had, in plaats van een foutmelding.
            'avatar_symbol' => ['nullable', Rule::enum(AvatarSymbol::class)],
            'avatar_color' => ['nullable', Rule::enum(AvatarColor::class)],
            'avatar_frame' => ['nullable', Rule::enum(AvatarFrame::class)],
        ];
    }
}
