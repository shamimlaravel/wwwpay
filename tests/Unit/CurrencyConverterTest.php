<?php

namespace ShamimStack\AllInOnePayment\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\AllInOnePayment\Helpers\CurrencyConverter;

class CurrencyConverterTest extends TestCase
{
    /** @test */
    public function it_converts_same_currency()
    {
        $converter = new CurrencyConverter('USD');
        $result = $converter->convert(100.00, 'USD', 'USD');
        
        $this->assertEquals(100.00, $result);
    }
    
    /** @test */
    public function it_converts_usd_to_eur_with_mocked_rate()
    {
        // Create a partial mock to mock the getExchangeRates method
        $converter = $this->getMockBuilder(CurrencyConverter::class)
            ->setConstructorArgs(['USD'])  // Set base currency via constructor
            ->onlyMethods(['getExchangeRates'])
            ->getMock();
        
        $converter->expects($this->once())
            ->method('getExchangeRates')
            ->willReturn(['USD' => 1.0, 'EUR' => 0.85]); // 1 USD = 0.85 EUR
        
        $result = $converter->convert(100.00, 'USD', 'EUR');
        $this->assertEquals(85.00, $result);
    }
    
    /** @test */
    public function it_formats_currency()
    {
        $converter = new CurrencyConverter('USD');
        $formatted = $converter->format(100.50, 'USD');
        
        $this->assertEquals('$100.50', $formatted);
        
        $formatted = $converter->format(-50.25, 'EUR');
        $this->assertEquals('-€50.25', $formatted);
    }
}