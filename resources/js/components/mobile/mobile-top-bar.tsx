import { router } from '@inertiajs/react';
import { ChevronLeft, Menu } from 'lucide-react';
import type { BreadcrumbItem } from '@/types';
import { useMobileNav } from './mobile-nav-context';

/** Header ringkas untuk halaman selain beranda di layar HP. */
export function MobileTopBar({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItem[];
}) {
    const { setOpenSheet } = useMobileNav();

    const current = breadcrumbs[breadcrumbs.length - 1];
    const parent = breadcrumbs[breadcrumbs.length - 2];

    const goBack = () => {
        if (window.history.length > 1) {
            window.history.back();

            return;
        }

        router.visit('/dashboard');
    };

    return (
        <header className="sticky top-0 z-30 border-b border-border/60 bg-background/85 pt-[env(safe-area-inset-top)] backdrop-blur-xl md:hidden">
            <div className="flex h-14 items-center gap-1 px-2">
                <button
                    type="button"
                    onClick={goBack}
                    aria-label="Kembali"
                    className="flex size-10 shrink-0 items-center justify-center rounded-full active:bg-muted"
                >
                    <ChevronLeft className="size-6" />
                </button>

                <div className="min-w-0 flex-1">
                    {parent && (
                        <p className="truncate text-[11px] leading-tight text-muted-foreground">
                            {parent.title}
                        </p>
                    )}
                    <h1 className="truncate text-base leading-tight font-semibold">
                        {current?.title ?? 'Zakiah'}
                    </h1>
                </div>

                <button
                    type="button"
                    onClick={() => setOpenSheet('menu')}
                    aria-label="Buka menu"
                    className="flex size-10 shrink-0 items-center justify-center rounded-xl active:bg-muted"
                >
                    <Menu className="size-5" />
                </button>
            </div>
        </header>
    );
}
