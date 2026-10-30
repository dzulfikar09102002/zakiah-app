import * as SheetPrimitive from '@radix-ui/react-dialog';
import { useRef, useState, type ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: ReactNode;
    description?: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    className?: string;
};

const CLOSE_THRESHOLD = 90;

/**
 * Panel yang muncul dari bawah layar (pola aplikasi m-banking / e-wallet).
 * Bisa ditutup dengan menyeret pegangan ke bawah, tap latar, atau tombol back.
 */
export function BottomSheet({
    open,
    onOpenChange,
    title,
    description,
    children,
    footer,
    className,
}: Props) {
    const [dragY, setDragY] = useState(0);
    const startY = useRef<number | null>(null);

    const onPointerDown = (event: React.PointerEvent) => {
        startY.current = event.clientY;
        (event.target as HTMLElement).setPointerCapture?.(event.pointerId);
    };

    const onPointerMove = (event: React.PointerEvent) => {
        if (startY.current === null) {
            return;
        }

        setDragY(Math.max(0, event.clientY - startY.current));
    };

    const onPointerEnd = () => {
        if (startY.current === null) {
            return;
        }

        startY.current = null;

        if (dragY > CLOSE_THRESHOLD) {
            onOpenChange(false);
        }

        setDragY(0);
    };

    return (
        <SheetPrimitive.Root open={open} onOpenChange={onOpenChange}>
            <SheetPrimitive.Portal>
                <SheetPrimitive.Overlay className="fixed inset-0 z-50 bg-black/45 backdrop-blur-[2px] data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
                <SheetPrimitive.Content
                    style={{
                        transform: dragY ? `translateY(${dragY}px)` : undefined,
                        transition: dragY ? 'none' : undefined,
                    }}
                    className={cn(
                        'fixed inset-x-0 bottom-0 z-50 flex max-h-[88dvh] flex-col rounded-t-[28px] bg-background shadow-[0_-12px_40px_-12px_rgb(0_0_0/0.25)] outline-none',
                        'duration-300 data-[state=closed]:animate-out data-[state=closed]:slide-out-to-bottom data-[state=open]:animate-in data-[state=open]:slide-in-from-bottom',
                        className,
                    )}
                >
                    <div
                        className="shrink-0 cursor-grab touch-none px-5 pt-3 pb-2 select-none active:cursor-grabbing"
                        onPointerDown={onPointerDown}
                        onPointerMove={onPointerMove}
                        onPointerUp={onPointerEnd}
                        onPointerCancel={onPointerEnd}
                    >
                        <div className="mx-auto mb-3 h-1.5 w-10 rounded-full bg-muted-foreground/25" />
                        <SheetPrimitive.Title className="text-lg font-semibold tracking-tight">
                            {title}
                        </SheetPrimitive.Title>
                        {description ? (
                            <SheetPrimitive.Description className="text-sm text-muted-foreground">
                                {description}
                            </SheetPrimitive.Description>
                        ) : (
                            <SheetPrimitive.Description className="sr-only">
                                {title}
                            </SheetPrimitive.Description>
                        )}
                    </div>

                    <div
                        className={cn(
                            'min-h-0 flex-1 overflow-y-auto overscroll-contain px-5',
                            footer
                                ? 'pb-4'
                                : 'pb-[calc(env(safe-area-inset-bottom)+20px)]',
                        )}
                    >
                        {children}
                    </div>

                    {footer && (
                        <div className="shrink-0 border-t px-5 pt-3 pb-[calc(env(safe-area-inset-bottom)+12px)]">
                            {footer}
                        </div>
                    )}
                </SheetPrimitive.Content>
            </SheetPrimitive.Portal>
        </SheetPrimitive.Root>
    );
}
