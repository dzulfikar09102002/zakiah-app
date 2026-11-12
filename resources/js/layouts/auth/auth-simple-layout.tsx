import { Link } from '@inertiajs/react';
import { BadgePercent, Boxes, ChartNoAxesCombined } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { BoutiqueIllustration } from '@/components/auth/boutique-illustration';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

const highlights = [
    { icon: Boxes, label: 'Stok multi-lokasi' },
    { icon: BadgePercent, label: 'Promo & loyalti' },
    { icon: ChartNoAxesCombined, label: 'Laporan penjualan' },
];

/**
 * Halaman auth: ilustrasi butik (8 kolom) di kiri, kartu form (4 kolom) di kanan.
 * Di bawah lg hanya kartu yang tampil.
 */
export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="grid min-h-svh w-full bg-background lg:grid-cols-12">
            {/* ── Kiri: ilustrasi vektor ── */}
            <div className="relative hidden overflow-hidden bg-[#243146] lg:col-span-8 lg:block">
                <BoutiqueIllustration />

                <div className="relative z-10 flex h-full flex-col justify-between p-10 xl:p-14">
                    <div className="max-w-md text-white select-none">
                        <p className="auth-fade-up-1 flex items-center gap-3 text-[11px] font-semibold tracking-[0.3em] text-[#fbe6b8] uppercase">
                            <span className="h-px w-8 bg-[#e3b873]" />
                            Zakiah Hijab &amp; Secaca
                        </p>
                        <h2 className="auth-fade-up-2 mt-5 text-[clamp(2.2rem,3.6vw,3.4rem)] leading-[1.08] font-bold tracking-tight drop-shadow-sm">
                            Favorite
                            <br />
                            <span className="text-[#fbe6b8]">Muslim Wear</span>
                        </h2>
                        <p className="auth-fade-up-3 mt-4 max-w-sm text-sm leading-relaxed text-white/80">
                            Pusat fashion viral, solusi anggun hijab sejak 2014.
                            Kelola seluruh toko dari satu backoffice.
                        </p>
                        <ul className="auth-fade-up-4 mt-6 hidden flex-wrap gap-2 xl:flex">
                            {highlights.map(({ icon: Icon, label }) => (
                                <li
                                    key={label}
                                    className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-medium text-white backdrop-blur-sm"
                                >
                                    <Icon
                                        aria-hidden="true"
                                        className="size-3.5 text-[#fbe6b8]"
                                    />
                                    {label}
                                </li>
                            ))}
                        </ul>
                    </div>

                    <p className="auth-fade-up-5 w-fit rounded-full border border-white/20 bg-black/15 px-4 py-1.5 text-[10px] font-medium tracking-[0.22em] text-white/85 uppercase backdrop-blur-sm">
                        Since 2014 · East Java, Indonesia
                    </p>
                </div>
            </div>

            {/* ── Kanan: kartu form ── */}
            <div className="relative flex min-h-svh flex-col items-center justify-center overflow-hidden p-5 sm:p-8 lg:col-span-4 lg:p-6 xl:p-10">
                {/* Latar lembut: motif titik + cahaya warna brand */}
                <div
                    aria-hidden="true"
                    className="absolute inset-0 [background-image:radial-gradient(color-mix(in_oklab,var(--primary)_18%,transparent)_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_75%)] [background-size:22px_22px] opacity-60"
                />
                <div
                    aria-hidden="true"
                    className="auth-orb absolute -top-24 -right-24 size-72 rounded-full bg-[#1f7f8c]/15 blur-3xl"
                />
                <div
                    aria-hidden="true"
                    className="auth-orb absolute -bottom-24 -left-24 size-64 rounded-full bg-gold/20 blur-3xl [animation-delay:2s]"
                />

                <div className="auth-fade-up-1 relative z-10 w-full max-w-sm">
                    <Card className="gap-0 overflow-hidden rounded-2xl border-border/70 py-0 shadow-[0_24px_60px_-24px_rgb(90_40_20/0.35)]">
                        <div
                            aria-hidden="true"
                            className="h-1.5 bg-[linear-gradient(90deg,#2f3e56,#d6b07a_35%,#1f7f8c_70%,#e5545a)]"
                        />

                        <CardHeader className="relative px-6 pt-7 pb-2 sm:px-8">
                            <div className="auth-fade-up-2 flex flex-col items-center gap-4">
                                <Link
                                    href={home()}
                                    className="group flex items-center gap-3 rounded-2xl outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <div className="flex size-12 items-center justify-center rounded-2xl bg-white shadow-md ring-1 shadow-primary/15 ring-primary/15 transition-transform duration-200 group-hover:scale-105">
                                        <AppLogoIcon className="size-8 object-contain" />
                                    </div>
                                    <span
                                        className="h-7 w-px bg-border"
                                        aria-hidden
                                    />
                                    <div className="flex size-12 items-center justify-center rounded-2xl bg-white shadow-md ring-1 shadow-primary/15 ring-primary/15 transition-transform duration-200 group-hover:scale-105 dark:bg-card">
                                        {/* Logo Secaca: versi gelap untuk tema terang, versi terang untuk tema gelap */}
                                        <img
                                            src="/assets/images/secaca.png"
                                            alt="Logo Secaca"
                                            className="w-8 object-contain dark:hidden"
                                        />
                                        <img
                                            src="/assets/images/secaca-light.png"
                                            alt="Logo Secaca"
                                            className="hidden w-8 object-contain dark:block"
                                        />
                                    </div>
                                    <span className="sr-only">Home</span>
                                </Link>

                                <div className="space-y-1.5 text-center">
                                    <CardTitle className="text-2xl leading-tight font-bold tracking-tight text-foreground">
                                        {title}
                                    </CardTitle>
                                    <CardDescription className="text-[13px] leading-relaxed text-muted-foreground">
                                        {description}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent className="auth-fade-up-3 relative px-6 pt-5 pb-7 sm:px-8">
                            {children}

                            <p className="auth-fade-up-4 mt-7 border-t border-border/70 pt-5 text-center text-[11px] leading-[1.7] text-muted-foreground">
                                &copy; {new Date().getFullYear()} Zakiah Hijab
                                &amp; Secaca. All rights reserved.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
