<?php

namespace ShamimStack\AllInOnePayment\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\AllInOnePayment\Security\PciCompliance;
use ShamimStack\AllInOnePayment\Security\FraudDetection;

class SecurityTest extends TestCase
{
    protected PciCompliance $pci;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pci = new PciCompliance();
    }

    public function test_mask_card_number()
    {
        $masked = $this->pci->mask('4111111111111111', 'card');
        $this->assertEquals('************1111', $masked);
    }

    public function test_mask_email()
    {
        $masked = $this->pci->mask('testuser@gmail.com', 'email');
        $this->assertStringContainsString('@gmail.com', $masked);
        $this->assertStringContainsString('t', $masked);
    }

    public function test_mask_phone()
    {
        $masked = $this->pci->mask('+1-555-123-4567', 'phone');
        $this->assertEquals('*******4567', $masked);
    }

    public function test_mask_name()
    {
        $masked = $this->pci->mask('John Doe', 'name');
        $this->assertEquals('J*** D**', $masked);
    }

    public function test_validate_luhn_valid_card()
    {
        $this->assertTrue($this->pci->validateLuhn('4111111111111111'));
        $this->assertTrue($this->pci->validateLuhn('5500000000000004'));
        $this->assertTrue($this->pci->validateLuhn('340000000000009'));
    }

    public function test_validate_luhn_invalid_card()
    {
        $this->assertFalse($this->pci->validateLuhn('4111111111111112'));
        $this->assertFalse($this->pci->validateLuhn('1234567890123456'));
    }

    public function test_detect_card_type_visa()
    {
        $this->assertEquals('visa', $this->pci->detectCardType('4111111111111111'));
        $this->assertEquals('visa', $this->pci->detectCardType('4111111111111111'));
    }

    public function test_detect_card_type_mastercard()
    {
        $this->assertEquals('mastercard', $this->pci->detectCardType('5500000000000004'));
        $this->assertEquals('mastercard', $this->pci->detectCardType('5555555555554444'));
    }

    public function test_detect_card_type_amex()
    {
        $this->assertEquals('amex', $this->pci->detectCardType('340000000000009'));
        $this->assertEquals('amex', $this->pci->detectCardType('378282246310005'));
    }

    public function test_detect_card_type_discover()
    {
        $this->assertEquals('discover', $this->pci->detectCardType('6011111111111117'));
        $this->assertEquals('discover', $this->pci->detectCardType('6011000990139424'));
    }

    public function test_detect_card_type_unknown()
    {
        $this->assertEquals('unknown', $this->pci->detectCardType('9999999999999999'));
    }

    public function test_validate_card_expiry_valid()
    {
        $futureYear = date('Y') + 1;
        $this->assertTrue($this->pci->validateCardExpiry('12', (string)$futureYear));
    }

    public function test_validate_card_expiry_invalid()
    {
        $pastYear = date('Y') - 1;
        $this->assertFalse($this->pci->validateCardExpiry('12', (string)$pastYear));
        $this->assertFalse($this->pci->validateCardExpiry('13', '2030'));
        $this->assertFalse($this->pci->validateCardExpiry('00', '2030'));
    }

    public function test_validate_cvv()
    {
        $this->assertTrue($this->pci->validateCVV('123', 'visa'));
        $this->assertTrue($this->pci->validateCVV('1234', 'amex'));
        $this->assertFalse($this->pci->validateCVV('12', 'visa'));
        $this->assertFalse($this->pci->validateCVV('12345', 'amex'));
    }

    public function test_hash_card_number()
    {
        $hash1 = $this->pci->hashCardNumber('4111111111111111');
        $hash2 = $this->pci->hashCardNumber('4111111111111111');
        $hash3 = $this->pci->hashCardNumber('4111111111111112');

        $this->assertEquals($hash1, $hash2);
        $this->assertNotEquals($hash1, $hash3);
        $this->assertEquals(64, strlen($hash1));
    }

    public function test_verify_card_number()
    {
        $hash = $this->pci->hashCardNumber('4111111111111111');
        $this->assertTrue($this->pci->verifyCardNumber('4111111111111111', $hash));
        $this->assertFalse($this->pci->verifyCardNumber('4111111111111112', $hash));
    }

    public function test_sanitize_log_data()
    {
        $data = [
            'amount' => 100.00,
            'card_number' => '4111111111111111',
            'cvv' => '123',
            'email' => 'test@example.com',
            'nested' => [
                'password' => 'secret123',
                'token' => 'abc123',
            ],
        ];

        $sanitized = $this->pci->sanitizeLogData($data);

        $this->assertEquals(100.00, $sanitized['amount']);
        $this->assertEquals('[REDACTED]', $sanitized['card_number']);
        $this->assertEquals('[REDACTED]', $sanitized['cvv']);
        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertEquals('[REDACTED]', $sanitized['nested']['password']);
        $this->assertEquals('[REDACTED]', $sanitized['nested']['token']);
    }

    public function test_get_card_metadata()
    {
        $metadata = $this->pci->getCardMetadata('4111111111111111');

        $this->assertEquals('visa', $metadata['type']);
        $this->assertEquals('1111', $metadata['last_four']);
        $this->assertTrue($metadata['valid_luhn']);
    }

    public function test_generate_transaction_key()
    {
        $key1 = $this->pci->generateTransactionKey();
        $key2 = $this->pci->generateTransactionKey();

        $this->assertEquals(64, strlen($key1));
        $this->assertNotEquals($key1, $key2);
    }
}

class FraudDetectionTest extends TestCase
{
    protected FraudDetection $fraud;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraud = new FraudDetection([
            'enabled' => true,
            'max_velocity_per_hour' => 5,
            'max_velocity_per_day' => 20,
            'high_risk_amount_threshold' => 1000,
        ]);
    }

    public function test_analyze_returns_result_structure()
    {
        $result = $this->fraud->analyze([
            'amount' => 100,
            'customer_email' => 'test@example.com',
        ]);

        $this->assertArrayHasKey('passed', $result);
        $this->assertArrayHasKey('risk_level', $result);
        $this->assertArrayHasKey('risk_score', $result);
        $this->assertArrayHasKey('flags', $result);
        $this->assertArrayHasKey('recommendation', $result);
    }

    public function test_analyze_high_amount_flag()
    {
        $result = $this->fraud->analyze([
            'amount' => 5000,
            'customer_email' => 'test@example.com',
        ]);

        $this->assertContains('high_amount', $result['flags']);
        $this->assertGreaterThan(0, $result['risk_score']);
    }

    public function test_analyze_disposable_email_flag()
    {
        $result = $this->fraud->analyze([
            'amount' => 100,
            'customer_email' => 'test@mailinator.com',
        ]);

        $this->assertContains('disposable_email', $result['flags']);
    }

    public function test_analyze_disabled_returns_passed()
    {
        $fraud = new FraudDetection(['enabled' => false]);
        
        $result = $fraud->analyze([
            'amount' => 100000,
        ]);

        $this->assertTrue($result['passed']);
        $this->assertEquals('none', $result['risk_level']);
        $this->assertEquals(0, $result['risk_score']);
    }
}
