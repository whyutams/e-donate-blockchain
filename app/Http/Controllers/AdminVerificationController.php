<?php

namespace App\Http\Controllers;

use App\Models\VerificationProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminVerificationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $profiles = VerificationProfile::query()
            ->with('user:id,name,email')
            ->when(in_array($status, ['pending', 'verified', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->through(fn (VerificationProfile $profile) => $this->present($profile));

        return Inertia::render('Admin/Verifications/Index', [
            'profiles' => $profiles,
            'activeStatus' => $status ?: 'all',
        ]);
    }

    public function approve(VerificationProfile $verificationProfile): RedirectResponse
    {
        $verificationProfile->update([
            'status' => 'verified',
            'review_notes' => null,
            'verified_at' => now(),
        ]);

        return back()->with('status', 'Pengajuan verifikasi diterima.');
    }

    public function reject(Request $request, VerificationProfile $verificationProfile): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:1000'],
        ]);

        $verificationProfile->update([
            'status' => 'rejected',
            'review_notes' => $validated['review_notes'],
            'verified_at' => null,
        ]);

        return back()->with('status', 'Pengajuan verifikasi ditolak dan dikembalikan untuk diperbaiki.');
    }

    public function document(VerificationProfile $verificationProfile, string $document)
    {
        $path = match ($document) {
            'selfie' => $verificationProfile->individual_selfie_path,
            'ktp' => $verificationProfile->individual_ktp_path,
            'legalitas' => $verificationProfile->foundation_legal_document_path,
            default => abort(404),
        };

        abort_unless($path && Storage::disk('private')->exists($path), 404);

        return response()->download(Storage::disk('private')->path($path));
    }

    private function present(VerificationProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'entity_type' => $profile->entity_type,
            'legal_name' => $profile->legal_name,
            'status' => $profile->status,
            'review_notes' => $profile->review_notes,
            'verified_at' => $profile->verified_at?->toIso8601String(),
            'submitted_at' => $profile->created_at?->toIso8601String(),
            'individual_nik' => $profile->individual_nik,
            'individual_address' => $profile->individual_address,
            'individual_call_center' => $profile->individual_call_center,
            'foundation_responsible_name' => $profile->foundation_responsible_name,
            'foundation_npwp' => $profile->foundation_npwp,
            'foundation_call_center' => $profile->foundation_call_center,
            'documents' => [
                'selfie' => (bool) $profile->individual_selfie_path,
                'ktp' => (bool) $profile->individual_ktp_path,
                'legalitas' => (bool) $profile->foundation_legal_document_path,
            ],
            'user' => $profile->user,
        ];
    }
}