<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'professional_title' => ['nullable', 'string', 'max:160'],
            'professional_summary' => ['nullable', 'string', 'max:4000'],
            'template_id' => ['nullable', Rule::exists('resume_templates', 'id')->where('is_active', true)],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:80'],
            'spacing' => ['nullable', 'in:compact,comfortable,spacious'],
        ];
    }
}
