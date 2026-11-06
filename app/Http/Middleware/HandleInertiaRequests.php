<?php

namespace App\Http\Middleware;

use App\Services\EntityBrandingService;
use App\Services\MenuAccessService;
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
        $user = $request->user();
        $entityName = $user?->entity?->name;
        $branding = app(EntityBrandingService::class);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'branding' => [
                'name' => $branding->displayName($entityName),
                'logo' => $branding->logoUrl($entityName),
            ],
            'auth' => [
                'user' => $user ? $user->load('entity', 'employee') : $user,
            ],
            'sso' => [
                'newzakica' => filled(config('services.newzakica.url')) && $user?->employee !== null,
            ],
            // Sidebar dari tabel menu; null bila tabelnya belum ada (pakai menu statis).
            'navigation' => fn () => $user ? app(MenuAccessService::class)->navigation() : null,
            'access' => fn () => $user ? [
                'enforced' => app(MenuAccessService::class)->enforced(),
                'permissions' => app(MenuAccessService::class)->permissions(),
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
