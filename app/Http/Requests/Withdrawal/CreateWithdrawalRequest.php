<?php

namespace App\Http\Requests\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class CreateWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:10',       // Minimum $10
                'max:50000',    // Maximum $50,000 per withdrawal
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Withdrawal amount is required.',
            'amount.numeric'  => 'Withdrawal amount must be a number.',
            'amount.min'      => 'Minimum withdrawal amount is $10.',
            'amount.max'      => 'Maximum withdrawal amount per request is $50,000.',
        ];
    }

    /**
     * Additional validation after rules pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user   = auth('api')->user();
            $amount = (float)$this->input('amount');

            $availableBalance = (float)($user->available_balance ?? 0);

            if ($availableBalance < $amount) {
                $validator->errors()->add(
                    'amount',
                    "Insufficient balance. Your available balance is \${$availableBalance}."
                );
            }
        });
    }
}
