export type * from './auth';
export type * from './navigation';
export type * from './ui';

import type { Auth } from './auth';

export type Branding = {
    name: string;
    logo: string | null;
};

export type SharedData = {
    name: string;
    branding: Branding;
    auth: Auth;
    sso: {
        newzakica: boolean;
    };
    sidebarOpen: boolean;
    [key: string]: unknown;
};
