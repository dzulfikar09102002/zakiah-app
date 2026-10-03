import { Printer } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    url: string;
    disabled?: boolean;
};

/**
 * Membuka PDF laporan di tab baru dengan filter yang sama seperti tabel
 * (query string halaman saat ini ikut diteruskan).
 */
export default function PrintPdfButton({ url, disabled }: Props) {
    const handleClick = () => {
        window.open(`${url}${window.location.search}`, '_blank');
    };

    return (
        <Button
            type="button"
            variant="outline"
            disabled={disabled}
            onClick={handleClick}
        >
            <Printer /> Cetak PDF
        </Button>
    );
}
