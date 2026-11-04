/**
 * Pemindai barcode dari kamera. Chrome Android memakai BarcodeDetector bawaan yang
 * ringan; browser lain (iPhone, komputer) memakai ZXing yang baru dimuat saat dibutuhkan.
 */
export type ScannerHandle = {
    stop: () => void;
    /** Null bila kamera tidak punya senter (mis. iPhone di browser, webcam). */
    setTorch: ((on: boolean) => Promise<boolean>) | null;
};

type NativeDetector = {
    detect: (source: HTMLVideoElement) => Promise<{ rawValue?: string }[]>;
};

type NativeDetectorClass = {
    new (options?: { formats: string[] }): NativeDetector;
    getSupportedFormats: () => Promise<string[]>;
};

const NATIVE_FORMATS = [
    'ean_13',
    'ean_8',
    'upc_a',
    'upc_e',
    'code_128',
    'code_39',
    'code_93',
    'itf',
    'codabar',
    'qr_code',
];

const NATIVE_INTERVAL_MS = 120;
const ZXING_INTERVAL_MS = 150;

/** Pesan kesalahan kamera yang bisa langsung ditampilkan ke petugas. */
export function cameraErrorMessage(error: unknown): string {
    if (!navigator.mediaDevices?.getUserMedia) {
        return window.isSecureContext
            ? 'Browser ini tidak mendukung kamera. Gunakan alat scan atau ketik barcode.'
            : 'Kamera hanya bisa dipakai lewat HTTPS.';
    }

    const name = error instanceof Error ? error.name : '';

    if (name === 'NotAllowedError' || name === 'SecurityError') {
        return 'Izin kamera ditolak. Aktifkan akses kamera untuk situs ini di pengaturan browser.';
    }

    if (name === 'NotFoundError' || name === 'OverconstrainedError') {
        return 'Kamera tidak ditemukan pada perangkat ini.';
    }

    if (name === 'NotReadableError') {
        return 'Kamera sedang dipakai aplikasi lain. Tutup dulu aplikasi tersebut.';
    }

    return 'Kamera tidak bisa dibuka. Gunakan alat scan atau ketik barcode.';
}

async function nativeDetector(): Promise<NativeDetector | null> {
    const Detector = (
        window as unknown as { BarcodeDetector?: NativeDetectorClass }
    ).BarcodeDetector;

    if (!Detector) {
        return null;
    }

    try {
        const supported = await Detector.getSupportedFormats();
        const formats = NATIVE_FORMATS.filter((format) =>
            supported.includes(format),
        );

        // Sebagian perangkat punya API-nya, tapi tidak bisa membaca barcode produk.
        return formats.includes('ean_13') ? new Detector({ formats }) : null;
    } catch {
        return null;
    }
}

function startNative(
    detector: NativeDetector,
    video: HTMLVideoElement,
    onCode: (code: string) => void,
): () => void {
    let stopped = false;

    const tick = async () => {
        if (stopped) {
            return;
        }

        if (video.readyState >= 2) {
            try {
                const codes = await detector.detect(video);
                const value = codes.find((code) => code.rawValue)?.rawValue;

                if (value && !stopped) {
                    onCode(value.trim());
                }
            } catch {
                // Frame gagal dibaca, coba frame berikutnya.
            }
        }

        if (!stopped) {
            setTimeout(tick, NATIVE_INTERVAL_MS);
        }
    };

    void tick();

    return () => {
        stopped = true;
    };
}

async function startZxing(
    stream: MediaStream,
    video: HTMLVideoElement,
    onCode: (code: string) => void,
): Promise<() => void> {
    const [{ BrowserMultiFormatReader }, { BarcodeFormat, DecodeHintType }] =
        await Promise.all([import('@zxing/browser'), import('@zxing/library')]);

    const hints = new Map<unknown, unknown>([
        [
            DecodeHintType.POSSIBLE_FORMATS,
            [
                BarcodeFormat.EAN_13,
                BarcodeFormat.EAN_8,
                BarcodeFormat.UPC_A,
                BarcodeFormat.UPC_E,
                BarcodeFormat.CODE_128,
                BarcodeFormat.CODE_39,
                BarcodeFormat.CODE_93,
                BarcodeFormat.ITF,
                BarcodeFormat.CODABAR,
                BarcodeFormat.QR_CODE,
            ],
        ],
        [DecodeHintType.TRY_HARDER, true],
    ]);

    const reader = new BrowserMultiFormatReader(hints as never, {
        delayBetweenScanAttempts: ZXING_INTERVAL_MS,
    });

    const controls = await reader.decodeFromStream(stream, video, (result) => {
        const value = result?.getText();

        if (value) {
            onCode(value.trim());
        }
    });

    return () => controls.stop();
}

export async function startBarcodeScanner(
    video: HTMLVideoElement,
    onCode: (code: string) => void,
): Promise<ScannerHandle> {
    if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error('Kamera tidak tersedia');
    }

    const stream = await navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1280 },
            height: { ideal: 720 },
        },
        audio: false,
    });

    let stopDecoder: () => void = () => {};

    const stopStream = () =>
        stream.getTracks().forEach((track) => track.stop());

    try {
        const detector = await nativeDetector();

        if (detector) {
            video.srcObject = stream;
            await video.play().catch(() => {});
            stopDecoder = startNative(detector, video, onCode);
        } else {
            stopDecoder = await startZxing(stream, video, onCode);
        }
    } catch (error) {
        stopStream();
        throw error;
    }

    const track = stream.getVideoTracks()[0];
    const capabilities = (track?.getCapabilities?.() ?? {}) as {
        torch?: boolean;
    };

    return {
        stop: () => {
            stopDecoder();
            stopStream();
            video.srcObject = null;
        },
        setTorch: capabilities.torch
            ? async (on) => {
                  try {
                      await track.applyConstraints({
                          advanced: [{ torch: on } as MediaTrackConstraintSet],
                      });

                      return on;
                  } catch {
                      return false;
                  }
              }
            : null,
    };
}
