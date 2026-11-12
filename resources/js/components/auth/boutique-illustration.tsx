/**
 * Ilustrasi vektor halaman login: butik busana muslim di balik gerbang lengkung khas,
 * rak gamis, manekin berhijab, tumpukan hijab, tas belanja, dan lentera. Warnanya perpaduan
 * Zakiah (navy, emas) dan Secaca (teal, coral); animasinya mati saat prefers-reduced-motion.
 */

const dresses = [
    { cx: 540, body: '#efd9bf', shade: '#e0c6a4' },
    { cx: 625, body: '#8fcad3', shade: '#6fb2bd' },
    { cx: 710, body: '#ee8f8a', shade: '#d9716d' },
    { cx: 795, body: '#3b4c68', shade: '#2c3a52' },
] as const;

// Palet perpaduan: navy & emas (Zakiah) + teal, coral, mustard (Secaca).
const hijabStack = ['#f3e2cf', '#ee8f8a', '#8fcad3', '#e9b949', '#3b4c68'];

const sparkles = [
    { x: 120, y: 640, s: 1, d: '0s' },
    { x: 930, y: 420, s: 0.8, d: '1.2s' },
    { x: 600, y: 190, s: 0.7, d: '0.6s' },
    { x: 940, y: 620, s: 1.1, d: '2s' },
    { x: 60, y: 420, s: 0.6, d: '1.6s' },
    { x: 250, y: 330, s: 0.5, d: '2.4s' },
];

function Dress({
    cx,
    body,
    shade,
    delay,
}: {
    cx: number;
    body: string;
    shade: string;
    delay: string;
}) {
    return (
        <g className="bq-sway" style={{ animationDelay: delay }}>
            {/* gantungan */}
            <path
                d={`M${cx} 430 q0 -10 7 -10 q6 0 6 6`}
                fill="none"
                stroke="#b98a52"
                strokeWidth="3"
                strokeLinecap="round"
            />
            <path
                d={`M${cx - 30} 456 L${cx} 438 L${cx + 30} 456`}
                fill="none"
                stroke="#b98a52"
                strokeWidth="4"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            {/* lengan panjang */}
            <path
                d={`M${cx - 30} 458 Q${cx - 52} 500 ${cx - 48} 606 L${cx - 37} 608 Q${cx - 38} 522 ${cx - 26} 484 Z`}
                fill={shade}
            />
            <path
                d={`M${cx + 30} 458 Q${cx + 52} 500 ${cx + 48} 606 L${cx + 37} 608 Q${cx + 38} 522 ${cx + 26} 484 Z`}
                fill={shade}
            />
            {/* badan gamis */}
            <path
                d={`M${cx - 12} 450 Q${cx} 463 ${cx + 12} 450 L${cx + 30} 458 Q${cx + 37} 472 ${cx + 34} 520 L${cx + 28} 560 Q${cx + 50} 680 ${cx + 60} 772 Q${cx} 786 ${cx - 60} 772 Q${cx - 50} 680 ${cx - 28} 560 L${cx - 34} 520 Q${cx - 37} 472 ${cx - 30} 458 Z`}
                fill={body}
            />
            {/* lipit & sabuk */}
            <path
                d={`M${cx} 462 V772 M${cx - 18} 600 Q${cx - 26} 690 ${cx - 32} 770 M${cx + 18} 600 Q${cx + 26} 690 ${cx + 32} 770`}
                fill="none"
                stroke={shade}
                strokeWidth="2"
                opacity="0.7"
            />
            <path
                d={`M${cx - 29} 558 Q${cx} 566 ${cx + 29} 558`}
                fill="none"
                stroke="#e3b873"
                strokeWidth="4"
                strokeLinecap="round"
            />
        </g>
    );
}

function Lantern({ x, y, delay }: { x: number; y: number; delay: string }) {
    return (
        <g className="bq-swing" style={{ animationDelay: delay }}>
            <line
                x1={x}
                y1={-10}
                x2={x}
                y2={y - 34}
                stroke="#f3d9a4"
                strokeWidth="1.5"
                opacity="0.7"
            />
            <circle
                cx={x}
                cy={y}
                r="46"
                fill="url(#bq-glow)"
                className="bq-flicker"
            />
            <path
                d={`M${x - 12} ${y - 26} L${x} ${y - 38} L${x + 12} ${y - 26} Z`}
                fill="#e3b873"
            />
            <path
                d={`M${x - 16} ${y - 26} H${x + 16} L${x + 22} ${y - 6} V${y + 14} L${x + 14} ${y + 28} H${x - 14} L${x - 22} ${y + 14} V${y - 6} Z`}
                fill="#fbe6b8"
                stroke="#e3b873"
                strokeWidth="3"
                strokeLinejoin="round"
            />
            <path
                d={`M${x} ${y - 24} V${y + 26} M${x - 21} ${y + 4} H${x + 21}`}
                stroke="#e3b873"
                strokeWidth="2"
            />
            <path
                d={`M${x - 10} ${y + 28} H${x + 10} L${x + 4} ${y + 38} H${x - 4} Z`}
                fill="#e3b873"
            />
        </g>
    );
}

export function BoutiqueIllustration() {
    return (
        <>
            {/* Latar penuh (boleh terpotong) */}
            <svg
                viewBox="0 0 1000 1000"
                preserveAspectRatio="xMidYMid slice"
                className="absolute inset-0 h-full w-full"
                aria-hidden="true"
            >
                <defs>
                    <linearGradient id="bq-bg" x1="0" y1="0" x2="0.5" y2="1">
                        <stop offset="0" stopColor="#34465f" />
                        <stop offset="1" stopColor="#1f7f8c" />
                    </linearGradient>
                    <linearGradient id="bq-wall" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stopColor="#fff8f0" />
                        <stop offset="1" stopColor="#f3e0cf" />
                    </linearGradient>
                    <linearGradient id="bq-frame" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stopColor="#f4d79c" />
                        <stop offset="0.5" stopColor="#e3b873" />
                        <stop offset="1" stopColor="#c8954f" />
                    </linearGradient>
                    <radialGradient id="bq-glow">
                        <stop
                            offset="0"
                            stopColor="#ffe3a6"
                            stopOpacity="0.75"
                        />
                        <stop offset="1" stopColor="#ffe3a6" stopOpacity="0" />
                    </radialGradient>
                    <radialGradient id="bq-spot" cx="0.5" cy="0.2" r="0.7">
                        <stop
                            offset="0"
                            stopColor="#ffffff"
                            stopOpacity="0.7"
                        />
                        <stop offset="1" stopColor="#ffffff" stopOpacity="0" />
                    </radialGradient>
                    {/* Motif bintang delapan khas geometri Islami */}
                    <pattern
                        id="bq-star"
                        width="84"
                        height="84"
                        patternUnits="userSpaceOnUse"
                    >
                        <g
                            fill="none"
                            stroke="#ffffff"
                            strokeWidth="1.2"
                            transform="translate(42 42)"
                        >
                            <rect x="-16" y="-16" width="32" height="32" />
                            <rect
                                x="-16"
                                y="-16"
                                width="32"
                                height="32"
                                transform="rotate(45)"
                            />
                            <circle r="5" />
                        </g>
                    </pattern>
                    <pattern
                        id="bq-wall-star"
                        width="56"
                        height="56"
                        patternUnits="userSpaceOnUse"
                    >
                        <g
                            fill="none"
                            stroke="#c8954f"
                            strokeWidth="1"
                            transform="translate(28 28)"
                        >
                            <rect x="-10" y="-10" width="20" height="20" />
                            <rect
                                x="-10"
                                y="-10"
                                width="20"
                                height="20"
                                transform="rotate(45)"
                            />
                        </g>
                    </pattern>
                    <clipPath id="bq-arch-inner">
                        <path d="M442 900 V425 Q442 318 532 272 Q606 236 650 182 Q694 236 768 272 Q858 318 858 425 V900 Z" />
                    </clipPath>
                </defs>

                {/* Latar */}
                <rect width="1000" height="1000" fill="url(#bq-bg)" />
                <rect
                    width="1000"
                    height="1000"
                    fill="url(#bq-star)"
                    opacity="0.09"
                />
                <circle
                    cx="650"
                    cy="520"
                    r="420"
                    fill="url(#bq-spot)"
                    opacity="0.35"
                />
                <circle
                    cx="120"
                    cy="160"
                    r="180"
                    fill="#ffffff"
                    opacity="0.06"
                    className="bq-breathe"
                />

                {/* Bulan sabit & bintang */}
                <g opacity="0.9">
                    <path
                        d="M470 168 a46 46 0 1 0 38 72 a38 38 0 1 1 -38 -72 Z"
                        fill="#fbe6b8"
                    />
                </g>
                {sparkles.map((sp, i) => (
                    <path
                        key={i}
                        className="bq-twinkle"
                        style={{ animationDelay: sp.d }}
                        transform={`translate(${sp.x} ${sp.y}) scale(${sp.s})`}
                        d="M0 -12 Q2 -2 12 0 Q2 2 0 12 Q-2 2 -12 0 Q-2 -2 0 -12 Z"
                        fill="#fff4dc"
                    />
                ))}
            </svg>

            {/* Adegan butik: selalu utuh, menempel kanan bawah */}
            <svg
                viewBox="250 80 760 830"
                preserveAspectRatio="xMaxYMax meet"
                overflow="visible"
                className="absolute inset-0 h-full w-full"
                aria-hidden="true"
            >
                {/* Lantai */}
                <rect
                    x="-3000"
                    y="880"
                    width="7000"
                    height="600"
                    fill="#000000"
                    opacity="0.12"
                />
                <ellipse
                    cx="650"
                    cy="905"
                    rx="330"
                    ry="26"
                    fill="#000000"
                    opacity="0.14"
                />

                {/* Gerbang lengkung (bingkai) */}
                <path
                    d="M408 900 V418 Q408 292 512 240 Q596 200 650 132 Q704 200 788 240 Q892 292 892 418 V900 Z"
                    fill="url(#bq-frame)"
                />
                <path
                    d="M420 900 V420 Q420 300 520 250 Q600 210 650 150 Q700 210 780 250 Q880 300 880 420 V900 Z"
                    fill="#fdf3e7"
                />

                {/* Isi butik */}
                <g clipPath="url(#bq-arch-inner)">
                    <rect
                        x="440"
                        y="150"
                        width="420"
                        height="760"
                        fill="url(#bq-wall)"
                    />
                    <rect
                        x="440"
                        y="150"
                        width="420"
                        height="760"
                        fill="url(#bq-wall-star)"
                        opacity="0.18"
                    />
                    {/* papan nama */}
                    <rect
                        x="560"
                        y="310"
                        width="180"
                        height="54"
                        rx="27"
                        fill="#ffffff"
                        opacity="0.9"
                    />
                    <rect
                        x="560"
                        y="310"
                        width="180"
                        height="54"
                        rx="27"
                        fill="none"
                        stroke="#e3b873"
                        strokeWidth="2"
                    />
                    <text
                        x="650"
                        y="345"
                        textAnchor="middle"
                        fontFamily="Poppins, sans-serif"
                        fontSize="22"
                        fontWeight="700"
                        letterSpacing="5"
                        fill="#2f3e56"
                    >
                        BUTIK
                    </text>
                    {/* lantai dalam */}
                    <rect
                        x="440"
                        y="800"
                        width="420"
                        height="110"
                        fill="#ecd3bd"
                    />
                    <path d="M440 800 H860" stroke="#dcbca1" strokeWidth="3" />
                    {/* rak */}
                    <g stroke="#2f3e56" strokeLinecap="round">
                        <line
                            x1="478"
                            y1="430"
                            x2="842"
                            y2="430"
                            strokeWidth="7"
                        />
                        <line
                            x1="486"
                            y1="430"
                            x2="486"
                            y2="800"
                            strokeWidth="6"
                        />
                        <line
                            x1="834"
                            y1="430"
                            x2="834"
                            y2="800"
                            strokeWidth="6"
                        />
                        <line
                            x1="468"
                            y1="800"
                            x2="504"
                            y2="800"
                            strokeWidth="6"
                        />
                        <line
                            x1="816"
                            y1="800"
                            x2="852"
                            y2="800"
                            strokeWidth="6"
                        />
                    </g>
                    {dresses.map((d, i) => (
                        <Dress key={d.cx} {...d} delay={`${i * 0.45}s`} />
                    ))}
                </g>

                {/* Meja + tumpukan hijab */}
                <g>
                    <rect
                        x="742"
                        y="806"
                        width="168"
                        height="14"
                        rx="5"
                        fill="#2f3e56"
                    />
                    <rect
                        x="754"
                        y="820"
                        width="10"
                        height="66"
                        fill="#243146"
                    />
                    <rect
                        x="888"
                        y="820"
                        width="10"
                        height="66"
                        fill="#243146"
                    />
                    {hijabStack.map((c, i) => (
                        <rect
                            key={i}
                            x={760 + (i % 2) * 4}
                            y={790 - i * 15}
                            width="132"
                            height="17"
                            rx="7"
                            fill={c}
                            stroke="#00000014"
                        />
                    ))}
                    <path
                        d="M826 722 q8 -16 0 -30"
                        stroke="#e3b873"
                        strokeWidth="3"
                        fill="none"
                        strokeLinecap="round"
                    />
                </g>

                {/* Manekin berhijab */}
                <g className="bq-float">
                    <ellipse
                        cx="360"
                        cy="888"
                        rx="70"
                        ry="12"
                        fill="#000000"
                        opacity="0.18"
                    />
                    <rect
                        x="356"
                        y="826"
                        width="8"
                        height="58"
                        fill="#2f3e56"
                    />
                    <ellipse cx="360" cy="886" rx="44" ry="8" fill="#2f3e56" />
                    {/* gamis */}
                    <path
                        d="M318 548 Q360 566 402 548 L414 602 Q434 720 446 830 Q360 846 274 830 Q286 720 306 602 Z"
                        fill="#f6efe6"
                    />
                    <path
                        d="M330 610 Q322 720 316 828 M390 610 Q398 720 404 828"
                        stroke="#e5d8c8"
                        strokeWidth="3"
                        fill="none"
                    />
                    <path
                        d="M307 604 Q360 618 413 604"
                        stroke="#e3b873"
                        strokeWidth="5"
                        fill="none"
                        strokeLinecap="round"
                    />
                    {/* hijab */}
                    <path
                        d="M326 470 Q324 420 360 416 Q396 420 394 470 Q398 520 422 560 Q360 600 298 560 Q322 520 326 470 Z"
                        fill="#3b4c68"
                    />
                    <ellipse cx="360" cy="472" rx="18" ry="23" fill="#f1cfb3" />
                    <path
                        d="M338 452 Q360 436 382 452"
                        stroke="#2c3a52"
                        strokeWidth="5"
                        fill="none"
                        strokeLinecap="round"
                    />
                    <path
                        d="M342 498 Q360 516 378 498 Q392 530 410 556"
                        stroke="#2c3a52"
                        strokeWidth="3"
                        fill="none"
                        strokeLinecap="round"
                    />
                    <circle cx="388" cy="500" r="4" fill="#e3b873" />
                </g>

                {/* Tas belanja */}
                <g className="bq-float" style={{ animationDelay: '1s' }}>
                    <path
                        d="M478 820 q22 -40 44 0"
                        stroke="#2f3e56"
                        strokeWidth="4"
                        fill="none"
                    />
                    <rect
                        x="462"
                        y="818"
                        width="76"
                        height="72"
                        rx="8"
                        fill="#2f3e56"
                    />
                    <path
                        d="M500 842 l5 10 11 1 -8 8 2 11 -10 -5 -10 5 2 -11 -8 -8 11 -1 Z"
                        fill="#e3b873"
                    />
                    <path
                        d="M556 838 q18 -32 36 0"
                        stroke="#2f3e56"
                        strokeWidth="4"
                        fill="none"
                    />
                    <rect
                        x="544"
                        y="836"
                        width="60"
                        height="54"
                        rx="8"
                        fill="#8fcad3"
                    />
                    <rect
                        x="544"
                        y="836"
                        width="60"
                        height="10"
                        rx="5"
                        fill="#e5545a"
                    />
                </g>

                {/* Lentera */}
                <Lantern x={928} y={262} delay="0s" />
                <Lantern x={866} y={146} delay="0.8s" />
            </svg>
        </>
    );
}
