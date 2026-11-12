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
            <div className="flex aspect-square size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md ring-1 shadow-primary/20 ring-primary/15 group-data-[collapsible=icon]:size-8 group-data-[collapsible=icon]:rounded-lg">
                <AppLogoIcon
                    className="size-7 object-contain group-data-[collapsible=icon]:size-6"
                    entityName={entityName}
                />
            </div>
            <div className="grid flex-1 text-left leading-none">
                <span className="truncate text-[15px] font-bold tracking-tight text-foreground">
                    {displayName}
                </span>
                <span className="mt-1 truncate text-[10px] font-semibold tracking-[0.2em] text-primary uppercase">
                    Backoffice
                </span>
            </div>
        </>
    );
}
