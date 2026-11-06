import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';
import AppLogoIcon from './app-logo-icon';

/** Logo entity + nama usaha. Saat sidebar diringkas hanya logonya yang terlihat. */
export default function AppLogo() {
    const { auth, branding } = usePage<
        SharedData & { branding?: { name?: string } }
    >().props;

    const entityName = auth?.user?.entity?.name;
    const displayName = branding?.name ?? entityName ?? 'Zakiah';

    return (
        <>
            <div className="flex aspect-square size-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white shadow-xs ring-1 ring-sidebar-border">
                <AppLogoIcon className="size-6" entityName={entityName} />
            </div>
            {/* Font brand sama dengan halaman login (Playfair Display + Lato). */}
            <div className="grid flex-1 gap-0.5 text-left leading-none">
                <span className="truncate font-serif text-base font-semibold tracking-[0.14em] uppercase">
                    {displayName.toUpperCase()}
                </span>
                <span className="truncate font-lato text-[10px] tracking-[0.24em] text-sidebar-muted uppercase">
                    Backoffice
                </span>
            </div>
        </>
    );
}
