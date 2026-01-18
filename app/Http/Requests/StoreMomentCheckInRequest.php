<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMomentCheckInRequest extends FormRequest
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
        return [
            'mood' => ['required', 'string', 'in:🙂,😐,😴,😣,😄'],
            'feeling_level' => ['nullable', 'integer', 'min:1', 'max:10'],
            'tags' => ['nullable', 'array', 'max:2'],
            'tags.*' => ['string', 'in:🏃‍♂️,🍺,😴,💼,🤒,❤️'],
            'occurred_at' => ['nullable', 'date'],
            'symptoms' => ['nullable', 'array'],
            'symptoms.*.code' => ['required_with:symptoms.*.severity', 'string'],
            'symptoms.*.severity' => ['required_with:symptoms.*.code', 'integer', 'min:0', 'max:10'],
            'symptoms.*.occurred_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mood.required' => 'Mood là bắt buộc.',
            'mood.in' => 'Mood không hợp lệ.',
            'feeling_level.integer' => 'Feeling level phải là số từ 1 đến 10.',
            'feeling_level.min' => 'Feeling level phải từ 1 đến 10.',
            'feeling_level.max' => 'Feeling level phải từ 1 đến 10.',
            'tags.array' => 'Tags phải là mảng.',
            'tags.max' => 'Tối đa 2 tags.',
        ];
    }
}
