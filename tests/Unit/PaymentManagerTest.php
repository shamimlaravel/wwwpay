<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\PaymentManager;
use Illuminate\Foundation\Application;
use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Exceptions\InvalidConfigurationException;

class PaymentManagerTest extends TestCase
{
    /** @test */
    public function it_can_get_a_gateway_instance()
    {
        // Create a mock Laravel application
        $app = $this->createMock(Application::class);
        
        // Configure the app to return a mock gateway when payment.stripe is requested
        $mockGateway = $this->createMock(PaymentGateway::class);
        $app->method('bound')
            ->with('payment.stripe')
            ->willReturn(true);
        $app->method('make')
            ->with('payment.stripe')
            ->willReturn($mockGateway);
        
        $paymentManager = new PaymentManager($app);
        
        // This should not throw an exception
        $gateway = $paymentManager->gateway('stripe');
        
        $this->assertInstanceOf(PaymentGateway::class, $gateway);
    }
    
    /** @test */
    public function it_throws_exception_for_unknown_gateway()
    {
        // Create a mock Laravel application
        $app = $this->createMock(Application::class);
        $app->method('bound')
            ->with('payment.unknown')
            ->willReturn(false);
        
        $paymentManager = new PaymentManager($app);
        
        $this->expectException(InvalidConfigurationException::class);
        $paymentManager->gateway('unknown');
    }
}