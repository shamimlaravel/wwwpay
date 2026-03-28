<?php

namespace ShamimStack\AllInOnePayment\Reporting;

use ShamimStack\AllInOnePayment\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TransactionReporter
{
    public function getSummary(array $filters = []): array
    {
        $query = Transaction::query();

        if (isset($filters['gateway'])) {
            $query->byGateway($filters['gateway']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['customer_id'])) {
            $query->byCustomer($filters['customer_id']);
        }

        $totalCount = $query->count();
        $totalAmount = $query->sum('amount');
        $successfulCount = (clone $query)->successful()->count();
        $failedCount = (clone $query)->where('status', Transaction::STATUS_FAILED)->count();
        $refundedAmount = $query->sum('refunded_amount');

        return [
            'total_transactions' => $totalCount,
            'successful_transactions' => $successfulCount,
            'failed_transactions' => $failedCount,
            'total_amount' => round($totalAmount, 2),
            'refunded_amount' => round($refundedAmount, 2),
            'net_amount' => round($totalAmount - $refundedAmount, 2),
            'success_rate' => $totalCount > 0 ? round(($successfulCount / $totalCount) * 100, 2) : 0,
        ];
    }

    public function getGatewayBreakdown(array $filters = []): Collection
    {
        $query = Transaction::query();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->select(
            'gateway',
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as successful_transactions', [Transaction::STATUS_COMPLETED]),
            DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed_transactions', [Transaction::STATUS_FAILED]),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('SUM(refunded_amount) as refunded_amount')
        )
        ->groupBy('gateway')
        ->orderBy('total_amount', 'desc')
        ->get();
    }

    public function getCurrencyBreakdown(array $filters = []): Collection
    {
        $query = Transaction::query();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->select(
            'currency',
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(amount) as total_amount')
        )
        ->groupBy('currency')
        ->orderBy('total_amount', 'desc')
        ->get();
    }

    public function getDailyVolume(int $days = 30, ?string $gateway = null): Collection
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $query = Transaction::query()
            ->successful()
            ->byDateRange($startDate, $endDate);

        if ($gateway) {
            $query->byGateway($gateway);
        }

        return $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(amount) as total_amount')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'asc')
        ->get();
    }

    public function getHourlyVolume(int $hours = 24, ?string $gateway = null): Collection
    {
        $startDate = Carbon::now()->subHours($hours);
        $endDate = Carbon::now();

        $query = Transaction::query()
            ->successful()
            ->byDateRange($startDate, $endDate);

        if ($gateway) {
            $query->byGateway($gateway);
        }

        return $query->select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(amount) as total_amount')
        )
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('hour', 'asc')
        ->get();
    }

    public function getTopCustomers(int $limit = 10, array $filters = []): Collection
    {
        $query = Transaction::query()->successful();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->select(
            'customer_id',
            'customer_email',
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('AVG(amount) as average_amount')
        )
        ->whereNotNull('customer_id')
        ->groupBy('customer_id', 'customer_email')
        ->orderBy('total_amount', 'desc')
        ->limit($limit)
        ->get();
    }

    public function getFailedTransactionsReport(array $filters = []): Collection
    {
        $query = Transaction::query()
            ->where('status', Transaction::STATUS_FAILED);

        if (isset($filters['gateway'])) {
            $query->byGateway($filters['gateway']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->select(
            'id',
            'gateway',
            'gateway_transaction_id',
            'amount',
            'currency',
            'customer_email',
            'error_message',
            'created_at'
        )
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function getRefundsReport(array $filters = []): Collection
    {
        $query = Transaction::query()
            ->where('status', 'like', '%refund%');

        if (isset($filters['gateway'])) {
            $query->byGateway($filters['gateway']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->select(
            'id',
            'gateway',
            'gateway_transaction_id',
            'amount',
            'refunded_amount',
            'currency',
            'customer_email',
            'refunded_at',
            'created_at'
        )
        ->orderBy('refunded_at', 'desc')
        ->get();
    }

    public function getChargebackReport(array $filters = []): Collection
    {
        $query = Transaction::query()
            ->where('status', 'chargeback');

        if (isset($filters['gateway'])) {
            $query->byGateway($filters['gateway']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->select(
            'id',
            'gateway',
            'gateway_transaction_id',
            'amount',
            'currency',
            'customer_email',
            'created_at'
        )
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function getGrowthMetrics(int $days = 30): array
    {
        $currentPeriodStart = Carbon::now()->subDays($days)->startOfDay();
        $previousPeriodStart = Carbon::now()->subDays($days * 2)->startOfDay();
        $previousPeriodEnd = Carbon::now()->subDays($days)->endOfDay();

        $currentStats = $this->getSummary([
            'start_date' => $currentPeriodStart,
            'end_date' => Carbon::now()->endOfDay(),
        ]);

        $previousStats = $this->getSummary([
            'start_date' => $previousPeriodStart,
            'end_date' => $previousPeriodEnd,
        ]);

        return [
            'period' => [
                'current' => [
                    'start' => $currentPeriodStart->toDateString(),
                    'end' => Carbon::now()->toDateString(),
                    'days' => $days,
                ],
                'previous' => [
                    'start' => $previousPeriodStart->toDateString(),
                    'end' => $previousPeriodEnd->toDateString(),
                    'days' => $days,
                ],
            ],
            'transactions' => [
                'current' => $currentStats['total_transactions'],
                'previous' => $previousStats['total_transactions'],
                'growth' => $this->calculateGrowth($currentStats['total_transactions'], $previousStats['total_transactions']),
            ],
            'volume' => [
                'current' => $currentStats['total_amount'],
                'previous' => $previousStats['total_amount'],
                'growth' => $this->calculateGrowth($currentStats['total_amount'], $previousStats['total_amount']),
            ],
            'success_rate' => [
                'current' => $currentStats['success_rate'],
                'previous' => $previousStats['success_rate'],
            ],
        ];
    }

    protected function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    public function exportToCSV(array $filters = []): string
    {
        $transactions = Transaction::query();

        if (isset($filters['gateway'])) {
            $transactions->byGateway($filters['gateway']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $transactions->byDateRange($filters['start_date'], $filters['end_date']);
        }

        $transactions = $transactions->orderBy('created_at', 'desc')->get();

        $csv = "ID,Gateway,Transaction ID,Order ID,Amount,Currency,Status,Customer Email,Error Message,Created At\n";

        foreach ($transactions as $tx) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f,%s,%s,%s,%s,%s\n",
                $tx->id,
                $tx->gateway,
                $tx->gateway_transaction_id ?? '',
                $tx->order_id ?? '',
                $tx->amount,
                $tx->currency,
                $tx->status,
                $tx->customer_email ?? '',
                str_replace(',', ';', $tx->error_message ?? ''),
                $tx->created_at->toDateTimeString()
            );
        }

        return $csv;
    }
}
