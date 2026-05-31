<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        return [
            'revenue' => [
                'total' => Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->sum('total'),
                'change' => $this->revenueChange(),
            ],
            'orders' => [
                'total' => Order::where('created_at', '>=', $thirtyDaysAgo)->count(),
                'by_status' => Order::selectRaw('status, count(*) as count')
                    ->groupBy('status')->pluck('count', 'status'),
            ],
            'customers' => [
                'total' => User::where('role', 'customer')->count(),
                'new' => User::where('role', 'customer')
                    ->where('created_at', '>=', $thirtyDaysAgo)->count(),
            ],
            'top_products' => $this->topProducts(),
            'daily_revenue' => $this->dailyRevenue(),
        ];
    }

    private function revenueChange(): float
    {
        $thisMonth = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [now()->startOfMonth(), now()])->sum('total');
        $lastMonth = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('total');

        if ($lastMonth == 0) {
            return 100;
        }

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function topProducts(): array
    {
        return OrderItem::select('product_id', 'product_name',
            DB::raw('SUM(quantity) as total_sold'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function dailyRevenue(): array
    {
        return Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }
}
