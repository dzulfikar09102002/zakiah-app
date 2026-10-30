import { Toaster } from 'sonner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { cn } from '@/lib/utils';
import type { AppLayoutProps } from '@/types';

export default ({
    children,
    breadcrumbs,
    mobileHome,
    ...props
}: AppLayoutProps) => (
    <AppLayoutTemplate
        breadcrumbs={breadcrumbs}
        mobileHome={mobileHome}
        {...props}
    >
        {/* Di HP, beri ruang untuk bottom nav (+ safe area iPhone). */}
        <main
            className={cn(
                mobileHome ? 'p-0' : 'p-4',
                'flex-1 pb-[calc(env(safe-area-inset-bottom)+6rem)] md:p-4 md:pb-12',
            )}
        >
            {children}
        </main>

        <footer className="hidden border-t py-4 text-center text-sm md:block">
            Copyright &copy; {new Date().getFullYear()} Zakiah Group. All rights
            reserved
        </footer>

        <Toaster
            mobileOffset={{
                bottom: 'calc(env(safe-area-inset-bottom) + 88px)',
            }}
        />
    </AppLayoutTemplate>
);
