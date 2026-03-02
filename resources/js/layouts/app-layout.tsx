import { Toaster } from 'sonner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { AppLayoutProps } from '@/types';

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
        <div className="p-4 pb-12">
            {children}
        </div>
        <footer className="border-t py-4 text-sm text-center">
            Copyright &copy; {new Date().getFullYear()}. All rights reserved
        </footer>
        <Toaster />
    </AppLayoutTemplate>
);
