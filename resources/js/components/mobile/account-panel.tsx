import { Link, router, usePage } from '@inertiajs/react';
import {
    ChevronRight,
    Download,
    LogOut,
    Monitor,
    Moon,
    SquareArrowOutUpRight,
    Sun,
    UserRoundCog,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useAppearance, type Appearance } from '@/hooks/use-appearance';
import { useInitials } from '@/hooks/use-initials';
import { usePwaInstall } from '@/lib/pwa';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import sso from '@/routes/sso';
import type { SharedData } from '@/types';
import { BottomSheet } from './bottom-sheet';

const appearanceOptions: {
    value: Appearance;
    label: string;
    icon: typeof Sun;
}[] = [
    { value: 'light', label: 'Terang', icon: Sun },
    { value: 'dark', label: 'Gelap', icon: Moon },
    { value: 'system', label: 'Sistem', icon: Monitor },
];

/** Isi tab "Akun": profil, tampilan, pasang aplikasi, dan keluar. */
export function AccountPanel() {
    const { auth, branding, sso: ssoLinks } = usePage<SharedData>().props;
    const getInitials = useInitials();
    const { appearance, updateAppearance } = useAppearance();
    const { state: installState, install } = usePwaInstall();
    const [iosGuideOpen, setIosGuideOpen] = useState(false);
    const [logoutOpen, setLogoutOpen] = useState(false);
    const user = auth.user;

    const handleInstall = async () => {
        if (installState === 'ios') {
            setIosGuideOpen(true);

            return;
        }

        if (await install()) {
            toast.success('Aplikasi berhasil dipasang');
        }
    };

    const rowClass =
        'flex w-full items-center gap-3 px-4 py-3.5 text-left text-sm font-medium active:bg-muted';

    return (
        <div className="space-y-4">
            <div className="flex items-center gap-3 rounded-3xl bg-gradient-to-br from-primary/15 to-primary/5 p-4">
                <Avatar className="size-12 rounded-2xl">
                    <AvatarImage src={user.avatar} alt={user.name} />
                    <AvatarFallback className="rounded-2xl bg-primary text-base font-semibold text-primary-foreground">
                        {getInitials(user.name)}
                    </AvatarFallback>
                </Avatar>
                <div className="min-w-0 flex-1">
                    <p className="truncate font-semibold">{user.name}</p>
                    <p className="truncate text-xs text-muted-foreground">
                        {branding?.name} · {user.email}
                    </p>
                </div>
            </div>

            <div className="grid grid-cols-3 gap-1 rounded-2xl bg-muted p-1">
                {appearanceOptions.map(({ value, label, icon: Icon }) => (
                    <button
                        key={value}
                        type="button"
                        onClick={() => updateAppearance(value)}
                        className={cn(
                            'flex items-center justify-center gap-1.5 rounded-xl py-2 text-xs font-medium transition-colors',
                            appearance === value
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground',
                        )}
                    >
                        <Icon className="size-3.5" />
                        {label}
                    </button>
                ))}
            </div>

            <div className="divide-y overflow-hidden rounded-2xl border bg-card">
                <Link href={edit()} className={rowClass}>
                    <UserRoundCog className="size-5 text-muted-foreground" />
                    <span className="flex-1">Profil & keamanan</span>
                    <ChevronRight className="size-4 text-muted-foreground" />
                </Link>

                {ssoLinks?.newzakica && (
                    <a
                        href={sso.newzakica().url}
                        target="_blank"
                        rel="noopener"
                        className={rowClass}
                    >
                        <SquareArrowOutUpRight className="size-5 text-muted-foreground" />
                        <span className="flex-1">POS & Backoffice CV</span>
                        <ChevronRight className="size-4 text-muted-foreground" />
                    </a>
                )}

                {(installState === 'available' || installState === 'ios') && (
                    <button
                        type="button"
                        onClick={handleInstall}
                        className={rowClass}
                    >
                        <Download className="size-5 text-primary" />
                        <span className="flex-1">Pasang aplikasi di HP</span>
                        <ChevronRight className="size-4 text-muted-foreground" />
                    </button>
                )}
            </div>

            <button
                type="button"
                onClick={() => setLogoutOpen(true)}
                className="flex h-12 w-full items-center justify-center gap-2 rounded-2xl border border-destructive/30 bg-destructive/5 text-sm font-semibold text-destructive active:bg-destructive/10"
                data-test="mobile-logout-button"
            >
                <LogOut className="size-[18px]" />
                Keluar
            </button>

            <BottomSheet
                open={logoutOpen}
                onOpenChange={setLogoutOpen}
                title="Keluar dari akun?"
                description="Kamu perlu login lagi untuk membuka backoffice."
            >
                <div className="grid gap-2 pt-3">
                    <Link
                        href={logout()}
                        as="button"
                        onClick={() => router.flushAll()}
                        className="flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-destructive text-sm font-semibold text-white active:opacity-90"
                        data-test="mobile-logout-confirm"
                    >
                        <LogOut className="size-[18px]" />
                        Ya, keluar
                    </Link>
                    <button
                        type="button"
                        onClick={() => setLogoutOpen(false)}
                        className="h-12 w-full rounded-2xl bg-muted text-sm font-semibold active:opacity-80"
                    >
                        Batal
                    </button>
                </div>
            </BottomSheet>

            <IosInstallGuide
                open={iosGuideOpen}
                onOpenChange={setIosGuideOpen}
            />
        </div>
    );
}

export function IosInstallGuide({
    open,
    onOpenChange,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const steps = [
        'Buka halaman ini di Safari.',
        'Ketuk tombol Bagikan (kotak dengan panah ke atas) di bawah layar.',
        'Pilih “Tambah ke Layar Utama”, lalu ketuk “Tambah”.',
    ];

    return (
        <BottomSheet
            open={open}
            onOpenChange={onOpenChange}
            title="Pasang di iPhone"
            description="Aplikasi akan muncul di layar utama seperti aplikasi biasa."
        >
            <ol className="space-y-3 pt-2">
                {steps.map((step, index) => (
                    <li key={step} className="flex gap-3 text-sm">
                        <span className="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                            {index + 1}
                        </span>
                        <span className="pt-1">{step}</span>
                    </li>
                ))}
            </ol>
        </BottomSheet>
    );
}
