<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationProfileController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'in:individual,foundation'],
            'legal_name' => ['required', 'string', 'max:255'],
            'individual_nik' => ['required_if:entity_type,individual', 'nullable', 'digits:16'],
            'individual_address' => ['required_if:entity_type,individual', 'nullable', 'string', 'max:1000'],
            'individual_call_center' => ['required_if:entity_type,individual', 'nullable', 'string', 'max:30'],
            'individual_selfie' => ['required_if:entity_type,individual', 'nullable', 'image', 'max:5120'],
            'individual_ktp' => ['required_if:entity_type,individual', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'foundation_responsible_name' => ['required_if:entity_type,foundation', 'nullable', 'string', 'max:255'],
            'foundation_npwp' => ['required_if:entity_type,foundation', 'nullable', 'string', 'max:32'],
            'foundation_call_center' => ['required_if:entity_type,foundation', 'nullable', 'string', 'max:30'],
            'foundation_legal_document' => ['required_if:entity_type,foundation', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'encrypted_identity_data' => ['nullable', 'string'],
        ]);

        $request->user()->verificationProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'entity_type' => $validated['entity_type'],
                'legal_name' => $validated['legal_name'],
                'individual_nik' => $validated['individual_nik'] ?? null,
                'individual_address' => $validated['individual_address'] ?? null,
                'individual_call_center' => $validated['individual_call_center'] ?? null,
                'individual_selfie_path' => $request->file('individual_selfie')?->store('verification-documents', 'private'),
                'individual_ktp_path' => $request->file('individual_ktp')?->store('verification-documents', 'private'),
                'foundation_responsible_name' => $validated['foundation_responsible_name'] ?? null,
                'foundation_npwp' => $validated['foundation_npwp'] ?? null,
                'foundation_call_center' => $validated['foundation_call_center'] ?? null,
                'foundation_legal_document_path' => $request->file('foundation_legal_document')?->store('verification-documents', 'private'),
                'encrypted_identity_data' => $validated['encrypted_identity_data'] ?? null,
                'status' => 'pending',
                'review_notes' => null,
                'verified_at' => null,
            ],
        );

        return back()->with('status', 'Pengajuan verifikasi berhasil dikirim.');
    }
}