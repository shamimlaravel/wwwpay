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
}