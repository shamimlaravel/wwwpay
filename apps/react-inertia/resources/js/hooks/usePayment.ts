import { useState } from 'react'

export function usePayment() {
  const [selectedGateway, setSelectedGateway] = useState('stripe')
  const [processing, setProcessing] = useState(false)
  const [error, setError] = useState(null)
  const [transactionId, setTransactionId] = useState(null)

  const gateways = [
    { key: 'stripe', name: 'Stripe', icon: '💳', region: 'Global' },
    { key: 'paypal', name: 'PayPal', icon: '🅿️', region: 'Global' },
    { key: 'paystack', name: 'Paystack', icon: '💚', region: 'Africa' },
    { key: 'flutterwave', name: 'Flutterwave', icon: '🌍', region: 'Africa' },
    { key: 'bkash', name: 'bKash', icon: '💰', region: 'Bangladesh' },
    { key: 'alipay', name: 'Alipay', icon: '🟢', region: 'China' },
  ]

  const processPayment = async (paymentData) => {
    setProcessing(true)
    setError(null)

    try {
      const response = await fetch('/api/payment/checkout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
        body: JSON.stringify({
          gateway: selectedGateway,
          ...paymentData,
        }),
      })

      const data = await response.json()

      if (data.success) {
        setTransactionId(data.transaction_id)
        return data
      } else {
        throw new Error(data.error || 'Payment failed')
      }
    } catch (err) {
      setError(err.message)
      throw err
    } finally {
      setProcessing(false)
    }
  }

  return {
    gateways,
    selectedGateway,
    setSelectedGateway,
    processing,
    error,
    transactionId,
    processPayment,
  }
}
