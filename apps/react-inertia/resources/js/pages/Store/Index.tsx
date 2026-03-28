import { Link } from '@inertiajs/react'
import { useState } from 'react'

export default function StoreIndex({ products = [], cartCount = 0 }) {
  const [cart, setCart] = useState([])

  const sampleProducts = products.length > 0 ? products : [
    { id: 1, name: 'Smart Watch Pro', description: 'Latest smartwatch', price: 299, icon: '📱' },
    { id: 2, name: 'Wireless Headphones', description: 'Premium audio', price: 199, icon: '🎧' },
    { id: 3, name: 'Ultra Laptop', description: 'Powerful computing', price: 1299, icon: '💻' },
    { id: 4, name: 'Classic Watch', description: 'Elegant timepiece', price: 449, icon: '⌚' },
  ]

  const addToCart = (product) => {
    setCart([...cart, product])
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Navigation */}
      <nav className="bg-white shadow-sm sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex items-center">
              <Link href="/" className="flex items-center gap-2">
                <span className="text-2xl">🛒</span>
                <span className="font-bold text-xl text-gray-900">Demo Store</span>
              </Link>
            </div>
            <div className="flex items-center gap-4">
              <Link href="/checkout" className="relative p-2 text-gray-500 hover:text-gray-900">
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                {cart.length > 0 && (
                  <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    {cart.length}
                  </span>
                )}
              </Link>
            </div>
          </div>
        </div>
      </nav>

      {/* Hero */}
      <div className="bg-gradient-to-r from-blue-600 to-purple-600">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <h1 className="text-5xl font-bold text-white mb-4">Welcome to Demo Store</h1>
          <p className="text-xl text-blue-100 mb-8">Shop with 33+ payment methods worldwide</p>
          <div className="flex gap-4">
            <Link href="#products" className="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
              Browse Products
            </Link>
            <Link href="/payment/demo" className="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition">
              Payment Demos
            </Link>
          </div>
        </div>
      </div>

      {/* Products */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Featured Products</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {sampleProducts.map((product) => (
            <div key={product.id} className="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group">
              <div className="aspect-square bg-gray-100 flex items-center justify-center">
                <span className="text-6xl">{product.icon}</span>
              </div>
              <div className="p-4">
                <h3 className="font-semibold text-gray-900 group-hover:text-blue-600 transition">
                  {product.name}
                </h3>
                <p className="text-sm text-gray-500 mt-1">{product.description}</p>
                <div className="flex items-center justify-between mt-3">
                  <span className="text-xl font-bold text-gray-900">${product.price}</span>
                  <button
                    onClick={() => addToCart(product)}
                    className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium"
                  >
                    Add to Cart
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Demo Links */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Payment Demos</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Link href="/payment/subscription" className="bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl p-6 text-white hover:shadow-lg transition">
            <span className="text-4xl mb-4 block">🔄</span>
            <h3 className="text-xl font-bold mb-2">Subscription Billing</h3>
            <p className="text-purple-100">Create recurring payment plans</p>
          </Link>
          <Link href="/payment/p2p" className="bg-gradient-to-br from-green-500 to-teal-500 rounded-xl p-6 text-white hover:shadow-lg transition">
            <span className="text-4xl mb-4 block">💸</span>
            <h3 className="text-xl font-bold mb-2">P2P Transfers</h3>
            <p className="text-green-100">Send money to friends</p>
          </Link>
          <Link href="/payment/b2b" className="bg-gradient-to-br from-orange-500 to-red-500 rounded-xl p-6 text-white hover:shadow-lg transition">
            <span className="text-4xl mb-4 block">🏢</span>
            <h3 className="text-xl font-bold mb-2">B2B Invoicing</h3>
            <p className="text-orange-100">Create business invoices</p>
          </Link>
        </div>
      </div>
    </div>
  )
}
