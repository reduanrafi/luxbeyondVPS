<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $range = $request->input('range', 'all'); // all, today, week, month, year

        // Helper to apply date filter
        $applyFilter = function ($query, $dateColumn = 'created_at') use ($range) {
            if ($range === 'today') $query->whereDate($dateColumn, Carbon::today());
            elseif ($range === 'week') $query->whereBetween($dateColumn, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            elseif ($range === 'month') $query->whereMonth($dateColumn, Carbon::now()->month)->whereYear($dateColumn, Carbon::now()->year);
            elseif ($range === 'year') $query->whereYear($dateColumn, Carbon::now()->year);
        };

        // 1. Total Revenue (Shop Orders + Product Requests)
        // Shop Orders (Paid)
        $shopRevenueQuery = Order::where(function($q) {
             $q->where('payment_status', 'paid')->orWhere('payment_status', 'partial');
        })->where('status_id', '!=', 6); // Assuming 6 is Cancelled, ideally use status name or config
        $applyFilter($shopRevenueQuery);
        $shopRevenue = $shopRevenueQuery->sum('total'); // Or sum of payments if strictly paid amount

        // Product Requests (Paid)
        $requestRevenueQuery = ProductRequest::where(function($q) {
            $q->where('payment_status', 'paid')->orWhere('payment_status', 'partial');
        });
        $applyFilter($requestRevenueQuery);
        $requestRevenue = $requestRevenueQuery->sum('total_amount_bdt') + $requestRevenueQuery->sum('declared_shipping_cost'); // Adjust based on strict revenue definition
        
        // Refined Revenue: Sum of actual payments might be better if available, but using total of paid orders for now
        $totalRevenue = $shopRevenue + $requestRevenue;


        // 2. Total Orders (Shop)
        $ordersQuery = Order::query();
        $applyFilter($ordersQuery);
        $totalOrders = $ordersQuery->count();

        // 3. Product Requests 
        $requestsQuery = ProductRequest::query();
        $applyFilter($requestsQuery);
        $totalRequests = $requestsQuery->count();

        // 4. Total Customers
        // Using Spatie's role scope if available, or whereHas roles
        $customersQuery = User::role('Customer'); 
        $applyFilter($customersQuery);
        $totalCustomers = $customersQuery->count();

        // 5. Pending Counts for Sidebar
        $pendingOrders = Order::where('status', 'Pending')->count(); // Or usage of status_id if preferred, but string is often used
        // Check if using status_id relationships:
        $pendingOrders = Order::whereHas('status', function($q) {
             $q->where('name', 'Pending');
        })->count();

        $pendingRequests = ProductRequest::whereHas('orderStatus', function($q) {
            $q->where('name', 'Pending');
        })->count();

        return response()->json([
            'revenue' => $totalRevenue,
            'orders' => $totalOrders,
            'requests' => $totalRequests,
            'customers' => $totalCustomers,
            'pending_orders' => $pendingOrders,
            'pending_requests' => $pendingRequests
        ]);
    }

    public function charts(Request $request)
    {
        // 1. Revenue History (Last 6 months)
        $months = collect([]);
        $revenueData = collect([]);
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');
            $year = $date->year;
            $month = $date->month;

            $shopRev = Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('payment_status', 'paid')
                ->sum('total');

            $reqRev = ProductRequest::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('payment_status', 'paid')
                ->sum('total_amount_bdt');

            $months->push($monthName);
            $revenueData->push($shopRev + $reqRev);
        }


        // 2. Order Success Ratio
        $totalOrders = Order::count();
        $completedOrders = Order::whereHas('status', function($q) {
            $q->where('name', 'Completed')->orWhere('name', 'Delivered');
        })->count();
        $cancelledOrders = Order::whereHas('status', function($q) {
            $q->where('name', 'Cancelled');
        })->count();
        $otherOrders = $totalOrders - $completedOrders - $cancelledOrders;

        // 3. Request vs Shop Ratio (Count)
        $shopCount = Order::count();
        $requestCount = ProductRequest::count();


        return response()->json([
            'revenue_history' => [
                'labels' => $months,
                'data' => $revenueData
            ],
            'order_status_distribution' => [
                'completed' => $completedOrders,
                'cancelled' => $cancelledOrders,
                'processing' => $otherOrders // Active/Pending
            ],
            'type_distribution' => [
                'shop' => $shopCount,
                'request' => $requestCount
            ]
        ]);
    }

    public function topProducts(Request $request)
    {
        // Top selling products only from Shop Orders for now
        $topProducts = OrderItem::select('product_id', DB::raw('sum(quantity) as total_sold'), DB::raw('sum(subtotal) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->take(5)
            ->with(['product:id,name,slug,image']) // Changed thumbnail to image
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product ? $item->product->name : 'Unknown Product',
                    'sales' => $item->total_sold,
                    'revenue' => $item->total_revenue,
                    'image' => $item->product ? $item->product->image_url : null
                ];
            });

        return response()->json($topProducts);
    }
}
