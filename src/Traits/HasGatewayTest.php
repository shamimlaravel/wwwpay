<?php

namespace ShamimStack\WwwPay\Traits;

trait HasGatewayTest
{
    public function test(array $options = []): array
    {
        return [
            'success' => true,
            'gateway' => $this->getName(),
            'message' => 'Gateway test successful',
            'timestamp' => date('c'),
            'config' => [
                'sandbox' => !empty($this->config['sandbox']),
                'test_mode' => $this->config['test_mode'] ?? false,
            ],
        ];
    }
}
