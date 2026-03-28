<?php

namespace ShamimStack\WwwPay\Security;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class PciCompliance
{
    protected array $config;
    protected static ?string $tokenizationKey = null;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'token_expiry' => 3600,
            'masking_char' => '*',
            'masking_pattern' => 'default',
            'encryption_algorithm' => 'AES-256-CBC',
        ], $config);
    }

    public function tokenize(array $cardData): string
    {
        $tokenData = [
            'last_four' => substr($cardData['number'] ?? '', -4),
            'exp_month' => $cardData['exp_month'] ?? '',
            'exp_year' => $cardData['exp_year'] ?? '',
            'card_type' => $this->detectCardType($cardData['number'] ?? ''),
            'created_at' => time(),
        ];

        $token = bin2hex(random_bytes(16));
        $cacheKey = "pci:token:{$token}";
        
        cache()->put($cacheKey, $tokenData, $this->config['token_expiry']);

        return $token;
    }

    public function detokenize(string $token): ?array
    {
        $cacheKey = "pci:token:{$token}";
        return cache()->get($cacheKey);
    }

    public function mask(string $value, string $type = 'card'): string
    {
        return match ($type) {
            'card' => $this->maskCardNumber($value),
            'email' => $this->maskEmail($value),
            'phone' => $this->maskPhone($value),
            'name' => $this->maskName($value),
            default => $this->maskDefault($value),
        };
    }

    protected function maskCardNumber(string $number): string
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        $length = strlen($cleanNumber);

        if ($length < 4) {
            return str_repeat($this->config['masking_char'], $length);
        }

        $lastFour = substr($cleanNumber, -4);
        $masked = str_repeat($this->config['masking_char'], $length - 4) . $lastFour;

        if (strlen($number) !== strlen($cleanNumber)) {
            $formatted = '';
            $j = 0;
            for ($i = 0; $i < strlen($number); $i++) {
                if (is_numeric($number[$i])) {
                    $formatted .= $masked[$j++];
                } else {
                    $formatted .= $number[$i];
                }
            }
            return $formatted;
        }

        return $masked;
    }

    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $this->maskDefault($email);
        }

        $local = $parts[0];
        $domain = $parts[1];
        $localLength = strlen($local);

        if ($localLength <= 2) {
            $maskedLocal = str_repeat($this->config['masking_char'], $localLength);
        } else {
            $maskedLocal = $local[0] . str_repeat($this->config['masking_char'], $localLength - 2) . $local[$localLength - 1];
        }

        return $maskedLocal . '@' . $domain;
    }

    protected function maskPhone(string $phone): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $length = strlen($cleanPhone);

        if ($length < 4) {
            return str_repeat($this->config['masking_char'], $length);
        }

        return str_repeat($this->config['masking_char'], $length - 4) . substr($cleanPhone, -4);
    }

    protected function maskName(string $name): string
    {
        $parts = explode(' ', $name);
        $maskedParts = [];

        foreach ($parts as $part) {
            $length = strlen($part);
            if ($length <= 1) {
                $maskedParts[] = $part;
            } else {
                $maskedParts[] = $part[0] . str_repeat($this->config['masking_char'], $length - 1);
            }
        }

        return implode(' ', $maskedParts);
    }

    protected function maskDefault(string $value): string
    {
        $length = strlen($value);
        return str_repeat($this->config['masking_char'], $length);
    }

    public function encryptSensitiveData(string $data): string
    {
        return Crypt::encryptString($data);
    }

    public function decryptSensitiveData(string $encryptedData): string
    {
        return Crypt::decryptString($encryptedData);
    }

    public function hashCardNumber(string $cardNumber): string
    {
        return hash('sha256', preg_replace('/[^0-9]/', '', $cardNumber));
    }

    public function verifyCardNumber(string $cardNumber, string $hash): bool
    {
        return hash_equals($this->hashCardNumber($cardNumber), $hash);
    }

    public function validateLuhn(string $cardNumber): bool
    {
        $number = preg_replace('/[^0-9]/', '', $cardNumber);
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$i];

            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    public function validateCardExpiry(string $expMonth, string $expYear): bool
    {
        $month = (int)$expMonth;
        $year = (int)$expYear;

        if ($month < 1 || $month > 12) {
            return false;
        }

        if ($year < 100) {
            $year += 2000;
        }

        $now = time();
        $expiry = mktime(0, 0, 0, $month + 1, 1, $year);

        return $expiry > $now;
    }

    public function detectCardType(string $cardNumber): string
    {
        $number = preg_replace('/[^0-9]/', '', $cardNumber);
        $length = strlen($number);

        $patterns = [
            'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'mastercard' => '/^5[1-5][0-9]{14}$|^2(?:2(?:2[1-9]|[3-9][0-9])|[3-6][0-9][0-9]|7(?:[01][0-9]|20))[0-9]{12}$/',
            'amex' => '/^3[47][0-9]{13}$/',
            'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
            'diners' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
            'jcb' => '/^(?:2131|1800|35\d{3})\d{11}$/',
            'unionpay' => '/^62[0-9]{14,17}$/',
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $number)) {
                return $type;
            }
        }

        return 'unknown';
    }

    public function validateCVV(string $cvv, string $cardType): bool
    {
        $cvvLength = strlen(preg_replace('/[^0-9]/', '', $cvv));

        return match ($cardType) {
            'amex' => $cvvLength === 4,
            default => $cvvLength === 3,
        };
    }

    public function generateTransactionKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function sanitizeLogData(array $data): array
    {
        $sensitiveFields = [
            'card_number', 'pan', 'card_number_last_four',
            'cvv', 'cvc', 'cvv2', 'cvc2',
            'expiry_month', 'expiry_year', 'exp_month', 'exp_year',
            'password', 'secret', 'token', 'api_key',
            'social_security_number', 'ssn', 'tax_id',
            'bank_account_number', 'routing_number',
            'authorization_code', 'auth_code',
        ];

        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);

            if (in_array($lowerKey, $sensitiveFields)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeLogData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    public function getCardMetadata(string $cardNumber): array
    {
        return [
            'type' => $this->detectCardType($cardNumber),
            'last_four' => substr(preg_replace('/[^0-9]/', '', $cardNumber), -4),
            'valid_luhn' => $this->validateLuhn($cardNumber),
        ];
    }
}
