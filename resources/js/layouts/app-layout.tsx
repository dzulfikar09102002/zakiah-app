import { Toaster } from 'sonner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { AppLayoutProps } from '@/types';

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
        <div className="p-4">
            {children}
        </div>
        <Toaster />
    </AppLayoutTemplate>
);
