import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { BoutiqueIllustration } from '@/components/auth/boutique-illustration';
import { cn } from '@/lib/utils';

/**
 * Halaman mandiri tanpa menu aplikasi: ilustrasi vektor dari halaman login dan kartu kaca.
 * Warnanya sengaja tetap terang di mode gelap (kartu kaca di atas foto), tapi mengikuti
 * tema entity lewat variabel --g-* (.glass-theme di app.css).
 */
export const glassText = 'text-[var(--g-ink)]';
export const glassMuted = 'text-[var(--g-muted)]';

export const glassButton =
    'inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold transition focus-visible:ring-2 focus-visible:ring-[var(--g-primary)] focus-visible:outline-none disabled:opacity-50';

export const glassPrimary = cn(
    glassButton,
    'bg-[var(--g-primary)] text-white shadow-md shadow-black/10 hover:bg-[var(--g-primary-hover)]',
);

export const glassSecondary = cn(
    glassButton,
    'border border-white/80 bg-white/70 text-[var(--g-ink)] hover:bg-white/90',
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
                'glass-theme relative min-h-dvh overflow-x-hidden bg-[var(--g-bg)] print:bg-white',
                glassText,
            )}
        >
            {/* Ilustrasi vektor butik yang sama dengan halaman login. */}
            <div
                aria-hidden="true"
                className="fixed inset-0 overflow-hidden bg-[#243146] print:hidden"
            >
                <BoutiqueIllustration />
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,var(--g-vignette)_100%)]" />
            </div>

            <main className="relative z-10 flex min-h-dvh items-center justify-center px-4 pt-[calc(env(safe-area-inset-top)+16px)] pb-[calc(env(safe-area-inset-bottom)+16px)] print:block print:p-0">
                <div
                    className={cn(
                        'relative w-full max-w-lg overflow-hidden rounded-3xl border border-white/75 bg-[var(--g-paper)]/75 p-6 shadow-[0_24px_48px_-18px_rgba(31,42,60,0.4),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-md backdrop-saturate-150 sm:p-8 print:border-0 print:bg-white print:shadow-none print:backdrop-blur-none',
                        className,
                    )}
                >
                    <div
                        aria-hidden="true"
                        className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[var(--g-primary)] via-[var(--g-accent)] to-[var(--g-primary-2)] print:hidden"
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
                <h1 className="text-2xl font-extrabold text-[var(--g-primary)]">
                    {title}
                </h1>
            )}
        </div>
    );
}
