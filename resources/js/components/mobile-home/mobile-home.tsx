import { Link, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { motion } from 'framer-motion';
import {
    ArrowDownRight,
    ArrowUpRight,
    ChevronDown,
    ChevronRight,
    Download,
    Eye,
    EyeOff,
    LayoutGrid,
    LineChart,
    Loader2,
    MapPin,
    Menu,
    RefreshCw,
    Smartphone,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { IosInstallGuide } from '@/components/mobile/account-panel';
import { MenuTile } from '@/components/mobile/menu-tile';
import { useMobileNav } from '@/components/mobile/mobile-nav-context';
import { AnimatedValue } from '@/components/ui/count-up';
import { Skeleton } from '@/components/ui/skeleton';
import { usePullToRefresh } from '@/hooks/use-pull-to-refresh';
import { quickActions } from '@/lib/mobile-nav';
import { usePwaInstall } from '@/lib/pwa';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';
import {
    HomeCard,
    MASKED,
    StockCard,
    TopCard,
    TrendCard,
    toRupiahCompact,
} from './home-cards';
import {
    formatRange,
    LocationSheet,
    locationLabel,
    PeriodChips,
} from './home-filters';
import {
    comparisonLabels,
    periodLabels,
    useHomeDashboard,
} from './use-home-dashboard';

function greeting(date = new Date()): string {
    const hour = date.getHours();

    if (hour < 11) {
        return 'Selamat pagi';
    }

    if (hour < 15) {
        return 'Selamat siang';
    }

    if (hour < 18) {
        return 'Selamat sore';
    }

    return 'Selamat malam';
}

const readFlag = (key: string) => {
    try {
        return localStorage.getItem(key) === '1';
    } catch {
        return false;
    }
};

const writeFlag = (key: string, value: boolean) => {
    try {
        localStorage.setItem(key, value ? '1' : '0');
    } catch {
        // localStorage tidak tersedia; preferensi hanya berlaku di sesi ini.
    }
};

function GrowthBadge({
    current,
    previous,
    label,
}: {
    current: number;
    previous: number | undefined;
    label: string;
}) {
    if (previous === undefined) {
        return <Skeleton className="h-5 w-40 rounded-full" />;
    }

    if (previous === 0) {
        return (
            <p className="text-xs text-muted-foreground">
                Belum ada pembanding {label}
            </p>
        );
    }

    const change = ((current - previous) / Math.abs(previous)) * 100;
    const up = change >= 0;
    const Icon = up ? ArrowUpRight : ArrowDownRight;

    return (
        <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
            <span
                className={cn(
                    'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 font-semibold',
                    up
                        ? 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400'
                        : 'bg-rose-500/12 text-rose-600 dark:text-rose-400',
                )}
            >
                <Icon className="size-3.5" />
                {Math.abs(change).toLocaleString('id-ID', {
                    maximumFractionDigits: 1,
                })}
                %
            </span>
            {label}
        </p>
    );
}

function InstallCard() {
    const { state, install } = usePwaInstall();
    const [dismissed, setDismissed] = useState(() =>
        readFlag('pwa:install-dismissed'),
    );
    const [iosGuideOpen, setIosGuideOpen] = useState(false);

    if (dismissed || (state !== 'available' && state !== 'ios')) {
        return null;
    }

    const dismiss = () => {
        writeFlag('pwa:install-dismissed', true);
        setDismissed(true);
    };

    return (
        <>
            <div className="relative flex items-center gap-3 overflow-hidden rounded-3xl bg-gradient-to-r from-foreground to-foreground/85 p-4 pr-10 text-background">
                <span className="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-background/15">
                    <Smartphone className="size-5" />
                </span>
                <div className="min-w-0 flex-1">
                    <p className="text-sm font-semibold">Pasang di HP kamu</p>
                    <p className="text-xs opacity-75">
                        Buka lebih cepat, langsung dari layar utama.
                    </p>
                </div>
                <button
                    type="button"
                    onClick={async () => {
                        if (state === 'ios') {
                            setIosGuideOpen(true);

                            return;
                        }

                        if (await install()) {
                            toast.success('Aplikasi berhasil dipasang');
                        }
                    }}
                    className="flex h-9 shrink-0 items-center gap-1 rounded-xl bg-primary px-3.5 text-xs font-semibold text-primary-foreground active:scale-95"
                >
                    <Download className="size-3.5" />
                    Pasang
                </button>
                <button
                    type="button"
                    onClick={dismiss}
                    aria-label="Tutup"
                    className="absolute top-2 right-2 flex size-7 items-center justify-center rounded-full opacity-60 active:bg-background/10"
                >
                    <X className="size-4" />
                </button>
            </div>
            <IosInstallGuide
                open={iosGuideOpen}
                onOpenChange={setIosGuideOpen}
            />
        </>
    );
}

/** Beranda khusus layar HP: gaya aplikasi m-banking / e-wallet. */
export default function MobileHome() {
    const { auth, branding } = usePage<SharedData>().props;
    const { setOpenSheet } = useMobileNav();
    const dashboard = useHomeDashboard();
    const { filters, summary, previous, refund } = dashboard;

    const [hidden, setHidden] = useState(() => readFlag('mobile-home:hidden'));
    const [locationOpen, setLocationOpen] = useState(false);
    const [locationKey, setLocationKey] = useState(0);

    const { pull, refreshing, progress } = usePullToRefresh(dashboard.refresh);

    const toggleHidden = () => {
        writeFlag('mobile-home:hidden', !hidden);
        setHidden(!hidden);
    };

    const openLocations = () => {
        setLocationKey((key) => key + 1);
        setLocationOpen(true);
    };

    const user = auth.user;
    const firstName = user.name.split(' ')[0];
    const periodLabel =
        filters.period === 'custom'
            ? formatRange(filters.range)
            : periodLabels[filters.period];

    return (
        <div className="relative min-h-dvh bg-background">
            {/* Indikator tarik untuk memuat ulang */}
            <div
                className="pointer-events-none fixed inset-x-0 top-[calc(env(safe-area-inset-top)+8px)] z-30 flex justify-center"
                style={{
                    transform: `translateY(${refreshing ? 48 : pull * 0.8}px)`,
                    opacity: refreshing ? 1 : progress,
                    transition: pull
                        ? 'none'
                        : 'transform 200ms, opacity 200ms',
                }}
            >
                <span className="flex size-10 items-center justify-center rounded-full bg-card text-primary shadow-lg">
                    {refreshing ? (
                        <Loader2 className="size-5 animate-spin" />
                    ) : (
                        <RefreshCw
                            className="size-5"
                            style={{
                                transform: `rotate(${progress * 270}deg)`,
                            }}
                        />
                    )}
                </span>
            </div>

            {/* Header berwarna */}
            <header className="relative overflow-hidden bg-gradient-to-br from-[#d98260] via-primary to-[#9b462c] px-4 pt-[calc(env(safe-area-inset-top)+14px)] pb-28 text-white">
                <div className="pointer-events-none absolute -top-16 -right-10 size-56 rounded-full bg-white/10" />
                <div className="pointer-events-none absolute top-20 -right-24 size-56 rounded-full bg-white/5" />
                <div className="pointer-events-none absolute -bottom-20 -left-16 size-48 rounded-full bg-black/5" />

                <div className="relative flex items-center gap-3">
                    <span className="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white p-1.5 shadow-sm">
                        {branding?.logo ? (
                            <img
                                src={branding.logo}
                                alt={branding.name}
                                className="size-full object-contain"
                            />
                        ) : (
                            <span className="text-lg font-bold text-primary">
                                {branding?.name?.[0] ?? 'Z'}
                            </span>
                        )}
                    </span>
                    <div className="min-w-0 flex-1">
                        <p className="text-xs text-white/80">{greeting()},</p>
                        <p className="truncate text-lg leading-tight font-semibold">
                            {firstName} 👋
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setOpenSheet('menu')}
                        aria-label="Buka menu"
                        className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm active:scale-95 active:bg-white/25"
                    >
                        <Menu className="size-5" />
                    </button>
                </div>

                <button
                    type="button"
                    onClick={openLocations}
                    className="relative mt-4 inline-flex max-w-full items-center gap-1.5 rounded-xl bg-white/15 py-1.5 pr-3 pl-2.5 text-[13px] font-medium backdrop-blur-sm active:bg-white/25"
                >
                    <MapPin className="size-4 shrink-0" />
                    <span className="truncate">
                        {branding?.name} ·{' '}
                        {locationLabel(filters, dashboard.locationOptions)}
                    </span>
                    <ChevronDown className="size-4 shrink-0" />
                </button>
            </header>

            <div className="relative -mt-24 space-y-4 px-4">
                {/* Kartu "saldo": omzet periode terpilih */}
                <motion.section
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.35, ease: 'easeOut' }}
                    className="rounded-[28px] border border-border/50 bg-card p-5 shadow-[0_18px_40px_-18px_rgb(155_70_44/0.45)]"
                >
                    <div className="flex items-center justify-between">
                        <p className="text-[13px] font-medium text-muted-foreground">
                            Omzet · {periodLabel}
                        </p>
                        <button
                            type="button"
                            onClick={toggleHidden}
                            aria-label={
                                hidden
                                    ? 'Tampilkan nominal'
                                    : 'Sembunyikan nominal'
                            }
                            className="-mr-2 flex size-9 items-center justify-center rounded-full text-muted-foreground active:bg-muted"
                        >
                            {hidden ? (
                                <EyeOff className="size-[18px]" />
                            ) : (
                                <Eye className="size-[18px]" />
                            )}
                        </button>
                    </div>

                    <div className="mt-0.5 mb-2 min-h-10 text-[32px] leading-10 font-bold tracking-tight tabular-nums">
                        {summary === null ? (
                            <Skeleton className="h-9 w-48" />
                        ) : hidden ? (
                            `Rp ${MASKED}`
                        ) : (
                            <AnimatedValue
                                value={summary.net_sales_after_tax}
                            />
                        )}
                    </div>

                    {summary === null ? (
                        <Skeleton className="h-5 w-40 rounded-full" />
                    ) : (
                        <GrowthBadge
                            current={summary.net_sales_after_tax}
                            previous={previous?.net_sales_after_tax}
                            label={comparisonLabels[filters.period]}
                        />
                    )}

                    <div className="mt-4 grid grid-cols-3 divide-x divide-border/70 rounded-2xl bg-muted/60 py-3">
                        {[
                            {
                                label: 'Laba bersih',
                                value:
                                    summary &&
                                    (hidden
                                        ? MASKED
                                        : toRupiahCompact(summary.net_profit)),
                            },
                            {
                                label: 'Transaksi',
                                value:
                                    summary &&
                                    summary.transaction_count.toLocaleString(
                                        'id-ID',
                                    ),
                            },
                            {
                                label: 'Retur',
                                value:
                                    refund !== null &&
                                    (hidden ? MASKED : toRupiahCompact(refund)),
                            },
                        ].map((stat) => (
                            <div key={stat.label} className="px-3 text-center">
                                <p className="text-[11px] text-muted-foreground">
                                    {stat.label}
                                </p>
                                {stat.value ? (
                                    <p className="truncate text-sm font-semibold tabular-nums">
                                        {stat.value}
                                    </p>
                                ) : (
                                    <Skeleton className="mx-auto mt-1 h-4 w-14" />
                                )}
                            </div>
                        ))}
                    </div>
                </motion.section>

                <PeriodChips filters={filters} onChange={dashboard.setPeriod} />

                {dashboard.error && (
                    <button
                        type="button"
                        onClick={dashboard.refresh}
                        className="flex w-full items-center gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-left text-sm text-destructive"
                    >
                        <RefreshCw className="size-4 shrink-0" />
                        Sebagian data gagal dimuat. Ketuk untuk coba lagi.
                    </button>
                )}

                {/* Grid layanan */}
                <HomeCard className="px-2 py-4">
                    <div className="grid grid-cols-4 gap-y-4">
                        {quickActions.map((action) => (
                            <MenuTile key={action.href} {...action} />
                        ))}
                        <MenuTile
                            title="Lainnya"
                            icon={LayoutGrid}
                            tone="slate"
                            onClick={() => setOpenSheet('menu')}
                        />
                    </div>
                </HomeCard>

                <InstallCard />

                <TrendCard data={dashboard.trend} hidden={hidden} />

                <TopCard top5={dashboard.top5} hidden={hidden} />

                <StockCard potential={dashboard.potential} hidden={hidden} />

                <Link
                    href="/dashboard?view=analytics"
                    className="flex items-center gap-3 rounded-3xl border border-dashed border-primary/40 bg-primary/5 p-4 active:bg-primary/10"
                >
                    <span className="flex size-10 items-center justify-center rounded-2xl bg-primary/15 text-primary">
                        <LineChart className="size-5" />
                    </span>
                    <span className="flex-1">
                        <span className="block text-sm font-semibold">
                            Analitik lengkap
                        </span>
                        <span className="block text-xs text-muted-foreground">
                            Grafik bulanan, tahunan & perbandingan
                        </span>
                    </span>
                    <ChevronRight className="size-5 text-muted-foreground" />
                </Link>

                {dashboard.updatedAt && (
                    <p className="pb-2 text-center text-[11px] text-muted-foreground">
                        Diperbarui{' '}
                        {format(dashboard.updatedAt, 'HH:mm', { locale: id })} ·
                        tarik ke bawah untuk memuat ulang
                    </p>
                )}
            </div>

            <LocationSheet
                key={locationKey}
                open={locationOpen}
                onOpenChange={setLocationOpen}
                options={dashboard.locationOptions}
                filters={filters}
                onApply={dashboard.setLocations}
            />
        </div>
    );
}
