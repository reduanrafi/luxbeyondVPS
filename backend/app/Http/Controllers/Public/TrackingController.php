<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductRequest;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function track(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $trackingNumber = trim($request->tracking_number);

        // 1. Try finding in Shop Orders
        $order = Order::where('order_number', $trackingNumber)
            ->with(['items.product', 'statusHistories', 'status'])
            ->first();

        if ($order) {
            return response()->json([
                'type' => 'order',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'date' => $order->created_at->format('d M, Y h:i A'),
                    'status' => $order->status->name ?? 'Unknown',
                    'total_amount' => $order->total,
                    'is_paid' => $order->is_fully_paid,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->product->name ?? 'Unknown Product',
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'image' => $item->product ? $item->product->image_url : null,
                        ];
                    }),
                    'timeline' => $order->statusHistories->map(function ($history) {
                        return [
                            'status' => $history->status,
                            'date' => $history->created_at->format('d M, Y h:i A'),
                            'note' => $history->note,
                        ];
                    }),
                    // If no history, imply created state
                    'current_status_step' => $this->mapStatusToStep($order->status->name ?? 'Pending')
                ]
            ]);
        }

        // 2. Try finding in Product Requests
        $productRequest = ProductRequest::where('request_number', $trackingNumber)
            ->with(['orderStatus', 'timeline'])
            ->first();

        if ($productRequest) {
            return response()->json([
                'type' => 'request',
                'data' => [
                    'id' => $productRequest->id,
                    'request_number' => $productRequest->request_number,
                    'date' => $productRequest->created_at->format('d M, Y h:i A'),
                    'status' => $productRequest->orderStatus->name ?? 'Pending',
                    'total_amount' => $productRequest->total_amount_bdt,
                    'url' => $productRequest->url,
                    'items' => [
                        [
                            'name' => 'Requested Item', // Requests usually have one main URL item
                            'quantity' => $productRequest->quantity,
                            'price' => $productRequest->price,
                            'image' => $productRequest->admin_image_url,
                        ]
                    ],
                    'timeline' => $productRequest->timeline->map(function ($history) {
                        return [
                            'status' => $history->status,
                            'date' => $history->created_at->format('d M, Y h:i A'),
                            'note' => $history->note,
                        ];
                    }),
                    'current_status_step' => $this->mapStatusToStep($productRequest->orderStatus->name ?? 'Pending')
                ]
            ]);
        }

        return response()->json(['message' => 'Tracking number not found.'], 404);
    }

    private function mapStatusToStep($status)
    {
        $status = strtolower($status);
        if (in_array($status, ['pending', 'awaiting payment'])) return 1;
        if (in_array($status, ['processing', 'approved', 'purchased'])) return 2;
        if (in_array($status, ['shipped', 'arrived in country', 'ready to deliver'])) return 3;
        if (in_array($status, ['delivered', 'completed'])) return 4;
        if (in_array($status, ['cancelled', 'refunded'])) return 0; // 0 for failed/cancelled
        return 1;
    }
}
