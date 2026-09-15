<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only('name', 'email'),
                'is_admin' => $request->user()?->isAdmin() ?? false,
                'is_user' => $request->user()?->isUser() ?? false,
            ],
            'admin_bank' => fn () => \App\Models\AdminBankSetting::current(),
            'midtrans' => fn () => [
                'client_key' => app(\App\Services\MidtransService::class)->getClientKey(),
                'is_production' => app(\App\Services\MidtransService::class)->isProduction(),
                'snap_url' => app(\App\Services\MidtransService::class)->getSnapJsUrl(),
                'is_configured' => app(\App\Services\MidtransService::class)->isConfigured(),
                'is_active' => \App\Models\AdminBankSetting::current()->isMidtransActive(),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
