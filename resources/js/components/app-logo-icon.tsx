import type { ImgHTMLAttributes } from 'react';

interface AppLogoIconProps extends ImgHTMLAttributes<HTMLImageElement> {
    entityName?: string;
}

export default function AppLogoIcon({ entityName, ...props }: AppLogoIconProps) {
    const slug = entityName
        ?.toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-]/g, '');

    const logoSrc = slug
        ? `/assets/images/${slug}.png`
        : '/assets/images/images-logo.png';

    return (
        <img
            {...props}
            src={logoSrc}
            alt={entityName ? `Logo ${entityName}` : 'Logo'}
            onError={(e) => {
                (e.currentTarget as HTMLImageElement).src = '/assets/images/zakiah.png';
            }}
        />
    );
}
