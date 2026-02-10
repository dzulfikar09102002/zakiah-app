import { usePage } from '@inertiajs/react';
import AppLogoIcon from './app-logo-icon';
import { SharedData } from '@/types';

export default function AppLogo() {
    const { auth } = usePage<SharedData>().props;
    return (
        <>
            <AppLogoIcon className="size-8 fill-current text-white dark:text-black" />

            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    {auth.user.entity.name}
                </span>
            </div>
        </>
    );
}
