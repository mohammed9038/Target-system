<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTargetRequest extends FormRequest
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
            'salesman_id' => 'required|exists:salesmen,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'target_amount' => 'required|numeric|min:0.01',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'salesman_id' => 'salesman',
            'supplier_id' => 'supplier',
            'category_id' => 'category',
            'target_amount' => 'target amount',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'salesman_id.required' => 'Please select a salesman.',
            'supplier_id.required' => 'Please select a supplier.',
            'category_id.required' => 'Please select a category.',
            'target_amount.min' => 'Target amount must be greater than 0.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if the period is open
            $activeMonthYear = \App\Models\ActiveMonthYear::where('year', $this->year)
                ->where('month', $this->month)
                ->where('is_open', true)
                ->first();

            if (!$activeMonthYear) {
                $validator->errors()->add('period', 'The selected period is not open for target entry.');
            }

            // Check if target already exists (for create only)
            if ($this->isMethod('post')) {
                $existingTarget = \App\Models\SalesTarget::where([
                    'salesman_id' => $this->salesman_id,
                    'supplier_id' => $this->supplier_id,
                    'category_id' => $this->category_id,
                    'year' => $this->year,
                    'month' => $this->month,
                ])->first();

                if ($existingTarget) {
                    $validator->errors()->add('duplicate', 'A target already exists for this combination.');
                }
            }
        });
    }
}
