<?php

namespace App\Http\Livewire\Store;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;

class CartManager extends Component
{
    public $cart = [];
    public $total = 0;
    public $count = 0;

    protected $listeners = ['addToCart', 'removeFromCart', 'clearCart'];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
        $this->calculateTotal();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return;
        }

        $cart = session()->get('cart', []);
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image,
            ];
        }

        session()->put('cart', $cart);
        $this->cart = $cart;
        $this->calculateTotal();
        
        $this->emit('cartUpdated', $this->count);
    }

    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            $this->cart = $cart;
            $this->calculateTotal();
            $this->emit('cartUpdated', $this->count);
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $quantity;
            }
            session()->put('cart', $cart);
            $this->cart = $cart;
            $this->calculateTotal();
            $this->emit('cartUpdated', $this->count);
        }
    }

    public function clearCart()
    {
        session()->forget('cart');
        $this->cart = [];
        $this->total = 0;
        $this->count = 0;
        $this->emit('cartUpdated', 0);
    }

    protected function calculateTotal()
    {
        $this->total = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
        
        $this->count = collect($this->cart)->sum('quantity');
    }

    public function render()
    {
        return view('livewire.store.cart-manager');
    }
}
