<?php

namespace ShamimStack\WwwPay\Traits;

use ShamimStack\WwwPay\Helpers\CurrencyConverter;

trait HandlesCurrency
{
    protected CurrencyConverter $currencyConverter;

    public function getCurrencyConverter(): CurrencyConverter
    {
        if (!isset($this->currencyConverter)) {
            $this->currencyConverter = new CurrencyConverter(
                config('payment.currency.base', 'USD')
            );
        }

        return $this->currencyConverter;
    }

    public function convertCurrency(float $amount, string $from, string $to): float
    {
        return $this->getCurrencyConverter()->convert($amount, $from, $to);
    }

    public function formatCurrency(float $amount, string $currency): string
    {
        return $this->getCurrencyConverter()->format($amount, $currency);
    }

    public function getExchangeRate(string $from, string $to): float
    {
        return $this->getCurrencyConverter()->getRate($from, $to);
    }

    public function getSupportedCurrencies(): array
    {
        return config('payment.currency.allowed', [
            'USD', 'EUR', 'GBP', 'BDT', 'NGN', 'INR', 'PKR'
        ]);
    }
}
