<?php

namespace App\Http\Requests;

use App\Enums\SurveyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $survey = $this->route('survey');
        if($this->user()->id !== $survey->user_id) {
            return false;
        }
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
            'title' => 'required|string|max:1000',
            'image' => 'nullable|string',
            'user_id' => 'exists:users,id',
            'status' => ['required', new Enum(SurveyStatus::class)],
            'description' => 'nullable|string',
            'expire_date' => 'nullable|date|after:tomorrow',
            'questions' => 'array'
        ];
    }
}
