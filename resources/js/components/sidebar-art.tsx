import { useId } from 'react';

/**
 * Motif dekoratif di belakang isi sidebar (meniru SidebarArt ERP TWO-M, bertema butik muslim):
 * - atas: lipatan kain hijab yang menjuntai, motif bintang delapan, bulan sabit & lentera;
 * - bawah: deretan gerbang lengkung, gelombang kain, dan motif bintang;
 * - tengah nyaris bening agar teks menu tetap terbaca.
 * Murni SVG/CSS dan mengikuti tema entity lewat var(--primary) & var(--gold), termasuk mode gelap.
 */
export function SidebarArt() {
    const id = useId().replace(/:/g, '');

    return (
        <div
            aria-hidden="true"
            className="pointer-events-none absolute inset-0 overflow-hidden"
        >
            {/* Wash setinggi sidebar */}
            <div
                className="absolute inset-0"
                style={{
                    background: [
                        'radial-gradient(120% 38% at 100% 0%, color-mix(in oklab, var(--primary) 13%, transparent), transparent 70%)',
                        'radial-gradient(110% 34% at 0% 100%, color-mix(in oklab, var(--primary) 12%, transparent), transparent 70%)',
                        'linear-gradient(to bottom, color-mix(in oklab, var(--gold) 9%, transparent) 0%, transparent 32%, transparent 62%, color-mix(in oklab, var(--gold) 8%, transparent) 100%)',
                    ].join(','),
                }}
            />

            {/* Pita atas */}
            <div className="absolute inset-x-0 top-0 h-56 [mask-image:linear-gradient(to_bottom,black_45%,transparent)]">
                <svg
                    className="h-full w-full"
                    viewBox="0 0 256 224"
                    preserveAspectRatio="xMidYMin slice"
                >
                    <defs>
                        <pattern
                            id={`${id}-star`}
                            width="22"
                            height="22"
                            patternUnits="userSpaceOnUse"
                        >
                            <g
                                transform="translate(11 11)"
                                fill="none"
                                stroke="var(--primary)"
                                strokeWidth="0.7"
                            >
                                <rect x="-4" y="-4" width="8" height="8" />
                                <rect
                                    x="-4"
                                    y="-4"
                                    width="8"
                                    height="8"
                                    transform="rotate(45)"
                                />
                            </g>
                        </pattern>
                        <radialGradient
                            id={`${id}-fade-top`}
                            cx="88%"
                            cy="0%"
                            r="85%"
                        >
                            <stop offset="0" stopColor="white" />
                            <stop
                                offset="1"
                                stopColor="white"
                                stopOpacity="0"
                            />
                        </radialGradient>
                        <mask id={`${id}-mask-top`}>
                            <rect
                                width="256"
                                height="224"
                                fill={`url(#${id}-fade-top)`}
                            />
                        </mask>
                    </defs>

                    {/* Lipatan kain hijab */}
                    <path
                        d="M0 84 C 40 64 86 118 140 92 S 222 44 256 66 L256 0 L0 0 Z"
                        fill="var(--primary)"
                        opacity="0.09"
                    />
                    <path
                        d="M0 120 C 56 92 104 150 168 116 S 236 92 256 102 L256 0 L0 0 Z"
                        fill="var(--primary)"
                        opacity="0.05"
                    />
                    <path
                        d="M0 84 C 40 64 86 118 140 92 S 222 44 256 66"
                        fill="none"
                        stroke="var(--gold)"
                        strokeWidth="1.2"
                        opacity="0.55"
                    />
                    <rect
                        width="256"
                        height="224"
                        fill={`url(#${id}-star)`}
                        opacity="0.13"
                        mask={`url(#${id}-mask-top)`}
                    />

                    {/* Bulan sabit */}
                    <path
                        d="M184 14 a15 15 0 1 0 13 24 a12 12 0 1 1 -13 -24 Z"
                        fill="var(--gold)"
                        opacity="0.7"
                    />
                    {/* Lentera kecil */}
                    <g opacity="0.6">
                        <line
                            x1="220"
                            y1="0"
                            x2="220"
                            y2="52"
                            stroke="var(--gold)"
                            strokeWidth="0.8"
                        />
                        <path
                            d="M215 52 L220 47 L225 52 Z"
                            fill="var(--gold)"
                        />
                        <path
                            d="M213 52 H227 L229 60 V68 L225 74 H215 L211 68 V60 Z"
                            fill="color-mix(in oklab, var(--gold) 30%, transparent)"
                            stroke="var(--gold)"
                            strokeWidth="1"
                        />
                        <path
                            d="M220 52 V74 M211 63 H229"
                            stroke="var(--gold)"
                            strokeWidth="0.7"
                        />
                    </g>
                    {/* Bintang kecil */}
                    <g fill="var(--gold)" opacity="0.65">
                        <path d="M190 52 q1 -4 5 -5 q-4 -1 -5 -5 q-1 4 -5 5 q4 1 5 5 Z" />
                        <path d="M22 108 q0.8 -3 4 -4 q-3.2 -1 -4 -4 q-0.8 3 -4 4 q3.2 1 4 4 Z" />
                    </g>
                </svg>
            </div>

            {/* Pita bawah: deretan gerbang lengkung */}
            <div className="absolute inset-x-0 bottom-0 h-72 [mask-image:linear-gradient(to_top,black_45%,transparent)]">
                <svg
                    className="h-full w-full"
                    viewBox="0 0 256 288"
                    preserveAspectRatio="xMidYMax slice"
                >
                    <defs>
                        <radialGradient
                            id={`${id}-fade-bottom`}
                            cx="10%"
                            cy="100%"
                            r="95%"
                        >
                            <stop offset="0" stopColor="white" />
                            <stop
                                offset="1"
                                stopColor="white"
                                stopOpacity="0"
                            />
                        </radialGradient>
                        <mask id={`${id}-mask-bottom`}>
                            <rect
                                width="256"
                                height="288"
                                fill={`url(#${id}-fade-bottom)`}
                            />
                        </mask>
                    </defs>

                    {/* Gelombang kain */}
                    <path
                        d="M0 168 C 54 190 102 140 160 166 S 230 206 256 190 L256 288 L0 288 Z"
                        fill="var(--primary)"
                        opacity="0.06"
                    />
                    <rect
                        width="256"
                        height="288"
                        fill={`url(#${id}-star)`}
                        opacity="0.12"
                        mask={`url(#${id}-mask-bottom)`}
                    />

                    {/* Gerbang lengkung khas */}
                    <g fill="var(--primary)">
                        {[
                            { x: 18, w: 46, h: 76, o: 0.08 },
                            { x: 74, w: 56, h: 96, o: 0.11 },
                            { x: 140, w: 46, h: 76, o: 0.08 },
                            { x: 196, w: 40, h: 62, o: 0.06 },
                        ].map(({ x, w, h, o }) => {
                            const top = 288 - h;
                            const mid = x + w / 2;

                            return (
                                <path
                                    key={x}
                                    opacity={o}
                                    d={`M${x} 288 V${top + w * 0.55} Q${x} ${top + w * 0.2} ${mid - w * 0.25} ${top + w * 0.12} Q${mid - 4} ${top + 6} ${mid} ${top} Q${mid + 4} ${top + 6} ${mid + w * 0.25} ${top + w * 0.12} Q${x + w} ${top + w * 0.2} ${x + w} ${top + w * 0.55} V288 Z`}
                                />
                            );
                        })}
                    </g>
                    <path
                        d="M0 236 C 70 254 130 220 196 242 S 240 252 256 248"
                        fill="none"
                        stroke="var(--gold)"
                        strokeWidth="1.2"
                        opacity="0.5"
                    />
                    {/* Tanda sudut */}
                    <g
                        stroke="var(--gold)"
                        strokeWidth="1.1"
                        opacity="0.45"
                        fill="none"
                    >
                        <path d="M226 196 l6 -6 l6 6 l-6 6 Z" />
                        <path d="M226 196 h12 M232 190 v12" />
                    </g>
                </svg>
            </div>
        </div>
    );
}
