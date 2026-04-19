<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRepairRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:120', Rule::requiredIf(fn () => $this->input('preferred_contact') === 'email')],
            'city' => ['nullable', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:50'],
            'issue' => ['required', 'string', 'max:1000'],
            'preferred_contact' => ['nullable', Rule::in(['phone', 'viber', 'whatsapp', 'email'])],
            'source_page' => ['nullable', 'string', 'max:255'],
            'form_fragment' => ['nullable', 'string', 'max:50'],
            'gdpr_consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Полето :attribute е задължително.',
            'email' => 'Полето :attribute трябва да бъде валиден имейл адрес.',
            'max' => 'Полето :attribute е твърде дълго.',
            'preferred_contact.in' => 'Изберете валиден начин за контакт.',
            'gdpr_consent.accepted' => 'Трябва да приемете политиката за поверителност.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'име',
            'phone' => 'телефон',
            'email' => 'имейл',
            'city' => 'град',
            'model' => 'модел',
            'issue' => 'описание на проблема',
            'preferred_contact' => 'предпочитан контакт',
            'gdpr_consent' => 'политиката за поверителност',
        ];
    }
}
