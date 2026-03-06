<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ihasan\Bkash\Facades\Bkash;
use Ihasan\Bkash\Exceptions\TokenGenerationException;
use Ihasan\Bkash\Exceptions\PaymentCreationException;
use Ihasan\Bkash\Exceptions\PaymentExecutionException;
use Ihasan\Bkash\Exceptions\PaymentQueryException;

class PaymentController extends Controller
{
    /**
     * Configure bKash package with credentials from database or .env fallback
     */
    private function configureBkashFromPaymentMethod()
    {
        $paymentMethod = PaymentMethod::where('type', 'bkash')
            ->where('is_active', true)
            ->where('is_online', true)
            ->first();

        // If payment method exists and has config, use it
        if ($paymentMethod && $paymentMethod->config) {
            $config = $paymentMethod->config;
            
            config([
                'bkash.sandbox' => $config['is_sandbox'] ?? env('BKASH_SANDBOX', true),
                'bkash.credentials.app_key' => $config['app_key'] ?? env('BKASH_APP_KEY', ''),
                'bkash.credentials.app_secret' => $config['app_secret'] ?? env('BKASH_APP_SECRET', ''),
                'bkash.credentials.username' => $config['username'] ?? env('BKASH_USERNAME', ''),
                'bkash.credentials.password' => $config['password'] ?? env('BKASH_PASSWORD', ''),
            ]);
        } else {
            // Fallback to .env credentials
            config([
                'bkash.sandbox' => env('BKASH_SANDBOX', true),
                'bkash.credentials.app_key' => env('BKASH_APP_KEY', ''),
                'bkash.credentials.app_secret' => env('BKASH_APP_SECRET', ''),
                'bkash.credentials.username' => env('BKASH_USERNAME', ''),
                'bkash.credentials.password' => env('BKASH_PASSWORD', ''),
            ]);
        }

        return $paymentMethod;
    }

    /**
     * Initiate bKash payment using the package
     */
    public function initiateBkashPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        
        if ($order->user_id != auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized access to order'
            ], 403);
        }

        try {
            // $this->configureBkashFromPaymentMethod();

            $backendUrl = rtrim(config('app.url'), '/');
            $callbackUrl = $backendUrl . '/api/payments/bkash/callback';
            
            $paymentData = [
                'amount' => number_format((float) $validated['amount'], 2, '.', ''),
                'payer_reference' => 'order_' . $order->order_number,
                'callback_url' => $callbackUrl,
                'merchant_invoice_number' => $order->order_number,
            ];
            
            Log::info('bKash Payment Initiated', [
                'order_id' => $order->id,
                'callback_url' => $callbackUrl,
            ]);

            $response = Bkash::createPayment($paymentData);

            $order->update([
                'payment_reference' => $response['paymentID'] ?? null,
            ]);

            return response()->json([
                'paymentID' => $response['paymentID'] ?? null,
                'bkashURL' => $response['bkashURL'] ?? null,
                'successCallback' => $response['successCallback'] ?? null,
            ]);

        } catch (PaymentCreationException $e) {
            Log::error('bKash Payment Creation Error', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'message' => 'Failed to create bKash payment',
                'error' => $e->getMessage()
            ], 500);
        } catch (TokenGenerationException $e) {
            Log::error('bKash Token Generation Error', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'message' => 'Failed to initialize bKash payment',
                'error' => 'Token generation failed'
            ], 500);
        } catch (\Exception $e) {
            Log::error('bKash Payment Initiation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * bKash payment callback using the package
     */
    public function bkashCallback(Request $request)
    {
        $paymentID = $request->input('paymentID');
        $status = $request->input('status');
        $requestId = $request->input('request_id'); // For Product Requests

        $frontendUrl = config('app.frontend_url', config('app.url'));

        if ($status != 'success') {
            return redirect($frontendUrl . ($requestId ? '/dashboard/requests' : '/checkout') . '?payment=failed');
        }

        if (!$paymentID) {
            return redirect($frontendUrl . ($requestId ? '/dashboard/requests' : '/checkout') . '?payment=error');
        }

        // Logic for Product Request
        if ($requestId) {
            $productRequest = \App\Models\ProductRequest::find($requestId);
            if (!$productRequest) {
                Log::warning('bKash Callback: Product Request not found', ['request_id' => $requestId]);
                return redirect($frontendUrl . '/dashboard/requests?payment=error');
            }

            try {
                $this->configureBkashFromPaymentMethod();
                $response = Bkash::executePayment($paymentID);

                if (isset($response['transactionStatus']) && $response['transactionStatus'] === 'Completed') {
                    $productRequest->update([
                        'payment_status' => 'paid',
                        'bkash_trx_id' => $response['trxID'] ?? null,
                        'paid_at' => now(),
                        'payment_method' => 'bkash'
                    ]);

                    return redirect($frontendUrl . '/dashboard/requests/' . $productRequest->id . '?payment=success');
                } else {
                    Log::warning('bKash Payment Not Completed (Product Reqeust)', [
                        'request_id' => $productRequest->id,
                        'status' => $response['transactionStatus'] ?? 'unknown',
                    ]);
                    return redirect($frontendUrl . '/dashboard/requests/' . $productRequest->id . '?payment=failed');
                }
            } catch (\Exception $e) {
                Log::error('bKash Callback Error (Product Request)', [
                    'error' => $e->getMessage(),
                    'request_id' => $requestId
                ]);
                return redirect($frontendUrl . '/dashboard/requests/' . $requestId . '?payment=error');
            }
        }

        // Existing Logic for Orders
        // Find order by payment reference
        $order = Order::where('payment_reference', $paymentID)->first();

        if (!$order) {
            Log::warning('bKash Callback: Order not found', ['paymentID' => $paymentID]);
            return redirect($frontendUrl . '/checkout?payment=error');
        }

        try {
            // Configure bKash with credentials from database
            $this->configureBkashFromPaymentMethod();

            // Execute payment to verify and complete
            $response = Bkash::executePayment($paymentID);

            // Check if payment was successful
            if (isset($response['transactionStatus']) && $response['transactionStatus'] === 'Completed') {
                // Create payment record
                $paymentAmount = $response['amount'] ?? 0;
                OrderPayment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'bkash',
                    'amount' => $paymentAmount,
                    'payment_reference' => $response['trxID'] ?? null,
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);

                // Update order status
                $order->update([
                    'payment_status' => 'paid',
                    'bkash_trx_id' => $response['trxID'] ?? null,
                    'paid_at' => now(),
                ]);

                Log::info('bKash Payment Successful', [
                    'order_id' => $order->id,
                    'paymentID' => $paymentID,
                    'trxID' => $response['trxID'] ?? null,
                    'amount' => $paymentAmount,
                ]);

                return redirect($frontendUrl . '/checkout?payment=success&order_id=' . $order->id);
            } else {
                Log::warning('bKash Payment Not Completed', [
                    'order_id' => $order->id,
                    'paymentID' => $paymentID,
                    'status' => $response['transactionStatus'] ?? 'unknown',
                ]);

                return redirect($frontendUrl . '/checkout?payment=failed');
            }

        } catch (PaymentExecutionException $e) {
            Log::error('bKash Payment Execution Error', [
                'error' => $e->getMessage(),
                'paymentID' => $paymentID,
                'order_id' => $order->id ?? null,
            ]);

            return redirect($frontendUrl . '/checkout?payment=error');
        } catch (\Exception $e) {
            Log::error('bKash Callback Error', [
                'error' => $e->getMessage(),
                'paymentID' => $paymentID,
                'order_id' => $order->id ?? null,
            ]);

            return redirect($frontendUrl . '/checkout?payment=error');
        }
    }

    /**
     * Upload payment slip for bank transfer
     */
    public function uploadPaymentSlip(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_slip' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:5120', // 5MB max
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Check if order payment method is bank transfer or not set
        if ($order->payment_method && $order->payment_method != 'bank_transfer') {
            return response()->json([
                'message' => 'Payment slip can only be uploaded for bank transfer orders'
            ], 400);
        }

        // Set payment method to bank_transfer if not set
        if (!$order->payment_method) {
            $order->payment_method = 'bank_transfer';
            $order->save();
        }

        // Upload payment slip
        if ($request->hasFile('payment_slip')) {
            $file = $request->file('payment_slip');
            $filename = 'payment_slips/' . $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public', $filename);
            
            $order->update([
                'payment_slip' => $filename,
                'payment_reference' => $validated['transaction_id'] ?? null,
                'payment_status' => 'pending', // Admin will verify
            ]);

            return response()->json([
                'message' => 'Payment slip uploaded successfully',
                'order' => $order->fresh(),
            ]);
        }

        return response()->json([
            'message' => 'No payment slip file provided'
        ], 400);
    }

    /**
     * Verify bKash payment manually (for admin) using the package
     */
    public function verifyBkashPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_id' => 'required|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        try {
            // Configure bKash with credentials from database
            $this->configureBkashFromPaymentMethod();

            // Query payment status
            $response = Bkash::queryPayment($validated['payment_id']);

            $status = $response['transactionStatus'] ?? 'Unknown';

            if ($status === 'Completed') {
                $order->update([
                    'bkash_trx_id' => $response['trxID'] ?? null,
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'order' => $order->fresh(),
                    'payment_data' => $response,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status: ' . $status,
                    'order' => $order->fresh(),
                    'payment_data' => $response,
                ], 400);
            }

        } catch (PaymentQueryException $e) {
            Log::error('bKash Payment Query Error', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'payment_id' => $validated['payment_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('bKash Verification Error', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}

