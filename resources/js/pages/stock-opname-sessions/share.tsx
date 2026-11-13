import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CalendarDays,
    Check,
    Copy,
    Expand,
    MapPin,
    Printer,
    Share2,
    TriangleAlert,
    UserRound,
    Users,
} from 'lucide-react';
import { useState } from 'react';
import {
    GlassBrand,
    GlassPage,
    glassMuted,
    glassSecondary,
} from '@/components/glass-page';
import { formatSessionDate } from '@/components/stock-opname-session/helpers';
import productOpnameSessions from '@/routes/product-opname-sessions';

type Props = {
    session: {
        id: number;
        code: string;
        location: string | null;
        opname_date: string;
        pic: string;
        employees_count: number;
    };
    url: string;
    qr: { size: number; path: string };
    isLocalAddress: boolean;
};

const steps = [
    'Buka kamera HP, arahkan ke QR ini',
    'Login dengan akun Anda bila diminta',
    'Sesi SO terbuka, langsung mulai scan barang',
];

/** Layar QR untuk dibagikan PJ, bisa ditampilkan di layar besar atau dicetak. */
export default function ShareSession({
    session,
    url,
    qr,
    isLocalAddress,
}: Props) {
    const [copied, setCopied] = useState(false);
    const canShare = typeof navigator !== 'undefined' && !!navigator.share;

    const copyLink = async () => {
        try {
            await navigator.clipboard.writeText(url);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch {
            // Clipboard hanya ada di HTTPS; tautan tetap terlihat di layar untuk disalin manual.
            setCopied(false);
        }
    };

    const shareLink = () =>
        navigator
            .share({
                title: `Sesi SO ${session.code}`,
                text: `Masuk ke sesi SO ${session.code}`,
                url,
            })
            .catch(() => {});

    const toggleFullscreen = () => {
        if (document.fullscreenElement) {
            void document.exitFullscreen();
        } else {
            void document.documentElement.requestFullscreen?.();
        }
    };

    const details = [
        { icon: MapPin, value: session.location ?? '-' },
        { icon: CalendarDays, value: formatSessionDate(session.opname_date) },
        { icon: UserRound, value: `PJ: ${session.pic}` },
        { icon: Users, value: `${session.employees_count} karyawan terdaftar` },
    ];

    return (
        <GlassPage className="max-w-5xl">
            <Head title={`QR Sesi SO ${session.code}`} />

            <style>{`
                @keyframes so-qr-scan { 0%, 100% { top: 6%; } 50% { top: 92%; } }
                @media (prefers-reduced-motion: reduce) { .so-qr-line { display: none; } }
                @media print { .so-qr-line { display: none; } }
            `}</style>

            <div className="grid items-center gap-6 md:grid-cols-[minmax(0,1fr)_auto] md:gap-10">
                <div className="min-w-0 space-y-5">
                    <GlassBrand title="Masuk Sesi SO" align="start" />

                    <div className="space-y-2">
                        <p className="text-xl font-bold break-all">
                            {session.code}
                        </p>
                        {details.map(({ icon: Icon, value }) => (
                            <p
                                key={value}
                                className={`flex items-center gap-2 text-sm font-medium ${glassMuted}`}
                            >
                                <Icon className="size-4 shrink-0 text-[var(--g-primary)]" />
                                <span className="min-w-0 break-words">
                                    {value}
                                </span>
                            </p>
                        ))}
                    </div>

                    <ol className="space-y-2">
                        {steps.map((step, index) => (
                            <li
                                key={step}
                                className="flex items-center gap-3 text-sm font-semibold"
                            >
                                <span className="flex size-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[var(--g-primary)] to-[var(--g-primary-2)] text-xs text-white">
                                    {index + 1}
                                </span>
                                {step}
                            </li>
                        ))}
                    </ol>

                    <p className={`text-xs ${glassMuted}`}>
                        Hanya karyawan yang terdaftar di sesi ini yang bisa
                        masuk. Yang lain akan ditolak.
                    </p>
                </div>

                <div className="mx-auto w-full max-w-sm min-w-0 md:w-80 lg:w-96">
                    <div className="relative rounded-3xl bg-white p-4 shadow-[0_30px_60px_-30px_rgba(31,42,60,0.55),0_0_0_6px_color-mix(in_oklab,var(--g-primary)_18%,transparent)]">
                        <svg
                            role="img"
                            aria-label={`QR masuk sesi ${session.code}`}
                            viewBox={`0 0 ${qr.size} ${qr.size}`}
                            shapeRendering="crispEdges"
                            className="block aspect-square w-full"
                        >
                            <path d={qr.path} fill="#000000" />
                        </svg>
                        <div
                            aria-hidden="true"
                            style={{
                                animation:
                                    'so-qr-scan 2.8s ease-in-out infinite',
                            }}
                            className="so-qr-line absolute inset-x-[6%] h-[3px] rounded-full bg-gradient-to-r from-transparent via-[var(--g-accent)] to-transparent shadow-[0_0_14px_var(--g-accent)]"
                        />
                    </div>
                    <p
                        className={`mt-3 text-center text-xs break-all ${glassMuted}`}
                    >
                        {url}
                    </p>
                </div>
            </div>

            {isLocalAddress && (
                <p className="mt-5 flex items-start gap-2 rounded-xl bg-amber-500/20 p-3 text-sm font-medium print:hidden">
                    <TriangleAlert className="mt-0.5 size-4 shrink-0 text-amber-700" />
                    Halaman ini dibuka lewat alamat lokal, jadi QR ini tidak
                    bisa dibuka dari HP lain. Buka lewat alamat HTTPS aplikasi
                    supaya QR-nya bisa dipakai.
                </p>
            )}

            <div className="mt-6 flex flex-wrap gap-2 print:hidden">
                <Link
                    href={productOpnameSessions.show(session.id).url}
                    className={glassSecondary}
                >
                    <ArrowLeft className="size-4" /> Kembali
                </Link>
                {canShare && (
                    <button
                        type="button"
                        className={glassSecondary}
                        onClick={shareLink}
                    >
                        <Share2 className="size-4" /> Bagikan
                    </button>
                )}
                <button
                    type="button"
                    className={glassSecondary}
                    onClick={copyLink}
                >
                    {copied ? (
                        <Check className="size-4" />
                    ) : (
                        <Copy className="size-4" />
                    )}
                    {copied ? 'Tersalin' : 'Salin Tautan'}
                </button>
                <button
                    type="button"
                    className={glassSecondary}
                    onClick={() => window.print()}
                >
                    <Printer className="size-4" /> Cetak
                </button>
                <button
                    type="button"
                    className={`${glassSecondary} hidden md:inline-flex`}
                    onClick={toggleFullscreen}
                >
                    <Expand className="size-4" /> Layar Penuh
                </button>
            </div>
        </GlassPage>
    );
}
