<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class StoreController extends Controller
{
    public function index()
    {
        return view('store.index');
    }

    public function checkout()
    {
        return view('payments.checkout');
    }

    public function paymentSuccess(Request $request)
    {
        $transactionId = $request->session()->get('transaction_id');
        return view('payments.success', compact('transactionId'));
    }

    public function paymentCancel(Request $request)
    {
        $error = $request->session()->get('error', 'Payment was cancelled');
        return view('payments.cancel', compact('error'));
    }

    public function orders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.orders', compact('orders'));
    }

    public function subscription()
    {
        return view('payments.subscription');
    }

    public function p2p()
    {
        return view('payments.p2p');
    }

    public function b2b()
    {
        return view('payments.b2b');
    }
}
