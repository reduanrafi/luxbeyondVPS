<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice #{{ $order->tracking_number ?? $order->id }}</title>
    <style type="text/css">
    /* DejaVu Sans is built into DomPDF and supports the ৳ symbol */
    body { 
        font-family: 'DejaVu Sans', sans-serif; 
        font-size: 11px; 
        color: #333; 
        line-height: 1.4; 
        margin: 0; 
        padding: 0; 
    }
    .invoice-wrapper { width: 100%; margin: 0 auto; background: #fff; }
    .header-table { width: 100%; margin-bottom: 20px;}
    .logo { width: 120px; }
    .invoice-label { font-size: 24px; color: #000; font-weight: bold; text-align: right;}
    .meta-bar { width: 100%; padding: 10px 0; margin-bottom: 30px; border-top: 1px solid #eee; border-bottom: 1px solid #eee; }
    .meta-bar td { font-size: 10px; text-transform: uppercase; color: #444; }
    .address-section { width: 100%; margin-bottom: 30px; }
    .address-box { vertical-align: top; width: 48%; }
    .address-title { font-weight: bold; border-bottom: 1px solid #000; margin-bottom: 8px; padding-bottom: 3px; }
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    .items-table th { background-color: #f8f8f8; color: #111; font-size: 10px; padding: 10px; text-align: left; border-bottom: 2px solid #eee; }
    .items-table td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: top; font-size: 10px; }
    .totals-table { width: 250px; float: right; border-collapse: collapse; }
    .totals-table td { padding: 8px; border-bottom: 1px solid #eee; font-size: 11px; }
    .grand-total { background-color: #000; color: #fff; font-weight: bold; }
    .footer { margin-top: 50px; font-size: 9px; color: #888; text-align: center; clear: both; }
    .variant-text { color: #666; font-size: 9px; margin-top: 4px; display: block; font-style: italic; }
    
    .taka { font-family: 'DejaVu Sans'; }
</style>
</head>
<body>
    @php
        $settings = $settings->options ?? [];
        $currency = '&#2547;'; // Based on your previous context
    @endphp

    <div class="invoice-wrapper">
        <table class="header-table">
            <tr>
                <td>
                    <img src="{{ $logo }}" class="logo">
                    <div style="font-size: 11px; margin-top: 5px;">{{ env('FRONTEND_URL')}}</div>
                </td>
                <td class="invoice-label">INVOICE</td>
            </tr>
        </table>

        <table class="meta-bar">
            <tr>
                <td><strong>ORDER ID:</strong> #{{ $order->order_number ?? $order->id }}</td>
                <td><strong>DATE:</strong> {{ $order->created_at->format('d/m/Y') }}</td>
                <td><strong>PAYMENT:</strong> {{ strtoupper($order->payment_method ?? 'N/A') }}</td>
                <td style="text-align: right;"><strong>STATUS:</strong> {{ strtoupper($order->payment_status) }}</td>
            </tr>
        </table>

        <table class="address-section">
            <tr>
                <td class="address-box">
                    <div class="address-title">Bill From</div>
                    <strong>Luxbeyond</strong><br>
                    Flat 4B,House 33,Road 03<br>
                    Sector 09,Uttara, Dhaka<br>
                    Bangladesh, 1230
                </td>
                <td width="4%"></td>
                <td class="address-box">
                    <div class="address-title">Ship To</div>
                    <strong>{{ $order->shipping_name }}</strong><br>
                    Phone: {{ $order->shipping_phone }}<br>
                    Email: {{ $order->shipping_email }}<br>
                    Address: {{ $order->shipping_address }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="50%">Product Details</th>
                    <th width="15%">Price</th>
                    <th width="10%">Qty</th>
                    <th width="20%" style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->variant_data)
                            <span class="variant-text">
                                @php
                                    $vData = is_string($item->variant_data) ? json_decode($item->variant_data, true) : $item->variant_data;
                                    $attrs = $vData['attributes'] ?? [];
                                @endphp
                                @foreach($attrs as $key => $val)
                                    {{ $key }}: {{ $val }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </span>
                        @endif
                    </td>
                    <td> {!! $currency !!}{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td style="text-align: right;">{!! $currency !!}{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td style="text-align: right;">{!! $currency !!}{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->discount > 0)
            <tr>
                <td>Discount</td>
                <td style="text-align: right;">-{!! $currency !!}{{ number_format($order->discount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Shipping</td>
                <td style="text-align: right;">{!! $currency !!}{{ number_format($order->delivery_fee ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td style="text-align: right;">{!! $currency !!}{{ number_format($order->tax ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td style="text-align: right;">{!! $currency !!}{{ number_format($order->discount ?? 0, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Total Payable</td>
                <td style="text-align: right;">{!! $currency !!}{{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>