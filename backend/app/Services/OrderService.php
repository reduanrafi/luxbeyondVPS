<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductRequest;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Convert a ProductRequest to a formal Order.
     */
    public function convertRequestToOrder(ProductRequest $productRequest, array $data = [])
    {
        return DB::transaction(function () use ($productRequest, $data) {
            $userId = $productRequest->user_id;
            
            // Determine status
            $statusId = $data['status_id'] ?? $productRequest->status_id;
            $statusName = $data['status'] ?? $productRequest->status;
            
            if (!$statusId) {
                $defaultStatus = OrderStatus::where('is_default', true)->first() 
                               ?? OrderStatus::where('name', 'pending')->first();
                $statusId = $defaultStatus?->id;
                $statusName = $defaultStatus?->name ?? 'pending';
            }

            // Create the Order
            $order = Order::create([
                'user_id' => $userId,
                'status_id' => $statusId,
                'status' => $statusName,
                'subtotal' => $productRequest->total_amount_bdt, // Using total as subtotal for requests
                'total' => $productRequest->total_amount_bdt,
                'currency' => 'BDT',
                'shipping' => ($productRequest->declared_shipping_cost ?? 0) + ($productRequest->delivery_charge ?? 0),
                'tax' => $productRequest->tax ?? 0,
                'payment_method' => $data['payment_method'] ?? $productRequest->payment_method,
                'payment_status' => $productRequest->payment_status ?? 'unpaid',
                'shipping_address' => $data['shipping_address'] ?? $productRequest->shipping_address,
                'shipping_name' => $data['shipping_name'] ?? $productRequest->shipping_name ?? $productRequest->user->name,
                'shipping_phone' => $data['shipping_phone'] ?? $productRequest->shipping_phone ?? $productRequest->user->phone,
                'notes' => $data['notes'] ?? 'Order from Request #' . $productRequest->request_number,
                'min_payment_amount' => $productRequest->min_payment_amount,
            ]);

            // Create Order Item
            OrderItem::create([
                'order_id' => $order->id,
                'request_id' => $productRequest->id,
                'product_name' => $productRequest->product_name ?? 'Product Request #' . $productRequest->request_number,
                'price' => $productRequest->price,
                'quantity' => $productRequest->quantity,
                'subtotal' => $productRequest->total_amount_bdt,
                'image' => $productRequest->admin_image_url ?? $this->getFallbackImage($productRequest->url),
                'variant_data' => [
                    'request_url' => $productRequest->url,
                    'request_number' => $productRequest->request_number,
                ],
            ]);

            // Mark request as completed
            $productRequest->update(['status' => 'completed']);

            // Timeline
            $productRequest->timeline()->create([
                'status' => 'completed',
                'note' => 'Converted to Order #' . $order->order_number,
                'created_by' => Auth::id() ?? $userId,
            ]);

            // Initial status history
            $order->updateStatus($statusId, 'Order created from request', Auth::id() ?? $userId);

            return $order;
        });
    }

    /**
     * Helper to determine valid image URL.
     */
    private function getFallbackImage($url)
    {
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
            return $url;
        }
        return config('app.url') . '/assets/placeholder.webp';
    }
}
