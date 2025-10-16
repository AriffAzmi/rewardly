<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all authenticated users to import vouchers
        // You can add more specific authorization logic here if needed
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:20480', // 20MB in kilobytes
            ],
            'merchant_id' => [
                'nullable',
                'string',
                'exists:merchants,id'
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be an Excel file (xlsx, xls) or CSV file.',
            'file.max' => 'The file size must not exceed 20MB.',
            'merchant_id.exists' => 'The selected merchant does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'file' => 'Excel file',
            'merchant_id' => 'merchant',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::warning('Voucher import validation failed', [
            'errors' => $validator->errors()->toArray(),
            'user_id' => auth()->id(),
            'user_login' => 'AriffAzmi',
            'timestamp' => '2025-10-14 05:53:52'
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Log the import attempt
        \Illuminate\Support\Facades\Log::info('Voucher import validation started', [
            'has_file' => $this->hasFile('file'),
            'file_name' => $this->hasFile('file') ? $this->file('file')->getClientOriginalName() : null,
            'file_size' => $this->hasFile('file') ? $this->file('file')->getSize() : null,
            'merchant_id' => $this->input('merchant_id'),
            'user_id' => auth()->id(),
            'user_login' => 'AriffAzmi',
            'timestamp' => '2025-10-14 05:53:52'
        ]);
    }
}