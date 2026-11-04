/**
 * Bunyi dan getar hasil scan, sama dengan kasir NewZakicaPOS: "tiit" nyaring untuk
 * berhasil, "tuut-tuut" rendah untuk gagal. iPhone tidak mendukung getar, jadi di sana
 * petugas mengandalkan bunyi dan warna bingkai.
 */
let audioContext: AudioContext | null = null;

export function unlockScanAudio(): AudioContext | null {
    try {
        const Context =
            window.AudioContext ??
            (window as unknown as { webkitAudioContext?: typeof AudioContext })
                .webkitAudioContext;

        if (!Context) {
            return null;
        }

        audioContext ??= new Context();

        if (audioContext.state === 'suspended') {
            void audioContext.resume();
        }

        return audioContext;
    } catch {
        return null;
    }
}

function tone(
    context: AudioContext,
    at: number,
    frequency: number,
    duration: number,
    volume: number,
) {
    const oscillator = context.createOscillator();
    const gain = context.createGain();

    oscillator.type = 'square';
    oscillator.frequency.setValueAtTime(frequency, at);
    gain.gain.setValueAtTime(0.0001, at);
    gain.gain.exponentialRampToValueAtTime(volume, at + 0.008);
    gain.gain.setValueAtTime(volume, at + duration - 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, at + duration);
    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.start(at);
    oscillator.stop(at + duration + 0.02);
}

export function scanFeedback(ok: boolean) {
    navigator.vibrate?.(ok ? 60 : [90, 70, 90]);

    const context = unlockScanAudio();

    if (!context) {
        return;
    }

    try {
        const at = context.currentTime + 0.01;

        if (ok) {
            tone(context, at, 2730, 0.14, 0.32);
        } else {
            tone(context, at, 330, 0.18, 0.4);
            tone(context, at + 0.27, 330, 0.18, 0.4);
        }
    } catch {
        // Bunyi hanya pelengkap, scan tetap jalan.
    }
}
