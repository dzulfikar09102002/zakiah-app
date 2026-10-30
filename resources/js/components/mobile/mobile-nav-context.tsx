import { router } from '@inertiajs/react';
import {
    createContext,
    useContext,
    useEffect,
    useState,
    type ReactNode,
} from 'react';
import type { MobileSheetKey } from '@/lib/mobile-nav';

type MobileNavContextValue = {
    openSheet: MobileSheetKey | null;
    setOpenSheet: (key: MobileSheetKey | null) => void;
};

const MobileNavContext = createContext<MobileNavContextValue>({
    openSheet: null,
    setOpenSheet: () => {},
});

export function MobileNavProvider({ children }: { children: ReactNode }) {
    const [openSheet, setOpenSheet] = useState<MobileSheetKey | null>(null);

    // Tutup sheet setiap kali pindah halaman.
    useEffect(() => router.on('start', () => setOpenSheet(null)), []);

    return (
        <MobileNavContext.Provider value={{ openSheet, setOpenSheet }}>
            {children}
        </MobileNavContext.Provider>
    );
}

export const useMobileNav = () => useContext(MobileNavContext);
