<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTargetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'target_amount' => 'required|numeric|min:0.01',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'target_amount' => 'target amount',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'target_amount.min' => 'Target amount must be greater than 0.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $target = $this->route('target');
            
            if ($target) {
                // Check if the period is still open
                $activeMonthYear = \App\Models\ActiveMonthYear::where('year', $target->year)
                    ->where('month', $target->month)
                    ->where('is_open', true)
                    ->first();

                if (!$activeMonthYear) {
                    $validator->errors()->add('period', 'Cannot update target for a closed period.');
                }
            }
        });
    }
}
