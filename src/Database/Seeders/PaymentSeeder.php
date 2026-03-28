<?php

namespace ShamimStack\WwwPay\Database\Seeders;

use Illuminate\Database\Seeder;
use ShamimStack\WwwPay\Models\Transaction;
use ShamimStack\WwwPay\Models\Subscription;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->createSampleTransactions();
        $this->createSampleSubscriptions();
    }

    protected function createSampleTransactions(): void
    {
        $gateways = ['stripe', 'paypal', 'paystack', 'bkash', 'flutterwave'];
        $statuses = ['pending', 'success', 'failed'];
        $currencies = ['USD', 'EUR', 'GBP', 'BDT', 'NGN', 'INR'];

        foreach (range(1, 50) as $i) {
            $gateway = $gateways[array_rand($gateways)];
            $status = $statuses[array_rand($statuses)];
            $currency = $currencies[array_rand($currencies)];
            $amount = fake()->randomFloat(2, 10, 1000);

            Transaction::create([
                'gateway' => $gateway,
                'type' => fake()->randomElement(['payment', 'refund']),
                'amount' => $amount,
                'currency' => $currency,
                'status' => $status,
                'transaction_id' => $gateway . '_' . uniqid(),
                'metadata' => [
                    'order_id' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'customer_email' => fake()->email(),
                    'ip_address' => fake()->ipv4(),
                ],
            ]);
        }
    }

    protected function createSampleSubscriptions(): void
    {
        $gateways = ['stripe', 'paypal'];
        $plans = ['basic', 'pro', 'enterprise'];
        $statuses = ['active', 'cancelled', 'past_due'];

        foreach (range(1, 20) as $i) {
            $gateway = $gateways[array_rand($gateways)];
            $status = $statuses[array_rand($statuses)];

            Subscription::create([
                'gateway' => $gateway,
                'plan_id' => $plans[array_rand($plans)] . '_' . fake()->randomElement(['monthly', 'yearly']),
                'customer_id' => 'cust_' . uniqid(),
                'status' => $status,
                'started_at' => fake()->dateTimeBetween('-6 months', 'now'),
                'ends_at' => $status === 'active' 
                    ? fake()->dateTimeBetween('now', '+1 year')
                    : fake()->dateTimeBetween('-1 month', 'now'),
                'metadata' => [
                    'customer_email' => fake()->email(),
                    'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
                ],
            ]);
        }
    }
}
