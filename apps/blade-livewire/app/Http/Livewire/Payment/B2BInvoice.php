<?php

namespace App\Http\Livewire\Payment;

use Livewire\Component;
use ShamimStack\WwwPay\B2B\B2BPayment;

class B2BInvoice extends Component
{
    public $clientName = '';
    public $clientEmail = '';
    public $clientAddress = '';
    public $items = [];
    public $newItem = ['description' => '', 'quantity' => 1, 'price' => 0];
    public $currency = 'USD';
    public $dueDate = '';
    public $notes = '';
    public $processing = false;
    public $invoiceId = null;
    public $error = null;
    public $success = false;
    
    public $tab = 'invoice';

    public function mount()
    {
        $this->addItem();
        $this->dueDate = now()->addDays(30)->format('Y-m-d');
    }

    public function addItem()
    {
        $this->items[] = [
            'id' => uniqid(),
            'description' => '',
            'quantity' => 1,
            'price' => 0,
        ];
    }

    public function removeItem($id)
    {
        $this->items = collect($this->items)->reject(fn($item) => $item['id'] === $id)->values()->toArray();
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(fn($item) => ($item['quantity'] ?? 0) * ($item['price'] ?? 0));
    }

    public function getTaxProperty()
    {
        return $this->subtotal * 0.1;
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->tax;
    }

    public function createInvoice()
    {
        $this->validate([
            'clientName' => 'required|string',
            'clientEmail' => 'required|email',
            'items' => 'required|array|min:1',
        ]);

        $this->processing = true;
        $this->error = null;

        try {
            $invoice = B2BPayment::createInvoice([
                'amount' => $this->total,
                'currency' => $this->currency,
                'client_name' => $this->clientName,
                'client_email' => $this->clientEmail,
                'due_date' => $this->dueDate,
                'items' => $this->items,
                'notes' => $this->notes,
            ]);

            $this->invoiceId = $invoice->getTransactionId();
            $this->success = true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function createWireTransfer()
    {
        $this->validate([
            'clientName' => 'required|string',
            'clientEmail' => 'required|email',
            'currency' => 'required|string|size:3',
        ]);

        $this->processing = true;
        $this->error = null;

        try {
            $transfer = B2BPayment::wireTransfer([
                'amount' => $this->total,
                'currency' => $this->currency,
                'beneficiary_name' => $this->clientName,
                'beneficiary_email' => $this->clientEmail,
            ]);

            $this->invoiceId = $transfer->getTransactionId();
            $this->success = true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.payment.b2b-invoice');
    }
}
