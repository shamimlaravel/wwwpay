<?php

namespace ShamimStack\AllInOnePayment\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyConverter
{
    /**
     * Base currency for conversion
     */
    protected string $baseCurrency;

    /**
     * Exchange rates cache key
     */
    protected string $cacheKey = 'payment_exchange_rates';

    /**
     * Cache duration in minutes
     */
    protected int $cacheDuration = 60; // 1 hour

    /**
     * Constructor
     *
     * @param string $baseCurrency
     */
    public function __construct(string $baseCurrency = 'USD')
    {
        $this->baseCurrency = strtoupper($baseCurrency);
    }

    /**
     * Convert amount from one currency to another
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);

        return $amount * $rate;
    }

    /**
     * Get exchange rate between two currencies
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    protected function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        $rates = $this->getExchangeRates();

        if (!isset($rates[$toCurrency])) {
            // If target rate not available, try via base currency
            if ($fromCurrency !== $this->baseCurrency && isset($rates[$fromCurrency])) {
                $rateFromToBase = 1 / ($rates[$fromCurrency] ?? 1);
                $rateBaseTo = $rates[$toCurrency] ?? 1;
                return $rateFromToBase * $rateBaseTo;
            }
            // Fallback: assume 1:1 if not available
            return 1.0;
        }

        // If converting from base currency
        if ($fromCurrency === $this->baseCurrency) {
            return $rates[$toCurrency];
        }

        // Convert via base currency
        $rateFromToBase = 1 / ($rates[$fromCurrency] ?? 1);
        $rateBaseTo = $rates[$toCurrency] ?? 1;

        return $rateFromToBase * $rateBaseTo;
    }

    /**
     * Get all exchange rates relative to base currency
     *
     * @return array
     */
    protected function getExchangeRates(): array
    {
        // Try to get from cache
        $cached = Cache::get($this->cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $cached;
        }

        // Fetch from exchange rate API (example: exchangerate.host)
        try {
            $response = Http::get("https://api.exchangerate.host/latest", [
                'base' => $this->baseCurrency,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rates = $data['rates'] ?? [];

                // Add base currency rate as 1
                $rates[$this->baseCurrency] = 1.0;

                // Cache the rates
                Cache::put($this->cacheKey, $rates, $this->cacheDuration);

                return $rates;
            }
        } catch (\Exception $e) {
            // Log error in production
        }

        // Fallback to empty rates (will cause 1:1 conversion)
        return [$this->baseCurrency => 1.0];
    }

    /**
     * Get supported currencies
     *
     * @return array
     */
    public function getSupportedCurrencies(): array
    {
        $rates = $this->getExchangeRates();
        return array_keys($rates);
    }

    /**
     * Set base currency
     *
     * @param string $currency
     * @return void
     */
    public function setBaseCurrency(string $currency): void
    {
        $this->baseCurrency = strtoupper($currency);
        // Clear cache when base currency changes
        Cache::forget($this->cacheKey);
    }

    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @param string $currency
     * @param int $decimals
     * @return string
     */
    public function format(float $amount, string $currency, int $decimals = 2): string
    {
        $currency = strtoupper($currency);
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'SEK' => 'kr',
            'NZD' => 'NZ$',
            'MXN' => 'MX$',
            'SGD' => 'S$',
            'HKD' => 'HK$',
            'NOK' => 'kr',
            'KRW' => '₩',
            'TRY' => '₺',
            'RUB' => '₽',
            'INR' => '₹',
            'BRL' => 'R$',
            'ZAR' => 'R',
            'BDT' => '৳',
            'PKR' => '₨',
            'NGN' => '₦',
            'KES' => 'KSh',
            'EGP' => '£',
            'GHS' => 'GH₵',
            'UGX' => 'USh',
            'TZS' => 'TSh',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        $formatted = number_format(abs($amount), $decimals);

        if ($amount < 0) {
            return "-{$symbol}{$formatted}";
        }

        return "{$symbol}{$formatted}";
    }
}