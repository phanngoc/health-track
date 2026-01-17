<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSymptomLogRequest extends FormRequest
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
            'symptom_code' => ['required', 'string', 'exists:symptoms,code'],
            'severity' => ['required', 'integer', 'min:0', 'max:10'],
            'occurred_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'in:checkin,manual,auto'],
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
            'symptom_code.required' => 'Mã triệu chứng là bắt buộc.',
            'symptom_code.exists' => 'Mã triệu chứng không tồn tại.',
            'severity.required' => 'Mức độ nghiêm trọng là bắt buộc.',
            'severity.integer' => 'Mức độ nghiêm trọng phải là số từ 0 đến 10.',
            'severity.min' => 'Mức độ nghiêm trọng phải từ 0 đến 10.',
            'severity.max' => 'Mức độ nghiêm trọng phải từ 0 đến 10.',
        ];
    }
}
