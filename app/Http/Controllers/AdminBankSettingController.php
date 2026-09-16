<?php

namespace App\Http\Controllers;

use App\Models\AdminBankSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminBankSettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/BankSettings/Index', [
            'setting' => AdminBankSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_code' => ['required', 'string', 'max:30'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'qris_image' => ['nullable', 'image', 'max:5120'],
            'midtrans_is_active' => ['nullable', 'boolean'],
            'midtrans_is_production' => ['nullable', 'boolean'],
            'midtrans_server_key' => ['nullable', 'string', 'max:255'],
            'midtrans_client_key' => ['nullable', 'string', 'max:255'],
            'midtrans_merchant_id' => ['nullable', 'string', 'max:100'],
        ]);

        $setting = AdminBankSetting::current();

        $qrisPath = $setting->qris_image_path;
        if ($request->hasFile('qris_image')) {
            $qrisPath = $request->file('qris_image')->store('bank_settings', 'public');
        }

        $setting->update([
            'bank_name' => $validated['bank_name'],
            'bank_code' => $validated['bank_code'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'instructions' => $validated['instructions'],
            'qris_image_path' => $qrisPath,
            'midtrans_is_active' => $request->boolean('midtrans_is_active', true),
            'midtrans_is_production' => $request->boolean('midtrans_is_production', false),
            'midtrans_server_key' => $validated['midtrans_server_key'] ?? $setting->midtrans_server_key,
            'midtrans_client_key' => $validated['midtrans_client_key'] ?? $setting->midtrans_client_key,
            'midtrans_merchant_id' => $validated['midtrans_merchant_id'] ?? $setting->midtrans_merchant_id,
        ]);

        return back()->with('status', 'Konfigurasi rekening dan Midtrans Payment Gateway berhasil disimpan.');
    }
}
