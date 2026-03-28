import { Link, usePage } from '@inertiajs/react'
import { useState } from 'react'
import { usePayment } from '@/hooks/usePayment'

export default function Checkout() {
  const { gateways, selectedGateway, setSelectedGateway, processing, error, processPayment } = usePayment()
  
  const [step, setStep] = useState(1)
  const [email, setEmail] = useState('')
  const [cardNumber, setCardNumber] = useState('')
  const [cardExpiry, setCardExpiry] = useState('')
  const [cardCvc, setCardCvc] = useState('')
  const [transactionId, setTransactionId] = useState(null)
  
  const cart = usePage().props.cart || []
  const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)

  const handleNextStep = () => {
    if (!email) {
      alert('Please enter your email')
      return
    }
    setStep(2)
  }

  const handlePayment = async () => {
    try {
      const result = await processPayment({ amount: total, email })
      setTransactionId(result.transaction_id)
      setStep(3)
    } catch (e) {
      // Error handled by hook
    }
  }

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>
      
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          {/* Progress */}
          <div className="flex items-center justify-center mb-8">
            {[1, 2, 3].map((s) => (
              <div key={s} className="flex items-center">
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-white font-medium ${step >= s ? 'bg-blue-600' : 'bg-gray-200'}`}>
                  {s}
                </div>
                <span className={`ml-2 text-sm ${step >= s ? 'text-blue-600' : 'text-gray-500'}`}>
                  {s === 1 ? 'Gateway' : s === 2 ? 'Payment' : 'Done'}
                </span>
                {s < 3 && <div className="w-12 h-0.5 bg-gray-200 mx-4"></div>}
              </div>
            ))}
          </div>

          {/* Step 1 */}
          {step === 1 && (
            <div className="bg-white rounded-xl shadow-sm p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-6">Select Payment Method</h2>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                {gateways.map((gateway) => (
                  <label key={gateway.key} className="cursor-pointer">
                    <input
                      type="radio"
                      checked={selectedGateway === gateway.key}
                      onChange={() => setSelectedGateway(gateway.key)}
                      className="sr-only peer"
                    />
                    <div className={`border-2 rounded-xl p-4 text-center transition ${selectedGateway === gateway.key ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'}`}>
                      <span className="text-3xl mb-2 block">{gateway.icon}</span>
                      <span className="block font-medium text-gray-900">{gateway.name}</span>
                      <span className="text-xs text-gray-500">{gateway.region}</span>
                    </div>
                  </label>
                ))}
              </div>

              <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  placeholder="your@email.com"
                />
              </div>

              <button
                onClick={handleNextStep}
                className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition"
              >
                Continue to Payment
              </button>
            </div>
          )}

          {/* Step 2 */}
          {step === 2 && (
            <div className="bg-white rounded-xl shadow-sm p-6">
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl font-bold text-gray-900">Complete Payment</h2>
                <button onClick={() => setStep(1)} className="text-blue-600 hover:text-blue-700">← Back</button>
              </div>

              <div className="bg-gray-50 rounded-lg p-4 mb-6">
                <div className="flex justify-between text-lg font-bold">
                  <span>Total to Pay</span>
                  <span className="text-blue-600">${total.toFixed(2)}</span>
                </div>
              </div>

              {selectedGateway === 'stripe' && (
                <div className="space-y-4 mb-6">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                    <input
                      type="text"
                      value={cardNumber}
                      onChange={(e) => setCardNumber(e.target.value)}
                      className="w-full px-4 py-3 border border-gray-300 rounded-lg"
                      placeholder="4242 4242 4242 4242"
                    />
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">Expiry</label>
                      <input
                        type="text"
                        value={cardExpiry}
                        onChange={(e) => setCardExpiry(e.target.value)}
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg"
                        placeholder="MM/YY"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">CVC</label>
                      <input
                        type="text"
                        value={cardCvc}
                        onChange={(e) => setCardCvc(e.target.value)}
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg"
                        placeholder="123"
                      />
                    </div>
                  </div>
                </div>
              )}

              {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                  {error}
                </div>
              )}

              <button
                onClick={handlePayment}
                disabled={processing}
                className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
              >
                {processing ? 'Processing...' : `Pay $${total.toFixed(2)}`}
              </button>
            </div>
          )}

          {/* Step 3 */}
          {step === 3 && (
            <div className="bg-white rounded-xl shadow-sm p-8 text-center">
              <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg className="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <h2 className="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
              <p className="text-gray-600 mb-6">Transaction ID: {transactionId}</p>
              <Link href="/" className="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                Continue Shopping
              </Link>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-xl shadow-sm p-6 sticky top-24">
            <h3 className="font-bold text-gray-900 mb-4">Order Summary</h3>
            <div className="space-y-3 border-b border-gray-200 pb-4">
              {cart.map((item, i) => (
                <div key={i} className="flex justify-between text-sm">
                  <span>{item.name} × {item.quantity}</span>
                  <span className="font-medium">${(item.price * item.quantity).toFixed(2)}</span>
                </div>
              ))}
            </div>
            <div className="pt-4">
              <div className="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span className="text-blue-600">${total.toFixed(2)}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
