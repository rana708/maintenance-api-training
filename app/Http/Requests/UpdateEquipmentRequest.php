<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $stringFields = ['name', 'description', 'serial_number', 'type', 'status'];
        $values = [];

        foreach ($stringFields as $field) {
            if (! array_key_exists($field, $this->all())) {
                continue;
            }

            $value = $this->input($field);
            $values[$field] = is_string($value) ? trim($value) : $value;
        }

        $this->merge($values);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:190'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'serial_number' => ['sometimes', 'required', 'string', 'max:100'],
            'type' => ['sometimes', 'required', 'string', 'max:100'],
            'status' => ['sometimes', 'required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المعدة مطلوب / Equipment name is required',
            'name.string' => 'اسم المعدة يجب أن يكون نصًا / Equipment name must be a string',
            'name.max' => 'اسم المعدة يجب ألا يتجاوز 190 حرفًا / Equipment name may not exceed 190 characters',
            'description.string' => 'الوصف يجب أن يكون نصًا / Description must be a string',
            'description.max' => 'الوصف يجب ألا يتجاوز 5000 حرف / Description may not exceed 5000 characters',
            'serial_number.required' => 'الرقم التسلسلي مطلوب / Serial number is required',
            'serial_number.string' => 'الرقم التسلسلي يجب أن يكون نصًا / Serial number must be a string',
            'serial_number.max' => 'الرقم التسلسلي يجب ألا يتجاوز 100 حرف / Serial number may not exceed 100 characters',
            'type.required' => 'نوع المعدة مطلوب / Equipment type is required',
            'type.string' => 'نوع المعدة يجب أن يكون نصًا / Equipment type must be a string',
            'type.max' => 'نوع المعدة يجب ألا يتجاوز 100 حرف / Equipment type may not exceed 100 characters',
            'status.required' => 'حالة المعدة مطلوبة / Equipment status is required',
            'status.string' => 'حالة المعدة يجب أن تكون نصًا / Equipment status must be a string',
            'status.max' => 'حالة المعدة يجب ألا تتجاوز 50 حرفًا / Equipment status may not exceed 50 characters',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم المعدة / equipment name',
            'description' => 'الوصف / description',
            'serial_number' => 'الرقم التسلسلي / serial number',
            'type' => 'نوع المعدة / equipment type',
            'status' => 'حالة المعدة / equipment status',
        ];
    }
}