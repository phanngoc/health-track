<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckInRequest extends FormRequest
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
            'checkin_date' => ['sometimes', 'date'],
            'overall_feeling' => ['nullable', 'integer', 'min:1', 'max:10'],
            'sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'overall_feeling.integer' => 'Cảm giác tổng thể phải là số từ 1 đến 10.',
            'overall_feeling.min' => 'Cảm giác tổng thể phải từ 1 đến 10.',
            'overall_feeling.max' => 'Cảm giác tổng thể phải từ 1 đến 10.',
            'sleep_hours.numeric' => 'Số giờ ngủ phải là số.',
            'sleep_hours.min' => 'Số giờ ngủ không thể nhỏ hơn 0.',
            'sleep_hours.max' => 'Số giờ ngủ không thể lớn hơn 24.',
            'symptoms.*.code.required_with' => 'Mỗi triệu chứng phải có mã.',
            'symptoms.*.severity.required_with' => 'Mỗi triệu chứng phải có mức độ nghiêm trọng.',
            'symptoms.*.severity.min' => 'Mức độ nghiêm trọng phải từ 0 đến 10.',
            'symptoms.*.severity.max' => 'Mức độ nghiêm trọng phải từ 0 đến 10.',
        ];
    }
}
