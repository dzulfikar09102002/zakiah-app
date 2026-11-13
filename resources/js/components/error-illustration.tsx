/**
 * Ilustrasi beranimasi untuk halaman error. Satu adegan per kelompok status,
 * digambar dengan SVG supaya ringan dan tajam di semua layar. Semua animasi
 * mati sendiri bila perangkat meminta gerakan dikurangi (lihat app.css).
 */
export type ErrorScene =
    | 'search'
    | 'lock'
    | 'document'
    | 'server'
    | 'hourglass'
    | 'gears';

// Warna dari .glass-theme (app.css), jadi ikut tema entity.
const ink = 'var(--g-primary, #2f3e56)';
const accent = 'var(--g-primary-2, #1f7f8c)';
const soft = 'var(--g-soft, #e3c99f)';
const paper = 'var(--g-paper, #fffdf9)';

export function ErrorIllustration({ scene }: { scene: ErrorScene }) {
    return (
        <div className="err-bob relative mx-auto size-40" aria-hidden="true">
            {/* Gelombang di belakang ilustrasi. */}
            <svg viewBox="0 0 160 160" className="absolute inset-0 size-full">
                <circle cx="80" cy="80" r="60" fill={soft} opacity="0.25" />
                <circle
                    className="err-ring"
                    cx="80"
                    cy="80"
                    r="62"
                    fill="none"
                    stroke={accent}
                    strokeWidth="2"
                />
                <circle
                    className="err-ring"
                    style={{ animationDelay: '1.3s' }}
                    cx="80"
                    cy="80"
                    r="62"
                    fill="none"
                    stroke={accent}
                    strokeWidth="2"
                />
            </svg>

            <svg viewBox="0 0 160 160" className="absolute inset-0 size-full">
                {scene === 'search' && <SearchScene />}
                {scene === 'lock' && <LockScene />}
                {scene === 'document' && <DocumentScene />}
                {scene === 'server' && <ServerScene />}
                {scene === 'hourglass' && <HourglassScene />}
                {scene === 'gears' && <GearsScene />}
            </svg>
        </div>
    );
}

/** 404: kaca pembesar menyisir kotak kosong. */
function SearchScene() {
    return (
        <>
            <rect
                className="err-dash"
                x="40"
                y="48"
                width="80"
                height="64"
                rx="10"
                fill={paper}
                stroke={accent}
                strokeWidth="3"
                strokeDasharray="8 4"
            />
            <text
                x="80"
                y="90"
                textAnchor="middle"
                fontSize="28"
                fontWeight="800"
                fill={soft}
            >
                ?
            </text>
            <g className="err-search">
                <circle
                    cx="88"
                    cy="74"
                    r="18"
                    fill="white"
                    fillOpacity="0.55"
                    stroke={ink}
                    strokeWidth="5"
                />
                <line
                    x1="101"
                    y1="87"
                    x2="118"
                    y2="104"
                    stroke={ink}
                    strokeWidth="7"
                    strokeLinecap="round"
                />
            </g>
        </>
    );
}

/** 401 / 403: gembok yang mencoba terbuka lalu tertutup lagi. */
function LockScene() {
    return (
        <>
            <path
                className="err-shackle"
                d="M60 74 V60 a20 20 0 0 1 40 0 V74"
                fill="none"
                stroke={ink}
                strokeWidth="8"
                strokeLinecap="round"
            />
            <rect x="48" y="72" width="64" height="50" rx="10" fill={accent} />
            <rect
                x="48"
                y="72"
                width="64"
                height="10"
                rx="5"
                fill="white"
                opacity="0.2"
            />
            <circle cx="80" cy="94" r="7" fill={paper} />
            <rect x="77" y="96" width="6" height="14" rx="3" fill={paper} />
        </>
    );
}

/** 400 / 405 / 413 / 422: dokumen yang ditolak, bergoyang. */
function DocumentScene() {
    return (
        <g className="err-shake">
            <path
                d="M52 36 h40 l20 20 v68 a6 6 0 0 1 -6 6 h-54 a6 6 0 0 1 -6 -6 v-82 a6 6 0 0 1 6 -6 z"
                fill={paper}
                stroke={ink}
                strokeWidth="4"
                strokeLinejoin="round"
            />
            <path d="M92 36 v20 h20" fill="none" stroke={ink} strokeWidth="4" />
            <line
                x1="60"
                y1="66"
                x2="92"
                y2="66"
                stroke={soft}
                strokeWidth="4"
                strokeLinecap="round"
            />
            <line
                x1="60"
                y1="78"
                x2="98"
                y2="78"
                stroke={soft}
                strokeWidth="4"
                strokeLinecap="round"
            />
            <circle cx="100" cy="112" r="16" fill={accent} />
            <path
                d="M93 105 l14 14 M107 105 l-14 14"
                stroke="white"
                strokeWidth="4"
                strokeLinecap="round"
            />
        </g>
    );
}

/** 500 / 502 / 504: rak server dengan lampu berkedip dan percikan. */
function ServerScene() {
    return (
        <>
            {[44, 74, 104].map((y, index) => (
                <g key={y}>
                    <rect
                        x="42"
                        y={y}
                        width="76"
                        height="24"
                        rx="6"
                        fill={index === 1 ? accent : paper}
                        stroke={ink}
                        strokeWidth="3.5"
                    />
                    <line
                        x1="52"
                        y1={y + 12}
                        x2="80"
                        y2={y + 12}
                        stroke={index === 1 ? paper : soft}
                        strokeWidth="3.5"
                        strokeLinecap="round"
                    />
                    <circle
                        className={index === 1 ? 'err-blink' : 'err-blink-slow'}
                        style={{ animationDelay: `${index * 0.35}s` }}
                        cx="104"
                        cy={y + 12}
                        r="4"
                        fill={index === 1 ? paper : ink}
                    />
                </g>
            ))}
            <path
                className="err-spark"
                d="M122 50 l-8 14 h9 l-7 14"
                fill="none"
                stroke="var(--g-accent, #c9a066)"
                strokeWidth="4"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </>
    );
}

/** 408 / 419 / 429: jam pasir yang terus dibalik. */
function HourglassScene() {
    return (
        <g className="err-flip">
            <line
                x1="54"
                y1="40"
                x2="106"
                y2="40"
                stroke={ink}
                strokeWidth="6"
                strokeLinecap="round"
            />
            <line
                x1="54"
                y1="120"
                x2="106"
                y2="120"
                stroke={ink}
                strokeWidth="6"
                strokeLinecap="round"
            />
            <path
                d="M60 42 C60 68 80 72 80 80 C80 88 60 92 60 118 H100 C100 92 80 88 80 80 C80 72 100 68 100 42 Z"
                fill={paper}
                stroke={ink}
                strokeWidth="4"
                strokeLinejoin="round"
            />
            <path
                className="err-sand"
                d="M66 48 H94 C92 62 82 68 80 74 C78 68 68 62 66 48 Z"
                fill={accent}
            />
            <path
                d="M66 116 C68 104 76 98 80 96 C84 98 92 104 94 116 Z"
                fill={soft}
            />
        </g>
    );
}

/** 503: roda gigi yang berputar, tanda sedang perbaikan. */
function GearsScene() {
    return (
        <>
            <g className="err-gear">
                <Gear cx={68} cy={72} r={24} teeth={8} fill={accent} />
                <circle cx="68" cy="72" r="9" fill={paper} />
            </g>
            <g className="err-gear-rev">
                <Gear cx={104} cy={104} r={16} teeth={6} fill={ink} />
                <circle cx="104" cy="104" r="6" fill={paper} />
            </g>
        </>
    );
}

function Gear({
    cx,
    cy,
    r,
    teeth,
    fill,
}: {
    cx: number;
    cy: number;
    r: number;
    teeth: number;
    fill: string;
}) {
    return (
        <>
            <circle cx={cx} cy={cy} r={r} fill={fill} />
            {Array.from({ length: teeth }, (_, index) => (
                <rect
                    key={index}
                    x={cx - 5}
                    y={cy - r - 8}
                    width="10"
                    height="14"
                    rx="3"
                    fill={fill}
                    transform={`rotate(${(360 / teeth) * index} ${cx} ${cy})`}
                />
            ))}
        </>
    );
}
