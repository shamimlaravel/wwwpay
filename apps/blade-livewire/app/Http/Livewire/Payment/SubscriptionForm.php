<?php

namespace App\Http\Livewire\Payment;

use Livewire\Component;
use ShamimStack\WwwPay\Facades\Payment;
use App\Models\Subscription;

class SubscriptionForm extends Component
{
    public $plans = [];
    public $selectedPlan = null;
    public $billingCycle = 'monthly';
    public $email = '';
    public $name = '';
    public $processing = false;
    public $subscriptionId = null;
    public $error = null;
    public $success = false;

    public function mount()
    {
        $this->loadPlans();
    }

    public function loadPlans()
    {
        $this->plans = [
            ['id' => 'basic', 'name' => 'Basic', 'price' => 9.99, 'features' => ['5 Products', 'Email Support', 'Basic Analytics']],
            ['id' => 'pro', 'name' => 'Professional', 'price' => 29.99, 'features' => ['50 Products', 'Priority Support', 'Advanced Analytics', 'API Access']],
            ['id' => 'enterprise', 'name' => 'Enterprise', 'price' => 99.99, 'features' => ['Unlimited Products', '24/7 Support', 'Custom Analytics', 'API Access', 'Dedicated Manager']],
        ];
    }

    public function selectPlan($planId)
    {
        $this->selectedPlan = collect($this->plans)->firstWhere('id', $planId);
    }

    public function subscribe()
    {
        $this->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'selectedPlan' => 'required',
        ]);

        $this->processing = true;
        $this->error = null;

        try {
            $subscription = Payment::gateway('stripe')->subscribe([
                'plan_id' => $this->selectedPlan['id'],
                'email' => $this->email,
                'name' => $this->name,
                'billing_cycle' => $this->billingCycle,
            ]);

            Subscription::create([
                'user_id' => auth()->id(),
                'plan_id' => $this->selectedPlan['id'],
                'gateway' => 'stripe',
                'status' => 'active',
                'started_at' => now(),
            ]);

            $this->subscriptionId = $subscription->getSubscriptionId();
            $this->success = true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.payment.subscription-form');
    }
}
