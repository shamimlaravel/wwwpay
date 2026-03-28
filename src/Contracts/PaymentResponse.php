<?php

namespace ShamimStack\WwwPay\Contracts;

class PaymentResponse
{
    /**
     * @var bool Indicates if the transaction was successful
     */
    public $success;

    /**
     * @var string Transaction ID from the gateway
     */
    public $transactionId;

    /**
     * @var string|null Gateway-specific transaction identifier
     */
    public $gatewayTransactionId;

    /**
     * @var string|null Error message if transaction failed
     */
    public $errorMessage;

    /**
     * @var array Raw response from the gateway
     */
    public $rawResponse;

    /**
     * @var array Additional data (authorization, redirect URL, etc.)
     */
    public $data;

    /**
     * Constructor
     *
     * @param bool $success
     * @param string|null $transactionId
     * @param array $options
     */
    public function __construct(
        bool $success = false,
        string $transactionId = null,
        array $options = []
    ) {
        $this->success = $success;
        $this->transactionId = $transactionId;
        $this->gatewayTransactionId = $options['gatewayTransactionId'] ?? null;
        $this->errorMessage = $options['errorMessage'] ?? null;
        $this->rawResponse = $options['rawResponse'] ?? [];
        $this->data = $options['data'] ?? [];
    }

    /**
     * Check if transaction was successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Get error message
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Get transaction ID
     *
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * Get gateway transaction ID
     *
     * @return string|null
     */
    public function getGatewayTransactionId(): ?string
    {
        return $this->gatewayTransactionId;
    }

    /**
     * Get raw response
     *
     * @return array
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    /**
     * Get additional data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get redirect URL if applicable
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->data['redirect_url'] ?? $this->data['payment_url'] ?? null;
    }

    /**
     * Check if this is a redirect response
     *
     * @return bool
     */
    public function isRedirect(): bool
    {
        return !empty($this->getRedirectUrl());
    }

    /**
     * Check if the payment is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        $status = $this->data['status'] ?? '';
        return strtoupper($status) === 'PENDING' || strtoupper($status) === 'PROCESSING';
    }

    /**
     * Convert response to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'gateway_transaction_id' => $this->gatewayTransactionId,
            'error_message' => $this->errorMessage,
            'data' => $this->data,
            'redirect_url' => $this->getRedirectUrl(),
            'is_redirect' => $this->isRedirect(),
            'is_pending' => $this->isPending(),
        ];
    }
}