import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

/* ─── Particle config ─── */
const PARTICLES = [
    { left: '8%', delay: '0s', dur: '14s', size: 4, type: 'a' },
    { left: '18%', delay: '2s', dur: '18s', size: 3, type: 'b' },
    { left: '30%', delay: '4.5s', dur: '13s', size: 5, type: 'a' },
    { left: '44%', delay: '1.2s', dur: '16s', size: 3, type: 'c' },
    { left: '58%', delay: '6s', dur: '12s', size: 4, type: 'b' },
    { left: '70%', delay: '3.2s', dur: '15s', size: 3, type: 'a' },
    { left: '82%', delay: '8s', dur: '17s', size: 5, type: 'c' },
    { left: '91%', delay: '5s', dur: '11s', size: 3, type: 'b' },
];

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh w-full overflow-hidden">
            {/* ══════════════════════════════════════
                LEFT PANEL — brand illustration
            ══════════════════════════════════════ */}
            <div
                className="auth-gradient-bg relative hidden flex-col items-center justify-center overflow-hidden lg:flex lg:w-1/2"
                style={{
                    background:
                        'linear-gradient(145deg, #fdf4f0, #fce8d5, #f9e0d6, #f2dde8, #fce8d5, #fdf4f0)',
                    backgroundSize: '400% 400%',
                }}
            >
                {/* ── Decorative SVG arabesque / floral ── */}
                <svg
                    className="auth-spin-slow absolute inset-0 h-full w-full"
                    viewBox="0 0 600 800"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style={{ opacity: 0.07 }}
                >
                    {/* Outer lace ring */}
                    <circle
                        cx="300"
                        cy="400"
                        r="260"
                        stroke="#c4866a"
                        strokeWidth="0.8"
                        strokeDasharray="6 10"
                    />
                    <circle
                        cx="300"
                        cy="400"
                        r="230"
                        stroke="#b87058"
                        strokeWidth="0.5"
                    />
                    <circle
                        cx="300"
                        cy="400"
                        r="195"
                        stroke="#c4866a"
                        strokeWidth="0.8"
                        strokeDasharray="3 7"
                    />

                    {/* Corner petal motifs */}
                    {[0, 60, 120, 180, 240, 300].map((angle, i) => {
                        const rad = (angle * Math.PI) / 180;
                        const cx = 300 + 250 * Math.cos(rad);
                        const cy = 400 + 250 * Math.sin(rad);
                        return (
                            <g key={i}>
                                <circle
                                    cx={cx}
                                    cy={cy}
                                    r="18"
                                    stroke="#c4866a"
                                    strokeWidth="0.8"
                                    fill="none"
                                />
                                <circle
                                    cx={cx}
                                    cy={cy}
                                    r="8"
                                    stroke="#b87058"
                                    strokeWidth="0.6"
                                    fill="rgba(196,134,106,0.15)"
                                />
                                <circle cx={cx} cy={cy} r="3" fill="#c4866a" />
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
                            <line
                                key={i}
                                x1={x1}
                                y1={y1}
                                x2={x2}
                                y2={y2}
                                stroke="#c4866a"
                                strokeWidth="0.6"
                                opacity="0.8"
                            />
                        );
                    })}
                    <circle
                        cx="300"
                        cy="400"
                        r="55"
                        stroke="#c4866a"
                        strokeWidth="0.8"
                        fill="none"
                    />

                    {/* Diamond corner accents */}
                    {[
                        [30, 60],
                        [570, 60],
                        [30, 740],
                        [570, 740],
                    ].map(([x, y], i) => (
                        <g key={i} transform={`translate(${x},${y})`}>
                            <rect
                                x="-12"
                                y="-12"
                                width="24"
                                height="24"
                                stroke="#c4866a"
                                strokeWidth="0.7"
                                fill="none"
                                transform="rotate(45)"
                            />
                            <rect
                                x="-6"
                                y="-6"
                                width="12"
                                height="12"
                                stroke="#b87058"
                                strokeWidth="0.5"
                                fill="rgba(196,134,106,0.1)"
                                transform="rotate(45)"
                            />
                        </g>
                    ))}

                    {/* Top & bottom ornamental arcs */}
                    <path
                        d="M 100 80 Q 300 20 500 80"
                        stroke="#c4866a"
                        strokeWidth="0.7"
                        fill="none"
                        strokeDasharray="4 8"
                    />
                    <path
                        d="M 100 720 Q 300 780 500 720"
                        stroke="#c4866a"
                        strokeWidth="0.7"
                        fill="none"
                        strokeDasharray="4 8"
                    />
                </svg>

                {/* ── Rings pojok kiri atas ── */}
                <div
                    className="auth-spin absolute -top-24 -left-24 h-96 w-96 rounded-full opacity-20"
                    style={{ border: '2px solid #c4866a' }}
                />
                <div
                    className="auth-spin-rev absolute -top-16 -left-16 h-80 w-80 rounded-full opacity-15"
                    style={{ border: '1px dashed #b87058' }}
                />

                {/* ── Rings pojok kanan bawah ── */}
                <div
                    className="auth-spin absolute -right-20 -bottom-20 h-[26rem] w-[26rem] rounded-full opacity-20"
                    style={{ border: '2px solid #c4866a' }}
                />
                <div
                    className="auth-spin-rev absolute -right-12 -bottom-12 h-80 w-80 rounded-full opacity-10"
                    style={{ border: '1px dashed #b87058' }}
                />

                {/* ── Corner glow orbs ── */}
                <div
                    className="auth-orb absolute -top-16 -left-16 h-56 w-56 rounded-full"
                    style={{
                        background:
                            'radial-gradient(circle, rgba(240,196,168,0.6) 0%, transparent 70%)',
                    }}
                />
                <div
                    className="auth-float-b absolute -right-12 -bottom-12 h-48 w-48 rounded-full"
                    style={{
                        background:
                            'radial-gradient(circle, rgba(238,221,230,0.7) 0%, transparent 70%)',
                        animationDelay: '2s',
                    }}
                />
                <div
                    className="auth-float-c absolute top-1/3 left-6 h-24 w-24 rounded-full"
                    style={{
                        background:
                            'radial-gradient(circle, rgba(196,134,106,0.3) 0%, transparent 70%)',
                        animationDelay: '1s',
                    }}
                />
                <div
                    className="auth-float-a absolute right-8 bottom-1/4 h-20 w-20 rounded-full"
                    style={{
                        background:
                            'radial-gradient(circle, rgba(248,220,200,0.5) 0%, transparent 70%)',
                        animationDelay: '3.5s',
                    }}
                />

                {/* ── Floating particles ── */}
                {PARTICLES.map((p, i) => (
                    <div
                        key={i}
                        className="absolute bottom-0 rounded-full"
                        style={{
                            left: p.left,
                            width: p.size,
                            height: p.size,
                            background:
                                i % 3 === 0
                                    ? '#c4866a'
                                    : i % 3 === 1
                                      ? '#e8b49a'
                                      : '#b87058',
                            animation: `auth-particle-${p.type} ${p.dur} ${p.delay} ease-in-out infinite`,
                        }}
                    />
                ))}

                {/* ── Diagonal texture ── */}
                <div
                    className="absolute inset-0 opacity-[0.035]"
                    style={{
                        backgroundImage:
                            'repeating-linear-gradient(45deg, #c4866a 0px, #c4866a 1px, transparent 1px, transparent 24px)',
                    }}
                />

                {/* ── Brand text ── */}
                <div className="relative z-10 px-10 text-center select-none">
                    <p
                        className="auth-fade-up-1 mb-5 text-[10px] font-bold tracking-[0.35em] uppercase"
                        style={{ color: '#b87058', letterSpacing: '0.35em' }}
                    >
                        Zakiah Hijab &amp; Secaca
                    </p>

                    <h2
                        className="auth-fade-up-2 mb-2 leading-tight"
                        style={{
                            fontFamily: '"Playfair Display", Georgia, serif',
                            fontStyle: 'italic',
                            fontWeight: 600,
                            fontSize: 'clamp(2.4rem, 4vw, 3.2rem)',
                            color: '#6b3320',
                            letterSpacing: '-0.02em',
                            textShadow: '0 2px 20px rgba(196,134,106,0.25)',
                        }}
                    >
                        Favorite
                        <br />
                        Muslim Wear
                    </h2>

                    {/* Animated shimmer line */}
                    <div
                        className="auth-fade-up-3 mx-auto mt-5 mb-5 h-[2px] w-28 overflow-hidden rounded-full"
                        style={{ background: 'rgba(196,134,106,0.2)' }}
                    >
                        <div
                            style={{
                                height: '100%',
                                background:
                                    'linear-gradient(90deg, transparent 0%, #e8b49a 30%, #c4866a 50%, #e8b49a 70%, transparent 100%)',
                                backgroundSize: '300% 100%',
                                animation: 'auth-shimmer 2.8s linear infinite',
                            }}
                        />
                    </div>

                    <p
                        className="auth-fade-up-4 mx-auto max-w-[200px] text-[13px] leading-relaxed"
                        style={{
                            color: '#9e5f48',
                            fontFamily: '"Lato", sans-serif',
                            fontWeight: 300,
                        }}
                    >
                        Solusi anggun hijabmu sejak 2014.
                        <br />
                        Pusat Fashion Viral ✨
                    </p>

                    {/* Animated ornament dots */}
                    <div className="auth-fade-up-5 mt-7 flex items-center justify-center gap-2">
                        {[0, 1, 2, 3, 4].map((i) => (
                            <div
                                key={i}
                                className="rounded-full"
                                style={{
                                    width:
                                        i === 2
                                            ? 9
                                            : i === 1 || i === 3
                                              ? 6
                                              : 4,
                                    height:
                                        i === 2
                                            ? 9
                                            : i === 1 || i === 3
                                              ? 6
                                              : 4,
                                    background:
                                        i === 2
                                            ? '#c4866a'
                                            : i === 1 || i === 3
                                              ? '#d4956e'
                                              : '#e8c4aa',
                                    animation:
                                        i === 2
                                            ? 'auth-pulse 2.5s ease-in-out infinite'
                                            : undefined,
                                }}
                            />
                        ))}
                    </div>
                </div>

                {/* Bottom badge */}
                <div
                    className="auth-fade-up-6 absolute bottom-7 left-1/2 -translate-x-1/2 rounded-full px-5 py-1.5 text-[10px] tracking-[0.22em] whitespace-nowrap uppercase"
                    style={{
                        background: 'rgba(196,134,106,0.12)',
                        border: '1px solid rgba(196,134,106,0.3)',
                        color: '#9e5f48',
                        fontFamily: '"Lato", sans-serif',
                        backdropFilter: 'blur(4px)',
                    }}
                >
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
                {/* Layer 1 — soft rose vignette edges (natural, not top-bottom) */}
                <div
                    className="absolute inset-0"
                    style={{
                        background:
                            'radial-gradient(ellipse at center, transparent 35%, rgba(120,60,30,0.45) 100%)',
                    }}
                />

                {/* Layer 2 — very subtle warm tint */}
                <div
                    className="absolute inset-0"
                    style={{
                        background: 'rgba(196,134,106,0.08)',
                    }}
                />

                {/* Layer 3 — top-left light leak */}
                <div
                    className="absolute -top-20 -left-20 h-64 w-64 rounded-full"
                    style={{
                        background:
                            'radial-gradient(circle, rgba(253,240,230,0.35) 0%, transparent 70%)',
                        animation: 'auth-orb 9s ease-in-out infinite',
                    }}
                />

                {/* Layer 4 — bottom-right warm leak */}
                <div
                    className="absolute -right-16 -bottom-16 h-52 w-52 rounded-full"
                    style={{
                        background:
                            'radial-gradient(circle, rgba(238,200,170,0.3) 0%, transparent 70%)',
                        animation: 'auth-orb 11s ease-in-out 2s infinite',
                    }}
                />

                {/* Spinning corner rings */}
                <div
                    className="auth-spin absolute top-5 right-5 h-14 w-14 rounded-full"
                    style={{
                        border: '1px dashed rgba(255,255,255,0.45)',
                        animationDuration: '16s',
                    }}
                />
                <div
                    className="auth-spin-rev absolute bottom-5 left-5 h-10 w-10 rounded-full"
                    style={{
                        border: '1px solid rgba(255,255,255,0.35)',
                        animationDuration: '20s',
                    }}
                />

                {/* Mobile-only floral accent */}
                <div
                    className="auth-breathe absolute top-0 right-0 h-36 w-36 opacity-20 lg:hidden"
                    style={{
                        backgroundImage:
                            'radial-gradient(circle at 100% 0%, #c4866a 0%, transparent 70%)',
                    }}
                />

                {/* ── PREMIUM FORM CARD ── */}
                <div
                    className="auth-fade-up-1 relative z-10 w-full max-w-[340px] overflow-hidden"
                    style={{
                        borderRadius: '24px',
                        background: 'rgba(255,252,250,0.92)',
                        backdropFilter: 'blur(20px)',
                        WebkitBackdropFilter: 'blur(20px)',
                        boxShadow: `
                            0 0 0 1px rgba(255,255,255,0.7),
                            0 8px 32px rgba(120,60,30,0.2),
                            0 32px 64px rgba(120,60,30,0.12),
                            0 2px 4px rgba(196,134,106,0.15)
                        `,
                    }}
                >
                    {/* Shimmer top border */}
                    <div
                        style={{
                            height: '2px',
                            background:
                                'linear-gradient(90deg, transparent, #e8b49a, #c4866a, #f0c4a8, #c4866a, #e8b49a, transparent)',
                            backgroundSize: '300% 100%',
                            animation: 'auth-shimmer 3s linear infinite',
                        }}
                    />

                    {/* Card inner glow */}
                    <div
                        className="pointer-events-none absolute top-0 right-0 left-0 h-32"
                        style={{
                            background:
                                'linear-gradient(180deg, rgba(252,232,213,0.3) 0%, transparent 100%)',
                        }}
                    />

                    <div className="relative px-8 pt-8 pb-8">
                        {/* Logo */}
                        <div className="auth-fade-up-2 mb-8 flex flex-col items-center gap-3">
                            <Link
                                href={home()}
                                className="group flex flex-col items-center gap-2"
                            >
                                <div
                                    className="flex h-12 w-12 items-center justify-center rounded-2xl"
                                    style={{
                                        background:
                                            'linear-gradient(135deg, #f9ede8, #fce8d5)',
                                    }}
                                >
                                    <AppLogoIcon
                                        className="size-7"
                                        style={{ color: '#b87058' }}
                                    />
                                </div>
                                <span className="sr-only">Home</span>
                            </Link>

                            <div className="space-y-1 text-center">
                                <h1
                                    className="text-[1.6rem] leading-tight font-semibold"
                                    style={{
                                        fontFamily:
                                            '"Playfair Display", Georgia, serif',
                                        fontStyle: 'italic',
                                        color: '#6b3320',
                                        letterSpacing: '-0.02em',
                                        textShadow:
                                            '0 1px 8px rgba(196,134,106,0.2)',
                                    }}
                                >
                                    {title}
                                </h1>
                                <p className="text-[13px] leading-relaxed text-muted-foreground">
                                    {description}
                                </p>
                            </div>

                            {/* Divider */}
                            <div className="mt-1 flex w-full items-center gap-3">
                                <div
                                    className="h-px flex-1"
                                    style={{
                                        background:
                                            'linear-gradient(90deg, transparent, rgba(196,134,106,0.3))',
                                    }}
                                />
                                <div
                                    className="auth-pulse h-1.5 w-1.5 rounded-full"
                                    style={{ background: '#c4866a' }}
                                />
                                <div
                                    className="h-px flex-1"
                                    style={{
                                        background:
                                            'linear-gradient(90deg, rgba(196,134,106,0.3), transparent)',
                                    }}
                                />
                            </div>
                        </div>

                        {/* Form slot */}
                        <div className="auth-fade-up-3">{children}</div>

                        {/* Footer */}
                        <p
                            className="auth-fade-up-4 mt-7 text-center text-[11px] text-muted-foreground"
                            style={{
                                fontFamily: '"Lato", sans-serif',
                                lineHeight: 1.7,
                            }}
                        >
                            &copy; {new Date().getFullYear()} Zakiah Hijab &amp;
                            Secaca.
                            <br />
                            All rights reserved.
                        </p>
                    </div>

                    {/* Bottom shimmer line */}
                    <div
                        style={{
                            height: '1px',
                            background:
                                'linear-gradient(90deg, transparent, rgba(196,134,106,0.4), transparent)',
                        }}
                    />
                </div>
            </div>
        </div>
    );
}
