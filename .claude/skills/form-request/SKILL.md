---
description: Use when creating or updating Laravel Form Request classes to implement validation rules, authorization logic, custom Arabic/English error messages, and input sanitization via prepareForValidation.
---

# Form Request Guidelines

## 1. Naming & Location
- Place all Form Requests in `app/Http/Requests/`.
- Naming format: `{Action}{Resource}Request.php` (e.g., `StoreTicketRequest.php`, `UpdateMaintenanceRequest.php`).

## 2. Authorization (`authorize`)
- Always define explicit authorization logic or return `true` if handled via middleware/policies.
- Do not leave it returning `false` unless intended to block all users.

## 3. Input Sanitization (`prepareForValidation`)
- Override `prepareForValidation()` to clean or transform raw inputs before validation runs.
- Examples: Trim strings, normalize phone numbers, cast types, or strip special characters.

## 4. Validation Rules (`rules`)
- Use array syntax for rules instead of pipe strings for readability (`['required', 'string']`).
- Use dependent validation rules when fields rely on each other (e.g., `required_if`, `required_with`, `prohibits`).

## 5. Custom Arabic & English Error Messages (`messages`)
- Provide explicit Arabic and English localized messages or clear Arabic translations for standard rules to ensure localized API responses.

---

## Real Example

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => preg_replace('/\D/', '', $this->phone),
            'title' => trim(strip_tags($this->title)),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:hardware,software,network'],
            'serial_number' => ['required_if:type,hardware', 'nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'min:10'],
            'phone' => ['required', 'string', 'regex:/^05\d{8}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان التذكرة مطلوب / Title is required',
            'type.required' => 'نوع التذكرة مطلوب / Type is required',
            'type.in' => 'نوع التذكرة غير صالحة / Invalid ticket type',
            'serial_number.required_if' => 'الرقم التسلسلي مطلوب للأجهزة / Serial number is required for hardware',
            'description.required' => 'وصف المشكلة مطلوب / Description is required',
            'phone.required' => 'رقم الهاتف مطلوب / Phone number is required',
            'phone.regex' => 'رقم الهاتف يجب أن يكون رقم سعودي صحيح / Invalid phone format',
        ];
    }
}