<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:160'],
            'title' => ['required', 'string', 'max:160'],
            'job_url' => ['nullable', 'url', 'max:500'],
            'location' => ['nullable', 'string', 'max:160'],
            'salary' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:30'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
