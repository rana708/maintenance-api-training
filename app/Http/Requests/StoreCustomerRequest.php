<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Customer::class);
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $email = $this->input('email');
        $phone = $this->input('phone');

        $this->merge([
            'name' => is_string($name) ? trim(strip_tags($name)) : $name,
            'email' => is_string($email) ? strtolower(trim($email)) : $email,
            'phone' => is_string($phone) ? preg_replace('/\D/', '', $phone) : $phone,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم العميل مطلوب / Customer name is required',
            'name.string' => 'اسم العميل يجب أن يكون نصًا / Customer name must be a string',
            'name.max' => 'اسم العميل يجب ألا يتجاوز 190 حرفًا / Customer name may not exceed 190 characters',
            'email.required' => 'البريد الإلكتروني مطلوب / Email is required',
            'email.string' => 'البريد الإلكتروني يجب أن يكون نصًا / Email must be a string',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة / Email format is invalid',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفًا / Email may not exceed 255 characters',
            'phone.required' => 'رقم الهاتف مطلوب / Phone number is required',
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا / Phone number must be a string',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 30 حرفًا / Phone number may not exceed 30 characters',
        ];
    }
}
