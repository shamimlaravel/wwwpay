<?php

namespace App\Http\Livewire\Payment;

use Livewire\Component;
use Illuminate\Http\Request;
use ShamimStack\WwwPay\Facades\Payment;
use ShamimStack\WwwPay\Helpers\CurrencyConverter;

class Checkout extends Component
{
    public $cart = [];
    public $total = 0;
    public $currency = 'USD';
    public $selectedGateway = 'stripe';
    public $gateways = [];
    public $step = 1;
    
    public $cardNumber = '';
    public $cardExpiry = '';
    public $cardCvc = '';
    public $cardName = '';
    
    public $email = '';
    public $phone = '';
    
    public $processing = false;
    public $error = null;
    public $transactionId = null;

    protected $rules = [
        'email' => 'required|email',
        'selectedGateway' => 'required|string',
    ];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
        $this->calculateTotal();
        $this->loadGateways();
    }

    public function loadGateways()
    {
        $this->gateways = [
            ['key' => 'stripe', 'name' => 'Stripe', 'icon' => '💳', 'region' => 'Global'],
            ['key' => 'paypal', 'name' => 'PayPal', 'icon' => '🅿️', 'region' => 'Global'],
            ['key' => 'paystack', 'name' => 'Paystack', 'icon' => '💚', 'region' => 'Africa'],
        ];
    }

    public function calculateTotal()
    {
        $this->total = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    public function selectGateway($gateway)
    {
        $this->selectedGateway = $gateway;
    }

    public function nextStep()
    {
        $this->validate();
        $this->step = 2;
    }

    public function prevStep()
    {
        $this->step = 1;
    }

    public function processPayment()
    {
        $this->processing = true;
        $this->error = null;

        try {
            $data = $this->preparePaymentData();
            $response = Payment::gateway($this->selectedGateway)->pay($data);

            if ($response->isRedirect()) {
                return redirect($response->getRedirectUrl());
            }

            if ($response->isSuccessful()) {
                $this->transactionId = $response->getTransactionId();
                $this->createOrder();
                session()->forget('cart');
                $this->step = 3;
            } else {
                $this->error = $response->getErrorMessage() ?? 'Payment failed';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    protected function preparePaymentData(): array
    {
        $data = [
            'amount' => $this->total,
            'currency' => $this->currency,
            'return_url' => route('payment.success'),
            'cancel_url' => route('payment.cancel'),
            'email' => $this->email,
        ];

        if ($this->selectedGateway === 'stripe') {
            $data['payment_method'] = 'pm_card_visa';
        } elseif (in_array($this->selectedGateway, ['paystack', 'flutterwave'])) {
            $data['phone'] = $this->phone;
        }

        return $data;
    }

    protected function createOrder()
    {
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $this->total,
            'currency' => $this->currency,
            'status' => 'success',
            'gateway' => $this->selectedGateway,
            'transaction_id' => $this->transactionId,
            'items' => json_encode($this->cart),
        ]);

        return $order;
    }

    public function formatCardNumber()
    {
        $this->cardNumber = preg_replace('/\D/', '', $this->cardNumber);
        $this->cardNumber = wordwrap($this->cardNumber, 4, ' ', true);
        $this->cardNumber = substr($this->cardNumber, 0, 19);
    }

    public function formatExpiry()
    {
        $this->cardExpiry = preg_replace('/\D/', '', $this->cardExpiry);
        if (strlen($this->cardExpiry) >= 2) {
            $this->cardExpiry = substr($this->cardExpiry, 0, 2) . '/' . substr($this->cardExpiry, 2, 2);
        }
    }

    public function render()
    {
        return view('livewire.payment.checkout');
    }
}
