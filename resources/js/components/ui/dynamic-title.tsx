import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export default function DynamicTitle() {
    const { auth } = usePage().props as any;

    useEffect(() => {
    const rawEntityName =
        auth?.user?.entity?.name ?? 'Zakiah & Secaca';
console.log(auth?.user?.entity?.name);
    const entityName =
        rawEntityName === rawEntityName.toUpperCase() ||
        rawEntityName === rawEntityName.toLowerCase()
            ? rawEntityName.charAt(0).toUpperCase() +
              rawEntityName.slice(1).toLowerCase()
            : rawEntityName;

    // ambil title halaman dari inertia
    const pageTitle =
        document.title.split(' - ')[0];

    document.title =
        `${pageTitle} | ${entityName} Backoffice`;

    const slug = rawEntityName
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-]/g, '');

    const favicon =
        document.querySelector(
            'link[rel="icon"]'
        ) as HTMLLinkElement | null;

    if (favicon) {
        favicon.href = `/assets/images/${slug}.png`;
    }
}, [auth?.user?.entity?.name]);
    return null;
}