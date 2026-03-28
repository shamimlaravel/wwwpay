<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main Content -->
      <div class="lg:col-span-2">
        <!-- Progress -->
        <div class="flex items-center justify-center mb-8">
          <div class="flex items-center gap-4">
            <div class="flex items-center">
              <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white font-medium', step >= 1 ? 'bg-blue-600' : 'bg-gray-200']">1</div>
              <span class="ml-2 text-sm" :class="step >= 1 ? 'text-blue-600' : 'text-gray-500'">Gateway</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200"></div>
            <div class="flex items-center">
              <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white font-medium', step >= 2 ? 'bg-blue-600' : 'bg-gray-200']">2</div>
              <span class="ml-2 text-sm" :class="step >= 2 ? 'text-blue-600' : 'text-gray-500'">Payment</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200"></div>
            <div class="flex items-center">
              <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white font-medium', step >= 3 ? 'bg-blue-600' : 'bg-gray-200']">3</div>
              <span class="ml-2 text-sm" :class="step >= 3 ? 'text-blue-600' : 'text-gray-500'">Done</span>
            </div>
          </div>
        </div>

        <!-- Step 1: Gateway -->
        <div v-if="step === 1" class="bg-white rounded-xl shadow-sm p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Select Payment Method</h2>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <label v-for="gateway in gateways" :key="gateway.key" class="cursor-pointer">
              <input type="radio" v-model="selectedGateway" :value="gateway.key" class="sr-only peer">
              <div class="border-2 border-gray-200 rounded-xl p-4 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300">
                <span class="text-3xl mb-2 block">{{ gateway.icon }}</span>
                <span class="block font-medium text-gray-900">{{ gateway.name }}</span>
                <span class="text-xs text-gray-500">{{ gateway.region }}</span>
              </div>
            </label>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input v-model="email" type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="your@email.com">
          </div>

          <button @click="nextStep" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
            Continue to Payment
          </button>
        </div>

        <!-- Step 2: Payment -->
        <div v-if="step === 2" class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">Complete Payment</h2>
            <button @click="step = 1" class="text-blue-600 hover:text-blue-700">← Back</button>
          </div>

          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between text-lg font-bold">
              <span>Total to Pay</span>
              <span class="text-blue-600">${{ total.toFixed(2) }} {{ currency }}</span>
            </div>
          </div>

          <div v-if="selectedGateway === 'stripe'" class="space-y-4 mb-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
              <input v-model="cardNumber" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="4242 4242 4242 4242">
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Expiry</label>
                <input v-model="cardExpiry" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="MM/YY">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CVC</label>
                <input v-model="cardCvc" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="123">
              </div>
            </div>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
          </div>

          <button @click="processPayment" :disabled="processing" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50">
            {{ processing ? 'Processing...' : `Pay $${total.toFixed(2)}` }}
          </button>
        </div>

        <!-- Step 3: Success -->
        <div v-if="step === 3" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
          <p class="text-gray-600 mb-6">Transaction ID: {{ transactionId }}</p>
          <Link href="/" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
            Continue Shopping
          </Link>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
          <h3 class="font-bold text-gray-900 mb-4">Order Summary</h3>
          <div class="space-y-3 border-b border-gray-200 pb-4">
            <div v-for="item in cart" :key="item.id" class="flex justify-between text-sm">
              <span>{{ item.name }} × {{ item.quantity }}</span>
              <span class="font-medium">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
          <div class="pt-4">
            <div class="flex justify-between text-lg font-bold">
              <span>Total</span>
              <span class="text-blue-600">${{ total.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  cart: Array,
})

const step = ref(1)
const selectedGateway = ref('stripe')
const email = ref('')
const cardNumber = ref('')
const cardExpiry = ref('')
const cardCvc = ref('')
const processing = ref(false)
const error = ref(null)
const transactionId = ref(null)
const total = ref(0)
const currency = ref('USD')

const gateways = [
  { key: 'stripe', name: 'Stripe', icon: '💳', region: 'Global' },
  { key: 'paypal', name: 'PayPal', icon: '🅿️', region: 'Global' },
  { key: 'paystack', name: 'Paystack', icon: '💚', region: 'Africa' },
  { key: 'flutterwave', name: 'Flutterwave', icon: '🌍', region: 'Africa' },
]

const cart = computed(() => props.cart || [])
const totalComputed = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const nextStep = () => {
  if (!email.value) {
    error.value = 'Please enter your email'
    return
  }
  error.value = null
  step.value = 2
}

const processPayment = async () => {
  processing.value = true
  error.value = null
  
  try {
    // Simulate payment
    await new Promise(resolve => setTimeout(resolve, 2000))
    transactionId.value = 'TXN_' + Math.random().toString(36).substr(2, 9).toUpperCase()
    step.value = 3
  } catch (e) {
    error.value = e.message || 'Payment failed'
  } finally {
    processing.value = false
  }
}
</script>
