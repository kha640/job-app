<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class ApplyJobVacancyRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume_option' => 'required|string',
            'resume_file' => 'required_if:resume_option,new_resume|file|mimes:pdf|max:5120',
        ];
        // 5120 ---> 5MB
    }

    #[Override]
    public function messages()
    {
        return [
            'resume_option' => 'P;ease select a resume option',
            'resume_file.required' => 'The resume file is required',
            'resume_file.file' => 'The resume file must be a file',
        ];
    }
}
