import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

/**
 * Halaman mandiri tanpa menu aplikasi: latar toko dari halaman login dan kartu kaca.
 * Warnanya sengaja tetap terang di mode gelap, karena kartu kaca berada di atas foto.
 */
export const glassText = 'text-[#2b1a12]';
export const glassMuted = 'text-[#6b5a52]';

export const glassButton =
    'inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold transition focus-visible:ring-2 focus-visible:ring-[#cc7455] focus-visible:outline-none disabled:opacity-50';

export const glassPrimary = cn(
    glassButton,
    'bg-[#cc7455] text-white hover:bg-[#b96545]',
);

export const glassSecondary = cn(
    glassButton,
    'border border-white/80 bg-white/70 text-[#2b1a12] hover:bg-white/90',
);

export function GlassPage({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'relative min-h-dvh overflow-x-hidden bg-[#f7efe4] print:bg-white',
                glassText,
            )}
        >
            <div
                aria-hidden="true"
                style={{ backgroundImage: "url('/assets/images/store.png')" }}
                className="fixed inset-0 bg-cover bg-top bg-no-repeat print:hidden"
            >
                {/* Vignette yang sama dengan panel kanan halaman login. */}
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(120,60,30,0.45)_100%)]" />
            </div>

            <main className="relative z-10 flex min-h-dvh items-center justify-center px-4 pt-[calc(env(safe-area-inset-top)+16px)] pb-[calc(env(safe-area-inset-bottom)+16px)] print:block print:p-0">
                <div
                    className={cn(
                        'relative w-full max-w-lg overflow-hidden rounded-3xl border border-white/75 bg-[#fffbf5]/70 p-6 shadow-[0_24px_48px_-18px_rgba(120,70,40,0.4),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-md backdrop-saturate-150 sm:p-8 print:border-0 print:bg-white print:shadow-none print:backdrop-blur-none',
                        className,
                    )}
                >
                    <div
                        aria-hidden="true"
                        className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#9b462c] via-[#cc7455] to-[#f0b48a] print:hidden"
                    />
                    {children}
                </div>
            </main>
        </div>
    );
}

/** Logo dan nama usaha. Halaman error tertentu tidak punya data bersama, jadi semuanya opsional. */
export function GlassBrand({
    logo,
    name,
    title,
    align = 'center',
}: {
    logo?: string | null;
    name?: string | null;
    title?: string;
    align?: 'center' | 'start';
}) {
    const { branding } = usePage<{
        branding?: { name?: string; logo?: string | null };
    }>().props;
    const shownLogo = logo ?? branding?.logo;
    const shownName = name ?? branding?.name ?? 'Zakiah Backoffice';

    return (
        <div
            className={cn(
                'flex flex-col gap-2',
                align === 'center' ? 'items-center text-center' : 'items-start',
            )}
        >
            {shownLogo && (
                <img src={shownLogo} alt="" className="h-14 w-auto max-w-40" />
            )}
            <p
                className={cn(
                    'text-xs font-bold tracking-[0.16em] uppercase',
                    glassMuted,
                )}
            >
                {shownName}
            </p>
            {title && (
                <h1 className="text-2xl font-extrabold text-[#9b462c]">
                    {title}
                </h1>
            )}
        </div>
    );
}
