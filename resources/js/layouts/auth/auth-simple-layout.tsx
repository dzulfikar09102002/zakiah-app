import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh w-full overflow-hidden bg-background">
            {/* ══════════════════════════════════════
                LEFT PANEL — brand illustration
            ══════════════════════════════════════ */}
            <div className="relative hidden flex-col items-center justify-center overflow-hidden bg-muted/30 lg:flex lg:w-1/2 dark:bg-muted/10">
                {/* ── Decorative SVG arabesque / floral ── */}
                <svg
                    className="auth-spin-slow absolute inset-0 h-full w-full opacity-10"
                    viewBox="0 0 600 800"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    {/* Outer lace ring */}
                    <circle cx="300" cy="400" r="260" className="stroke-primary" strokeWidth="0.8" strokeDasharray="6 10" />
                    <circle cx="300" cy="400" r="230" className="stroke-primary/80" strokeWidth="0.5" />
                    <circle cx="300" cy="400" r="195" className="stroke-primary" strokeWidth="0.8" strokeDasharray="3 7" />

                    {/* Corner petal motifs */}
                    {[0, 60, 120, 180, 240, 300].map((angle, i) => {
                        const rad = (angle * Math.PI) / 180;
                        const cx = 300 + 250 * Math.cos(rad);
                        const cy = 400 + 250 * Math.sin(rad);
                        return (
                            <g key={i}>
                                <circle cx={cx} cy={cy} r="18" className="stroke-primary" strokeWidth="0.8" fill="none" />
                                <circle cx={cx} cy={cy} r="8" className="stroke-primary/80 fill-primary/10" strokeWidth="0.6" />
                                <circle cx={cx} cy={cy} r="3" className="fill-primary" />
                            </g>
                        );
                    })}

                    {/* Inner ornament star */}
                    {[0, 45, 90, 135, 180, 225, 270, 315].map((angle, i) => {
                        const rad = (angle * Math.PI) / 180;
                        const x1 = 300 + 60 * Math.cos(rad);
                        const y1 = 400 + 60 * Math.sin(rad);
                        const x2 = 300 + 120 * Math.cos(rad);
                        const y2 = 400 + 120 * Math.sin(rad);
                        return (
                            <line key={i} x1={x1} y1={y1} x2={x2} y2={y2} className="stroke-primary/80" strokeWidth="0.6" />
                        );
                    })}
                    <circle cx="300" cy="400" r="55" className="stroke-primary" strokeWidth="0.8" fill="none" />

                    {/* Diamond corner accents */}
                    {[ [30, 60], [570, 60], [30, 740], [570, 740] ].map(([x, y], i) => (
                        <g key={i} transform={`translate(${x},${y})`}>
                            <rect x="-12" y="-12" width="24" height="24" className="stroke-primary" strokeWidth="0.7" fill="none" transform="rotate(45)" />
                            <rect x="-6" y="-6" width="12" height="12" className="stroke-primary/80 fill-primary/10" strokeWidth="0.5" transform="rotate(45)" />
                        </g>
                    ))}

                    {/* Top & bottom ornamental arcs */}
                    <path d="M 100 80 Q 300 20 500 80" className="stroke-primary" strokeWidth="0.7" fill="none" strokeDasharray="4 8" />
                    <path d="M 100 720 Q 300 780 500 720" className="stroke-primary" strokeWidth="0.7" fill="none" strokeDasharray="4 8" />
                </svg>

                {/* ── Rings pojok kiri atas ── */}
                <div className="auth-spin absolute -top-24 -left-24 h-96 w-96 rounded-full border-2 border-primary/40 opacity-20" />
                <div className="auth-spin-rev absolute -top-16 -left-16 h-80 w-80 rounded-full border border-dashed border-primary/60 opacity-15" />

                {/* ── Rings pojok kanan bawah ── */}
                <div className="auth-spin absolute -right-20 -bottom-20 h-104 w-104 rounded-full border-2 border-primary/40 opacity-20" />
                <div className="auth-spin-rev absolute -right-12 -bottom-12 h-80 w-80 rounded-full border border-dashed border-primary/60 opacity-10" />

                {/* ── Corner glow orbs ── */}
                <div className="auth-orb absolute -top-16 -left-16 h-56 w-56 rounded-full bg-primary/20 blur-3xl" />
                <div className="auth-float-b absolute -right-12 -bottom-12 h-48 w-48 rounded-full bg-secondary/30 blur-3xl delay-200" />
                <div className="auth-float-c absolute top-1/3 left-6 h-24 w-24 rounded-full bg-primary/20 blur-2xl delay-100" />
                <div className="auth-float-a absolute right-8 bottom-1/4 h-20 w-20 rounded-full bg-primary/10 blur-2xl delay-300" />

                {/* ── Brand text ── */}
                <div className="relative z-10 px-10 text-center select-none">
                    <p className="auth-fade-up-1 mb-5 text-[10px] font-bold tracking-[0.35em] uppercase text-primary">
                        Zakiah Hijab &amp; Secaca
                    </p>

                    <h2 className="auth-fade-up-2 mb-2 text-[clamp(2.4rem,4vw,3.2rem)] font-serif italic font-semibold leading-tight text-primary drop-shadow-md">
                        Favorite
                        <br />
                        Muslim Wear
                    </h2>

                    {/* Animated shimmer line */}
                    <div className="auth-fade-up-3 mx-auto mt-5 mb-5 h-0.5 w-28 overflow-hidden rounded-full bg-primary/20">
                        <div className="auth-gradient-bg h-full w-[300%] bg-gradient-to-r from-transparent via-primary to-transparent" />
                    </div>

                    <p className="auth-fade-up-4 mx-auto text-[13px] font-lato font-light leading-relaxed text-muted-foreground">
                        Pusat fashion viral, solusi anggun hijab sejak 2014.
                    </p>

                    {/* Animated ornament dots */}
                    <div className="auth-fade-up-5 mt-7 flex items-center justify-center gap-2">
                        <div className="h-1 w-1 rounded-full bg-primary/40" />
                        <div className="h-1.5 w-1.5 rounded-full bg-primary/60" />
                        <div className="h-2 w-2 rounded-full bg-primary" />
                        <div className="h-1.5 w-1.5 rounded-full bg-primary/60" />
                        <div className="h-1 w-1 rounded-full bg-primary/40" />
                    </div>
                </div>

                {/* Bottom badge */}
                <div className="auth-fade-up-6 absolute bottom-7 left-1/2 -translate-x-1/2 rounded-full border border-primary/20 bg-background/50 backdrop-blur-sm px-5 py-1.5 text-[10px] font-lato tracking-[0.22em] uppercase text-primary">
                    Since 2014 · East Java, Indonesia
                </div>
            </div>

            {/* ══════════════════════════════════════
                RIGHT PANEL — store bg + form card
            ══════════════════════════════════════ */}
            <div 
                className="relative flex flex-1 flex-col items-center justify-center overflow-hidden p-6 md:p-10"
                style={{
                    backgroundImage: "url('/assets/images/store.png')",
                    backgroundSize: 'cover',
                    backgroundPosition: 'center top',
                }}
            >
                {/* Layer 1 — soft vignette edges */}
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(120,60,30,0.45)_100%)] dark:bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(0,0,0,0.7)_100%)]" />

                {/* Layer 2 — very subtle tint and dark mode overlay */}
                <div className="absolute inset-0 bg-primary/5 dark:bg-background/80" />

                {/* Layer 3 — top-left light leak */}
                <div className="auth-orb absolute -top-20 -left-20 h-64 w-64 rounded-full bg-primary/10 blur-3xl" />

                {/* Layer 4 — bottom-right warm leak */}
                <div className="auth-orb absolute -right-16 -bottom-16 h-52 w-52 rounded-full bg-primary/10 blur-3xl [animation-delay:2s]" />

                {/* Mobile-only floral accent */}
                <div className="auth-breathe absolute top-0 right-0 h-36 w-36 bg-gradient-to-bl from-primary/10 to-transparent blur-2xl opacity-50 lg:hidden" />

                {/* ── PREMIUM FORM CARD ── */}
                <div className="auth-fade-up-1 relative z-10 w-full max-w-sm">
                    <Card className="border-border/50 bg-card backdrop-blur-xl shadow-xl">
                        {/* Card inner glow */}
                        <div className="pointer-events-none absolute top-0 right-0 left-0 h-32 bg-gradient-to-b from-primary/10 to-transparent opacity-50 dark:opacity-20 rounded-t-xl" />

                        <CardHeader className="relative px-8 pt-8 pb-3 space-y-8">
                            {/* Logo */}
                            <div className="auth-fade-up-2 flex flex-col items-center gap-3">
                                <Link href={home()} className="group flex flex-col items-center gap-2">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/15 transition-transform hover:scale-105 hover:bg-primary/20">
                                        <AppLogoIcon className="size-7 text-primary" />
                                    </div>
                                    <span className="sr-only">Home</span>
                                </Link>

                                <div className="space-y-1 text-center">
                                    <CardTitle className="text-2xl md:text-[1.6rem] font-serif font-semibold leading-tight tracking-tight text-foreground">
                                        {title}
                                    </CardTitle>
                                    <CardDescription className="text-[13px] leading-relaxed text-muted-foreground font-lato">
                                        {description}
                                    </CardDescription>
                                </div>

                                {/* Divider */}
                                <div className="mt-1 flex w-full items-center gap-3">
                                    <div className="h-px flex-1 bg-gradient-to-r from-transparent to-primary/30" />
                                    <div className="h-1.5 w-1.5 rounded-full bg-primary/50" />
                                    <div className="h-px flex-1 bg-gradient-to-l from-transparent to-primary/30" />
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent className="auth-fade-up-3 relative px-8 pb-8">
                            {children}

                            {/* Footer */}
                            <p className="auth-fade-up-4 mt-7 text-center text-[11px] font-lato leading-[1.7] text-muted-foreground opacity-80">
                                &copy; {new Date().getFullYear()} Zakiah Hijab &amp; Secaca. <br />
                                All rights reserved.
                            </p>
                        </CardContent>

                        {/* Bottom shimmer line */}
                        <div className="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/30 to-transparent opacity-50" />
                    </Card>
                </div>
            </div>
        </div>
    );
}
