import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { toneClasses, type MenuTone } from '@/lib/mobile-nav';
import { cn } from '@/lib/utils';

type Props = {
    title: string;
    icon: LucideIcon;
    tone: MenuTone;
    href?: string;
    active?: boolean;
    onClick?: () => void;
    badge?: ReactNode;
};

/** Tile ikon + label, dipakai di grid beranda dan di sheet menu. */
export function MenuTile({
    title,
    icon: Icon,
    tone,
    href,
    active,
    onClick,
    badge,
}: Props) {
    const content = (
        <>
            <span
                className={cn(
                    'relative flex size-13 items-center justify-center rounded-2xl transition-transform duration-150 group-active:scale-90',
                    toneClasses[tone],
                    active &&
                        'ring-2 ring-primary ring-offset-2 ring-offset-background',
                )}
            >
                <Icon className="size-6" strokeWidth={1.9} />
                {badge}
            </span>
            <span className="line-clamp-2 text-center text-[11.5px] leading-tight font-medium text-foreground/85">
                {title}
            </span>
        </>
    );

    const className =
        'group flex flex-col items-center gap-1.5 rounded-2xl py-1 outline-none focus-visible:ring-2 focus-visible:ring-ring';

    if (href) {
        return (
            <Link href={href} className={className} onClick={onClick}>
                {content}
            </Link>
        );
    }

    return (
        <button type="button" className={className} onClick={onClick}>
            {content}
        </button>
    );
}
