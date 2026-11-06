import { Link, router, usePage } from '@inertiajs/react';
import {
    LogOut,
    Monitor,
    Moon,
    Palette,
    Settings,
    SquareArrowOutUpRight,
    Sun,
} from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import sso from '@/routes/sso';
import type { SharedData, User } from '@/types';

type Props = {
    user: User;
};

const themes: { value: Appearance; label: string; icon: typeof Sun }[] = [
    { value: 'light', label: 'Terang', icon: Sun },
    { value: 'dark', label: 'Gelap', icon: Moon },
    { value: 'system', label: 'Ikuti perangkat', icon: Monitor },
];

/** Isi menu akun: profil, tema, POS, dan keluar. */
export function UserMenuContent({ user }: Props) {
    const cleanup = useMobileNavigation();
    const { appearance, updateAppearance } = useAppearance();
    const { sso: ssoLinks } = usePage<SharedData>().props;

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="w-full cursor-pointer"
                        href={edit()}
                        prefetch
                        onClick={cleanup}
                    >
                        <Settings />
                        Profil & Pengaturan
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSub>
                    <DropdownMenuSubTrigger className="gap-2">
                        <Palette className="size-4 text-muted-foreground" />
                        Tampilan
                    </DropdownMenuSubTrigger>
                    <DropdownMenuSubContent>
                        <DropdownMenuRadioGroup
                            value={appearance}
                            onValueChange={(value) =>
                                updateAppearance(value as Appearance)
                            }
                        >
                            {themes.map(({ value, label, icon: Icon }) => (
                                <DropdownMenuRadioItem
                                    key={value}
                                    value={value}
                                    className="gap-2"
                                >
                                    <Icon className="size-4 text-muted-foreground" />
                                    {label}
                                </DropdownMenuRadioItem>
                            ))}
                        </DropdownMenuRadioGroup>
                    </DropdownMenuSubContent>
                </DropdownMenuSub>
                {ssoLinks?.newzakica && (
                    <DropdownMenuItem asChild>
                        <a
                            href={sso.newzakica().url}
                            target="_blank"
                            rel="noopener"
                            className="w-full cursor-pointer"
                        >
                            <SquareArrowOutUpRight />
                            POS & Backoffice CV
                        </a>
                    </DropdownMenuItem>
                )}
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                asChild
                className="text-destructive focus:bg-destructive/10 focus:text-destructive [&_svg]:!text-destructive"
            >
                <Link
                    className="w-full cursor-pointer"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut />
                    Keluar
                </Link>
            </DropdownMenuItem>
        </>
    );
}
