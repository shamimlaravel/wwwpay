<?php

namespace ShamimStack\WwwPay\Http\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class PaymentValidator
{
    public static function validatePayment(array $data): ValidationValidator
    {
        return Validator::make($data, [
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => 'required|string|size:3|in:USD,EUR,GBP,BDT,NGN,INR,PKR,AED,SAR,CNY,JPY,KRW,SGD,MYR,THB,IDR,PHP,VND,ZAR,KES,EGP,BRL,MXN,ARS,CAD,AUD,NZD',
            'email' => 'nullable|email|max:255',
            'return_url' => 'nullable|url|max:2048',
            'cancel_url' => 'nullable|url|max:2048',
            'metadata' => 'nullable|array',
            'gateway' => 'nullable|string|max:50',
        ]);
    }

    public static function validateCard(array $data): ValidationValidator
    {
        return Validator::make($data, [
            'card_number' => 'required|string|digits_between:13,19',
            'expiry_month' => 'required|string|digits:2',
            'expiry_year' => 'required|string|digits:4',
            'cvv' => 'required|string|digits_between:3,4',
            'card_holder_name' => 'required|string|max:255',
        ]);
    }

    public static function validateRefund(array $data): ValidationValidator
    {
        return Validator::make($data, [
            'transaction_id' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:1000',
        ]);
    }

    public static function validateSubscription(array $data): ValidationValidator
    {
        return Validator::make($data, [
            'plan_id' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_id' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);
    }

    public static function validateWebhook(array $data): ValidationValidator
    {
        return Validator::make($data, [
            'gateway' => 'required|string|max:50',
            'event_type' => 'required|string|max:100',
            'transaction_id' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'currency' => 'nullable|string|size:3',
        ]);
    }
}
