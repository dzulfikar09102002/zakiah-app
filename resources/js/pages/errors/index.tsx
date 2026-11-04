import { Head } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import {
    ArrowLeft,
    Construction,
    FileQuestion,
    Hourglass,
    House,
    LockKeyhole,
    LogIn,
    RotateCw,
    ServerCrash,
    ShieldX,
    TimerOff,
} from 'lucide-react';
import {
    GlassBrand,
    GlassPage,
    glassMuted,
    glassPrimary,
    glassSecondary,
} from '@/components/glass-page';

type ErrorContent = {
    icon: LucideIcon;
    title: string;
    description: string;
    action: 'home' | 'login' | 'reload';
};

const contents: Record<number, ErrorContent> = {
    401: {
        icon: LockKeyhole,
        title: 'Silakan Login Dulu',
        description: 'Halaman ini hanya bisa dibuka setelah Anda masuk.',
        action: 'login',
    },
    403: {
        icon: ShieldX,
        title: 'Akses Ditolak',
        description: 'Akun Anda tidak punya izin untuk membuka halaman ini.',
        action: 'home',
    },
    404: {
        icon: FileQuestion,
        title: 'Halaman Tidak Ditemukan',
        description:
            'Halaman atau data yang Anda cari tidak ada, atau sudah dihapus.',
        action: 'home',
    },
    419: {
        icon: TimerOff,
        title: 'Sesi Kedaluwarsa',
        description:
            'Halaman terlalu lama dibiarkan terbuka. Muat ulang lalu coba lagi.',
        action: 'reload',
    },
    429: {
        icon: Hourglass,
        title: 'Terlalu Banyak Permintaan',
        description: 'Tunggu sebentar, lalu coba lagi.',
        action: 'reload',
    },
    500: {
        icon: ServerCrash,
        title: 'Terjadi Kesalahan',
        description:
            'Ada masalah di server kami. Data Anda aman, silakan coba lagi beberapa saat.',
        action: 'reload',
    },
    503: {
        icon: Construction,
        title: 'Sedang Perbaikan',
        description:
            'Aplikasi sedang dalam perbaikan. Silakan kembali beberapa saat lagi.',
        action: 'reload',
    },
};

export default function ErrorPage({
    status,
    message,
}: {
    status: number;
    message?: string | null;
}) {
    const content = contents[status] ?? contents[500];
    const Icon = content.icon;

    return (
        <GlassPage className="max-w-md text-center">
            <Head title={content.title} />

            <GlassBrand />

            <div className="my-6 flex flex-col items-center gap-3">
                <span className="flex size-16 items-center justify-center rounded-2xl bg-[#cc7455]/15 text-[#9b462c]">
                    <Icon className="size-8" />
                </span>
                <p className="text-5xl font-extrabold tracking-tight text-[#9b462c]">
                    {status}
                </p>
                <h1 className="text-xl font-bold">{content.title}</h1>
                <p className={`text-sm leading-relaxed ${glassMuted}`}>
                    {message || content.description}
                </p>
            </div>

            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-center">
                <button
                    type="button"
                    className={glassSecondary}
                    onClick={() => window.history.back()}
                >
                    <ArrowLeft className="size-4" /> Kembali
                </button>

                {content.action === 'login' && (
                    <a href="/login" className={glassPrimary}>
                        <LogIn className="size-4" /> Login
                    </a>
                )}
                {content.action === 'reload' && (
                    <button
                        type="button"
                        className={glassPrimary}
                        onClick={() => window.location.reload()}
                    >
                        <RotateCw className="size-4" /> Muat Ulang
                    </button>
                )}
                {content.action === 'home' && (
                    <a href="/dashboard" className={glassPrimary}>
                        <House className="size-4" /> Ke Beranda
                    </a>
                )}
            </div>
        </GlassPage>
    );
}
