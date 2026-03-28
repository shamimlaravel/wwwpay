<?php

namespace ShamimStack\WwwPay\Contracts;

interface Subscription
{
    public function getGatewaySubscriptionId(): ?string;
    
    public function getPlanId(): ?string;
    
    public function getCustomerId(): ?string;
    
    public function getStatus(): string;
    
    public function isActive(): bool;
    
    public function isCancelled(): bool;
    
    public function isExpired(): bool;
    
    public function cancel(?string $reason = null): bool;
    
    public function getStartDate(): ?\DateTimeInterface;
    
    public function getEndDate(): ?\DateTimeInterface;
}
