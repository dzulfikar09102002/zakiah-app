import { Printer } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    url: string;
    disabled?: boolean;
    iconOnly?: boolean;
};

/**
 * Buka struk (NewZakicaPOS) di tab baru; halaman struk langsung memunculkan dialog cetak.
 * URL mengarah ke route Laravel yang membuat tautan bertanda tangan lalu redirect.
 */
export default function PrintStrukButton({ url, disabled, iconOnly }: Props) {
    const content = iconOnly ? (
        <Printer />
    ) : (
        <>
            <Printer /> Cetak Struk
        </>
    );
    const size = iconOnly ? 'icon' : 'default';
    const title = disabled
        ? 'Struk tersedia setelah tutup shift'
        : 'Cetak struk';

    if (disabled) {
        return (
            <Button variant="outline" size={size} disabled title={title}>
                {content}
            </Button>
        );
    }

    return (
        <Button variant="outline" size={size} asChild>
            <a
                href={url}
                target="_blank"
                rel="noopener noreferrer"
                title={title}
            >
                {content}
            </a>
        </Button>
    );
}
