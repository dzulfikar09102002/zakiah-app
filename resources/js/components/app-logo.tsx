import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';
import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    const { auth } = usePage<SharedData>().props;

    const entityName = auth?.user?.entity?.name;

    return (
        <>
            <div className="size-8 flex justify-center items-center rounded-md bg-white border">
                <AppLogoIcon
                    className="size-6"
                    entityName={entityName}
                />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    {entityName?.toUpperCase()} POS
                </span>
            </div>
        </>
    );
}
