<?php

namespace App\Http\Requests\Api\Funeral;

use Illuminate\Foundation\Http\FormRequest;

/**
 * HTTP validation for saving wizard draft.
 */
class SaveFuneralDraftHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'draft_id' => ['nullable', 'integer', 'exists:funeral_drafts,id'],
            'wizard_data' => ['required', 'array'],
            'current_step' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
