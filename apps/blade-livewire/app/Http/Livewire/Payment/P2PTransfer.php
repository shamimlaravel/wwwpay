<?php

namespace App\Http\Livewire\Payment;

use Livewire\Component;
use ShamimStack\WwwPay\P2P\P2PPayment;

class P2PTransfer extends Component
{
    public $recipientId = '';
    public $amount = 0;
    public $currency = 'USD';
    public $note = '';
    public $transferType = 'send';
    public $processing = false;
    public $transactionId = null;
    public $error = null;
    public $success = false;

    public $splitAmount = 0;
    public $splitParticipants = [];
    public $newParticipant = '';

    public $escrowAmount = 0;
    public $releasedTo = '';

    public function sendMoney()
    {
        $this->validate([
            'recipientId' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
        ]);

        $this->processing = true;
        $this->error = null;

        try {
            $response = P2PPayment::sendMoney([
                'amount' => $this->amount,
                'currency' => $this->currency,
                'recipient_id' => $this->recipientId,
                'note' => $this->note,
            ]);

            $this->transactionId = $response->getTransactionId();
            $this->success = true;
            $this->reset(['recipientId', 'amount', 'note']);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function requestMoney()
    {
        $this->validate([
            'recipientId' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
        ]);

        $this->processing = true;
        $this->error = null;

        try {
            $response = P2PPayment::requestMoney([
                'amount' => $this->amount,
                'currency' => $this->currency,
                'from_user' => $this->recipientId,
                'note' => $this->note,
            ]);

            $this->transactionId = $response->getTransactionId();
            $this->success = true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function addParticipant()
    {
        if (!empty($this->newParticipant) && !in_array($this->newParticipant, $this->splitParticipants)) {
            $this->splitParticipants[] = $this->newParticipant;
            $this->newParticipant = '';
        }
    }

    public function removeParticipant($index)
    {
        unset($this->splitParticipants[$index]);
        $this->splitParticipants = array_values($this->splitParticipants);
    }

    public function splitPayment()
    {
        $this->validate([
            'splitAmount' => 'required|numeric|min:1',
            'splitParticipants' => 'required|array|min:2',
        ]);

        $this->processing = true;
        $this->error = null;

        try {
            $response = P2PPayment::splitPayment([
                'total_amount' => $this->splitAmount,
                'currency' => $this->currency,
                'participants' => $this->splitParticipants,
            ]);

            $this->transactionId = $response->getTransactionId();
            $this->success = true;
            $this->reset(['splitAmount', 'splitParticipants']);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function createEscrow()
    {
        $this->validate([
            'escrowAmount' => 'required|numeric|min:1',
            'releasedTo' => 'required|string',
        ]);

        $this->processing = true;
        $this->error = null;

        try {
            $response = P2PPayment::escrow([
                'amount' => $this->escrowAmount,
                'currency' => $this->currency,
                'released_to' => $this->releasedTo,
            ]);

            $this->transactionId = $response->getTransactionId();
            $this->success = true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.payment.p2p-transfer');
    }
}
