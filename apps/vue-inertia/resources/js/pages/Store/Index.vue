<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <Link href="/" class="flex items-center gap-2">
              <span class="text-2xl">🛒</span>
              <span class="font-bold text-xl text-gray-900">Demo Store</span>
            </Link>
          </div>
          <div class="flex items-center gap-4">
            <Link href="/checkout" class="relative p-2 text-gray-500 hover:text-gray-900">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
              <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                {{ cartCount }}
              </span>
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-5xl font-bold text-white mb-4">Welcome to Demo Store</h1>
        <p class="text-xl text-blue-100 mb-8">Shop with 33+ payment methods worldwide</p>
        <div class="flex gap-4">
          <Link href="#products" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
            Browse Products
          </Link>
          <Link href="/payment/demo" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition">
            Payment Demos
          </Link>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Products</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="product in products" :key="product.id" class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group">
          <div class="aspect-square bg-gray-100 flex items-center justify-center">
            <span class="text-6xl">{{ product.icon }}</span>
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">
              {{ product.name }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">{{ product.description }}</p>
            <div class="flex items-center justify-between mt-3">
              <span class="text-xl font-bold text-gray-900">${{ product.price }}</span>
              <button @click="addToCart(product)" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                Add to Cart
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Demo Links -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Payment Demos</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Link href="/payment/subscription" class="bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl p-6 text-white hover:shadow-lg transition">
          <span class="text-4xl mb-4 block">🔄</span>
          <h3 class="text-xl font-bold mb-2">Subscription Billing</h3>
          <p class="text-purple-100">Create recurring payment plans</p>
        </Link>
        <Link href="/payment/p2p" class="bg-gradient-to-br from-green-500 to-teal-500 rounded-xl p-6 text-white hover:shadow-lg transition">
          <span class="text-4xl mb-4 block">💸</span>
          <h3 class="text-xl font-bold mb-2">P2P Transfers</h3>
          <p class="text-green-100">Send money to friends</p>
        </Link>
        <Link href="/payment/b2b" class="bg-gradient-to-br from-orange-500 to-red-500 rounded-xl p-6 text-white hover:shadow-lg transition">
          <span class="text-4xl mb-4 block">🏢</span>
          <h3 class="text-xl font-bold mb-2">B2B Invoicing</h3>
          <p class="text-orange-100">Create business invoices</p>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  products: Array,
  cartCount: Number,
})

const products = ref(props.products || [
  { id: 1, name: 'Smart Watch Pro', description: 'Latest smartwatch', price: 299, icon: '📱' },
  { id: 2, name: 'Wireless Headphones', description: 'Premium audio', price: 199, icon: '🎧' },
  { id: 3, name: 'Ultra Laptop', description: 'Powerful computing', price: 1299, icon: '💻' },
  { id: 4, name: 'Classic Watch', description: 'Elegant timepiece', price: 449, icon: '⌚' },
])

const addToCart = (product) => {
  // Implementation would interact with cart store
  console.log('Added to cart:', product)
}
</script>
