<?php

namespace ShamimStack\WwwPay\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FraudDetection
{
    protected array $config;
    protected array $riskScore = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'enabled' => true,
            'max_velocity_per_hour' => 5,
            'max_velocity_per_day' => 20,
            'high_risk_amount_threshold' => 1000,
            'block_velocity_violations' => true,
            'block_high_risk' => false,
            'block_suspicious_ips' => true,
            'block_proxy_ips' => false,
            'fraud_api_key' => null,
        ], $config);
    }

    public function analyze(array $data): array
    {
        if (!$this->config['enabled']) {
            return [
                'passed' => true,
                'risk_level' => 'none',
                'risk_score' => 0,
                'flags' => [],
            ];
        }

        $this->riskScore = [];
        $flags = [];

        $this->checkVelocity($data, $flags);
        $this->checkAmount($data, $flags);
        $this->checkIpAddress($data, $flags);
        $this->checkEmailPatterns($data, $flags);
        $this->checkShippingBillingMatch($data, $flags);
        $this->checkDeviceFingerprint($data, $flags);
        $this->checkGeographicRisk($data, $flags);

        $totalScore = array_sum($this->riskScore);
        $riskLevel = $this->calculateRiskLevel($totalScore);

        return [
            'passed' => $riskLevel !== 'high',
            'risk_level' => $riskLevel,
            'risk_score' => $totalScore,
            'flags' => $flags,
            'recommendation' => $this->getRecommendation($riskLevel, $flags),
        ];
    }

    protected function checkVelocity(array $data, array &$flags): void
    {
        $customerId = $data['customer_id'] ?? $data['customer_email'] ?? null;
        $ip = $data['ip_address'] ?? request()->ip() ?? '';

        if ($customerId) {
            $hourlyKey = "fraud:velocity:hourly:{$customerId}";
            $dailyKey = "fraud:velocity:daily:{$customerId}";

            $hourlyCount = Cache::get($hourlyKey, 0);
            $dailyCount = Cache::get($dailyKey, 0);

            if ($hourlyCount >= $this->config['max_velocity_per_hour']) {
                $flags[] = 'high_velocity_hourly';
                $this->riskScore[] = 40;
                
                if ($this->config['block_velocity_violations']) {
                    $flags[] = 'blocked_velocity';
                    $this->riskScore[] = 50;
                }
            }

            if ($dailyCount >= $this->config['max_velocity_per_day']) {
                $flags[] = 'high_velocity_daily';
                $this->riskScore[] = 30;
            }
        }

        if ($ip) {
            $ipHourlyKey = "fraud:ip:velocity:hourly:{$ip}";
            $ipHourlyCount = Cache::get($ipHourlyKey, 0);

            if ($ipHourlyCount >= ($this->config['max_velocity_per_hour'] * 2)) {
                $flags[] = 'high_ip_velocity';
                $this->riskScore[] = 25;
            }
        }
    }

    protected function checkAmount(array $data, array &$flags): void
    {
        $amount = floatval($data['amount'] ?? 0);

        if ($amount >= $this->config['high_risk_amount_threshold']) {
            $flags[] = 'high_amount';
            $this->riskScore[] = 30;
        }

        if ($amount > 10000) {
            $flags[] = 'very_high_amount';
            $this->riskScore[] = 40;
        }

        if (isset($data['currency']) && $amount > 0) {
            $currencyRisk = $this->getCurrencyRisk($data['currency']);
            if ($currencyRisk > 0) {
                $flags[] = 'high_risk_currency';
                $this->riskScore[] = $currencyRisk;
            }
        }
    }

    protected function checkIpAddress(array $data, array &$flags): void
    {
        $ip = $data['ip_address'] ?? request()->ip() ?? '';

        if (!$ip) {
            return;
        }

        if ($this->isProxyIp($ip)) {
            $flags[] = 'proxy_ip';
            $this->riskScore[] = $this->config['block_proxy_ips'] ? 50 : 20;
        }

        if ($this->config['block_suspicious_ips'] && $this->isSuspiciousIp($ip)) {
            $flags[] = 'suspicious_ip';
            $this->riskScore[] = 30;
        }

        if ($this->isTorExitNode($ip)) {
            $flags[] = 'tor_exit_node';
            $this->riskScore[] = 40;
        }

        $ispRisk = $this->getIspRisk($ip);
        if ($ispRisk > 0) {
            $flags[] = 'high_risk_isp';
            $this->riskScore[] = $ispRisk;
        }
    }

    protected function checkEmailPatterns(array $data, array &$flags): void
    {
        $email = $data['customer_email'] ?? $data['email'] ?? '';

        if (!$email) {
            return;
        }

        if ($this->isDisposableEmail($email)) {
            $flags[] = 'disposable_email';
            $this->riskScore[] = 35;
        }

        if ($this->isFreeEmailProvider($email) && floatval($data['amount'] ?? 0) > 500) {
            $flags[] = 'free_email_high_value';
            $this->riskScore[] = 10;
        }

        $emailAge = $this->getEmailAge($email);
        if ($emailAge !== null && $emailAge < 7) {
            $flags[] = 'new_email';
            $this->riskScore[] = 15;
        }
    }

    protected function checkShippingBillingMatch(array $data, array &$flags): void
    {
        $shipping = $data['shipping_address'] ?? [];
        $billing = $data['billing_address'] ?? [];

        if (empty($shipping) || empty($billing)) {
            return;
        }

        $countryMatch = ($shipping['country'] ?? '') === ($billing['country'] ?? '');
        $postalMatch = ($shipping['postal_code'] ?? '') === ($billing['postal_code'] ?? '');
        $cityMatch = strcasecmp($shipping['city'] ?? '', $billing['city'] ?? '') === 0;

        if (!$countryMatch) {
            $flags[] = 'address_country_mismatch';
            $this->riskScore[] = 20;
        }

        if (!$postalMatch && !$cityMatch) {
            $flags[] = 'address_mismatch';
            $this->riskScore[] = 25;
        }
    }

    protected function checkDeviceFingerprint(array $data, array &$flags): void
    {
        $fingerprint = $data['device_fingerprint'] ?? $data['fingerprint'] ?? '';

        if (!$fingerprint) {
            $flags[] = 'no_device_fingerprint';
            $this->riskScore[] = 5;
            return;
        }

        $fingerprintKey = "fraud:fingerprint:{$fingerprint}";
        $fingerprintCount = Cache::get($fingerprintKey, 0);

        if ($fingerprintCount > 10) {
            $flags[] = 'high_fingerprint_reuse';
            $this->riskScore[] = 25;
        }
    }

    protected function checkGeographicRisk(array $data, array &$flags): void
    {
        $ip = $data['ip_address'] ?? request()->ip() ?? '';
        $billingCountry = $data['billing_address']['country'] ?? '';

        if ($ip && $billingCountry) {
            $ipCountry = $this->getIpCountry($ip);
            
            if ($ipCountry && $ipCountry !== $billingCountry) {
                $flags[] = 'ip_country_mismatch';
                $this->riskScore[] = 30;
            }
        }

        $highRiskCountries = ['NK', 'IR', 'SY', 'CU', 'VE', 'MM'];
        if (in_array($billingCountry, $highRiskCountries)) {
            $flags[] = 'high_risk_country';
            $this->riskScore[] = 40;
        }
    }

    protected function calculateRiskLevel(int $score): string
    {
        if ($score >= 70) {
            return 'high';
        } elseif ($score >= 40) {
            return 'medium';
        } elseif ($score >= 15) {
            return 'low';
        }
        return 'none';
    }

    protected function getRecommendation(string $riskLevel, array $flags): string
    {
        if (in_array('blocked_velocity', $flags)) {
            return 'BLOCK';
        }

        if (in_array('tor_exit_node', $flags) || in_array('high_risk_country', $flags)) {
            return 'REVIEW';
        }

        switch ($riskLevel) {
            case 'high':
                return 'BLOCK';
            case 'medium':
                return 'REVIEW';
            case 'low':
                return 'ALLOW_REVIEW';
            default:
                return 'ALLOW';
        }
    }

    protected function isProxyIp(string $ip): bool
    {
        return Cache::remember("fraud:proxy:{$ip}", 3600, function () use ($ip) {
            try {
                $response = Http::timeout(2)->get("https://ip-api.com/json/{$ip}?fields=proxy");
                if ($response->successful()) {
                    return $response->json()['proxy'] ?? false;
                }
            } catch (\Exception $e) {
            }
            return false;
        });
    }

    protected function isSuspiciousIp(string $ip): bool
    {
        $suspiciousRanges = [
            '192.168.',
            '10.',
            '172.16.',
            '127.',
        ];

        foreach ($suspiciousRanges as $range) {
            if (strpos($ip, $range) === 0) {
                return true;
            }
        }

        return false;
    }

    protected function isTorExitNode(string $ip): bool
    {
        return Cache::remember("fraud:tor:{$ip}", 3600, function () use ($ip) {
            try {
                $response = Http::timeout(2)->get("https://check.torproject.org/exit-addresses");
                if ($response->successful()) {
                    return strpos($response->body(), $ip) !== false;
                }
            } catch (\Exception $e) {
            }
            return false;
        });
    }

    protected function getIspRisk(string $ip): int
    {
        return 0;
    }

    protected function getCurrencyRisk(string $currency): int
    {
        $highRiskCurrencies = [
            'BTC' => 10,
            'ETH' => 10,
            'XMR' => 30,
        ];

        return $highRiskCurrencies[strtoupper($currency)] ?? 0;
    }

    protected function isDisposableEmail(string $email): bool
    {
        $disposableDomains = [
            'tempmail.com',
            'guerrillamail.com',
            'mailinator.com',
            '10minutemail.com',
            'throwaway.email',
            'temp-mail.org',
        ];

        $domain = strtolower(explode('@', $email)[1] ?? '');

        return in_array($domain, $disposableDomains);
    }

    protected function isFreeEmailProvider(string $email): bool
    {
        $freeProviders = [
            'gmail.com',
            'yahoo.com',
            'hotmail.com',
            'outlook.com',
            'aol.com',
        ];

        $domain = strtolower(explode('@', $email)[1] ?? '');

        return in_array($domain, $freeProviders);
    }

    protected function getEmailAge(string $email): ?int
    {
        return null;
    }

    protected function getIpCountry(string $ip): ?string
    {
        return Cache::remember("fraud:ip_country:{$ip}", 86400, function () use ($ip) {
            try {
                $response = Http::timeout(2)->get("https://ip-api.com/json/{$ip}?fields=countryCode");
                if ($response->successful()) {
                    return $response->json()['countryCode'] ?? null;
                }
            } catch (\Exception $e) {
            }
            return null;
        });
    }

    public function incrementVelocity(string $customerId, float $amount): void
    {
        $hourlyKey = "fraud:velocity:hourly:{$customerId}";
        $dailyKey = "fraud:velocity:daily:{$customerId}";

        Cache::put($hourlyKey, Cache::get($hourlyKey, 0) + 1, 3600);
        Cache::put($dailyKey, Cache::get($dailyKey, 0) + 1, 86400);

        $ip = request()->ip() ?? '';
        if ($ip) {
            $ipHourlyKey = "fraud:ip:velocity:hourly:{$ip}";
            Cache::put($ipHourlyKey, Cache::get($ipHourlyKey, 0) + 1, 3600);
        }
    }
}
