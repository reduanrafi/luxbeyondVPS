<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\PaymentMethod;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    // ========== General Settings ==========
    
    public function getSettings(Request $request)
    {
        $group = $request->get('group', 'general');
        $settings = Setting::where('group', $group)->orderBy('sort_order')->get();
        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($validated['settings'] as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                ['value' => $settingData['value'] ?? '']
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    public function updateSetting(Request $request, $key)
    {
        $validated = $request->validate([
            'value' => 'nullable',
        ]);

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $validated['value'] ?? '']
        );

        return response()->json(['message' => 'Setting updated successfully']);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
        ]);

        $setting = Setting::where('key', 'site_logo')->first();

        if ($setting && $setting->value) {
            if (Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }
        }

        $path = $request->file('logo')->store('settings', 'public');

        Setting::updateOrCreate(
            ['key' => 'site_logo'],
            [
                'value' => $path,
                'group' => 'general',
                'type' => 'image'
            ]
        );

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'url' => Storage::disk('public')->url($path)
        ]);
    }

    // ========== Payment Methods ==========

    public function getPaymentMethods()
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();
        return response()->json($methods);
    }

    public function storePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:bkash,nagad,rocket,bank_transfer,cash_on_delivery,stripe,paypal,other',
            'sub_type' => 'nullable|string|in:api,manual',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'account_number' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'routing_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'sort_order' => 'integer',
            'fee' => 'nullable|numeric|min:0',
            'fee_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $method = PaymentMethod::create($validated);
        return response()->json($method, 201);
    }

    public function updatePaymentMethod(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:bkash,nagad,rocket,bank_transfer,cash_on_delivery,stripe,paypal,other',
            'sub_type' => 'nullable|string|in:api,manual',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'account_number' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'routing_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'sort_order' => 'integer',
            'fee' => 'nullable|numeric|min:0',
            'fee_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $method->update($validated);
        return response()->json($method);
    }

    public function deletePaymentMethod($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete();
        return response()->json(['message' => 'Payment method deleted successfully']);
    }

    // ========== Notification Settings ==========

    public function getNotificationSettings()
    {
        $settings = NotificationSetting::orderBy('sort_order')->get();
        return response()->json($settings);
    }

    public function storeNotificationSetting(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|string|max:255',
            'channel' => 'required|string|in:email,sms,push,in_app,webhook',
            'recipient_type' => 'required|string|in:customer,admin,both',
            'enabled' => 'boolean',
            'template_name' => 'nullable|string|max:255',
            'subject' => 'nullable|string',
            'message_template' => 'nullable|string',
            'variables' => 'nullable|array',
            'conditions' => 'nullable|array',
            'recipients' => 'nullable|array',
            'delay_minutes' => 'integer|min:0',
            'priority' => 'string|in:low,normal,high,urgent',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $setting = NotificationSetting::create($validated);
        return response()->json($setting, 201);
    }

    public function updateNotificationSetting(Request $request, $id)
    {
        $setting = NotificationSetting::findOrFail($id);

        $validated = $request->validate([
            'event_type' => 'sometimes|string|max:255',
            'channel' => 'sometimes|string|in:email,sms,push,in_app,webhook',
            'recipient_type' => 'sometimes|string|in:customer,admin,both',
            'enabled' => 'boolean',
            'template_name' => 'nullable|string|max:255',
            'subject' => 'nullable|string',
            'message_template' => 'nullable|string',
            'variables' => 'nullable|array',
            'conditions' => 'nullable|array',
            'recipients' => 'nullable|array',
            'delay_minutes' => 'integer|min:0',
            'priority' => 'string|in:low,normal,high,urgent',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $setting->update($validated);
        return response()->json($setting);
    }

    public function deleteNotificationSetting($id)
    {
        $setting = NotificationSetting::findOrFail($id);
        $setting->delete();
        return response()->json(['message' => 'Notification setting deleted successfully']);
    }
}
