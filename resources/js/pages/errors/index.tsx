import { Head } from '@inertiajs/react';
import { ArrowLeft, House, LogIn, RotateCw } from 'lucide-react';
import type { ErrorScene } from '@/components/error-illustration';
import { ErrorIllustration } from '@/components/error-illustration';
import {
    GlassBrand,
    GlassPage,
    glassMuted,
    glassPrimary,
    glassSecondary,
} from '@/components/glass-page';

type ErrorContent = {
    scene: ErrorScene;
    title: string;
    description: string;
    action: 'home' | 'login' | 'reload';
};

const contents: Record<number, ErrorContent> = {
    400: {
        scene: 'document',
        title: 'Permintaan Tidak Valid',
        description:
            'Data yang dikirim tidak bisa diproses. Periksa kembali isian Anda lalu coba lagi.',
        action: 'home',
    },
    401: {
        scene: 'lock',
        title: 'Silakan Login Dulu',
        description: 'Halaman ini hanya bisa dibuka setelah Anda masuk.',
        action: 'login',
    },
    403: {
        scene: 'lock',
        title: 'Akses Ditolak',
        description: 'Akun Anda tidak punya izin untuk membuka halaman ini.',
        action: 'home',
    },
    404: {
        scene: 'search',
        title: 'Halaman Tidak Ditemukan',
        description:
            'Halaman atau data yang Anda cari tidak ada, atau sudah dihapus.',
        action: 'home',
    },
    405: {
        scene: 'document',
        title: 'Aksi Tidak Didukung',
        description:
            'Cara membuka halaman ini tidak didukung. Kembali lalu ulangi dari menu.',
        action: 'home',
    },
    408: {
        scene: 'hourglass',
        title: 'Waktu Habis',
        description:
            'Server menunggu terlalu lama. Periksa koneksi Anda lalu coba lagi.',
        action: 'reload',
    },
    413: {
        scene: 'document',
        title: 'File Terlalu Besar',
        description:
            'Ukuran data atau file yang dikirim melebihi batas. Perkecil lalu coba lagi.',
        action: 'home',
    },
    419: {
        scene: 'hourglass',
        title: 'Sesi Kedaluwarsa',
        description:
            'Halaman terlalu lama dibiarkan terbuka. Muat ulang lalu coba lagi.',
        action: 'reload',
    },
    429: {
        scene: 'hourglass',
        title: 'Terlalu Banyak Permintaan',
        description: 'Tunggu sebentar, lalu coba lagi.',
        action: 'reload',
    },
    500: {
        scene: 'server',
        title: 'Terjadi Kesalahan',
        description:
            'Ada masalah di server kami. Data Anda aman, silakan coba lagi beberapa saat.',
        action: 'reload',
    },
    502: {
        scene: 'server',
        title: 'Server Tidak Merespons',
        description:
            'Server perantara gagal menghubungi aplikasi. Coba lagi beberapa saat.',
        action: 'reload',
    },
    503: {
        scene: 'gears',
        title: 'Sedang Perbaikan',
        description:
            'Aplikasi sedang dalam perbaikan. Silakan kembali beberapa saat lagi.',
        action: 'reload',
    },
    504: {
        scene: 'server',
        title: 'Server Terlalu Lama',
        description:
            'Server butuh waktu terlalu lama untuk menjawab. Coba lagi beberapa saat.',
        action: 'reload',
    },
};

/** Status yang tidak terdaftar memakai isi kelompoknya (4xx atau 5xx). */
function contentFor(status: number): ErrorContent {
    return contents[status] ?? (status < 500 ? contents[400] : contents[500]);
}

/** Partikel kecil yang naik pelan di latar kartu. */
const particles = [
    { left: '12%', size: 6, delay: '0s', duration: '7s' },
    { left: '28%', size: 4, delay: '2.2s', duration: '6s' },
    { left: '50%', size: 5, delay: '1.1s', duration: '8s' },
    { left: '70%', size: 4, delay: '3.4s', duration: '6.5s' },
    { left: '86%', size: 6, delay: '0.6s', duration: '7.5s' },
];

export default function ErrorPage({
    status,
    message,
}: {
    status: number;
    message?: string | null;
}) {
    const content = contentFor(status);

    return (
        <GlassPage className="max-w-md text-center">
            <Head title={content.title} />

            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-x-0 bottom-0 h-40"
            >
                {particles.map((particle) => (
                    <span
                        key={particle.left}
                        className="err-particle absolute bottom-0 rounded-full bg-[var(--g-accent)]"
                        style={{
                            left: particle.left,
                            width: particle.size,
                            height: particle.size,
                            animationDelay: particle.delay,
                            animationDuration: particle.duration,
                        }}
                    />
                ))}
            </div>

            <div className="relative">
                <div className="err-rise">
                    <GlassBrand />
                </div>

                <div className="my-6 flex flex-col items-center gap-3">
                    <div
                        className="err-rise"
                        style={{ animationDelay: '0.1s' }}
                    >
                        <ErrorIllustration scene={content.scene} />
                    </div>

                    <p
                        className="flex text-6xl font-extrabold tracking-tight text-[var(--g-primary)] tabular-nums"
                        aria-label={`Kode ${status}`}
                    >
                        {String(status)
                            .split('')
                            .map((digit, index) => (
                                <span
                                    key={index}
                                    aria-hidden="true"
                                    className="err-digit"
                                    style={{
                                        animationDelay: `${0.25 + index * 0.12}s`,
                                    }}
                                >
                                    {digit}
                                </span>
                            ))}
                    </p>

                    <h1
                        className="err-rise text-xl font-bold"
                        style={{ animationDelay: '0.55s' }}
                    >
                        {content.title}
                    </h1>
                    <p
                        className={`err-rise text-sm leading-relaxed ${glassMuted}`}
                        style={{ animationDelay: '0.65s' }}
                    >
                        {message || content.description}
                    </p>
                </div>

                <div
                    className="err-rise flex flex-col-reverse gap-2 sm:flex-row sm:justify-center"
                    style={{ animationDelay: '0.75s' }}
                >
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
            </div>
        </GlassPage>
    );
}
