<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'template_id' => ['nullable', 'exists:resume_templates,id'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:80'],
            'spacing' => ['nullable', 'in:compact,comfortable,spacious'],
        ];
    }
}
