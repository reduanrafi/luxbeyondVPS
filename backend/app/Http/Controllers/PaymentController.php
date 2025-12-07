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
     * Configure bKash package with credentials from database
     */
    private function configureBkashFromPaymentMethod()
    {
        $paymentMethod = PaymentMethod::where('type', 'bkash')
            ->where('is_active', true)
            ->where('is_online', true)
            ->first();

        if (!$paymentMethod || !$paymentMethod->config) {
            throw new \Exception('bKash payment method is not configured');
        }

        $config = $paymentMethod->config;
        
        // Temporarily update config for this request
        config([
            'bkash.sandbox' => $config['is_sandbox'] ?? true,
            'bkash.credentials.app_key' => $config['app_key'] ?? '',
            'bkash.credentials.app_secret' => $config['app_secret'] ?? '',
            'bkash.credentials.username' => $config['username'] ?? '',
            'bkash.credentials.password' => $config['password'] ?? '',
        ]);

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
        
        // Verify order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized access to order'
            ], 403);
        }

        try {
            // Configure bKash with credentials from database
            $this->configureBkashFromPaymentMethod();

            // Prepare payment data
            // Callback URL must point to the backend API server (APP_URL), not frontend
            // APP_URL is the backend server URL (e.g., http://127.0.0.1:8000)
            $backendUrl = rtrim(config('app.url'), '/'); // APP_URL is the backend URL
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

            // Create payment using the package
            $response = Bkash::createPayment($paymentData);

            // Store payment reference in order
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

        $frontendUrl = config('app.frontend_url', config('app.url'));

        if ($status !== 'success') {
            return redirect($frontendUrl . '/checkout?payment=failed');
        }

        if (!$paymentID) {
            return redirect($frontendUrl . '/checkout?payment=error');
        }

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
            'payment_slip' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Check if order payment method is bank transfer
        if ($order->payment_method !== 'bank_transfer') {
            return response()->json([
                'message' => 'Payment slip can only be uploaded for bank transfer orders'
            ], 400);
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

