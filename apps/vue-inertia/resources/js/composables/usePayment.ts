import { defineStore } from 'pinia'

export const usePayment = defineStore('payment', {
  state: () => ({
    selectedGateway: 'stripe',
    amount: 0,
    currency: 'USD',
    processing: false,
    error: null,
    transactionId: null,
  }),

  getters: {
    gateways: () => [
      { key: 'stripe', name: 'Stripe', icon: '💳', region: 'Global' },
      { key: 'paypal', name: 'PayPal', icon: '🅿️', region: 'Global' },
      { key: 'paystack', name: 'Paystack', icon: '💚', region: 'Africa' },
      { key: 'flutterwave', name: 'Flutterwave', icon: '🌍', region: 'Africa' },
      { key: 'bkash', name: 'bKash', icon: '💰', region: 'Bangladesh' },
      { key: 'alipay', name: 'Alipay', icon: '🟢', region: 'China' },
    ],
  },

  actions: {
    async processPayment(paymentData) {
      this.processing = true
      this.error = null

      try {
        const response = await fetch('/api/payment/checkout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          },
          body: JSON.stringify({
            gateway: this.selectedGateway,
            ...paymentData,
          }),
        })

        const data = await response.json()

        if (data.success) {
          this.transactionId = data.transaction_id
          return data
        } else {
          this.error = data.error || 'Payment failed'
          throw new Error(this.error)
        }
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.processing = false
      }
    },

    setGateway(gateway) {
      this.selectedGateway = gateway
    },
  },
})
