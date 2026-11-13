import CountUp from 'react-countup';
import { toRupiah } from '@/lib/utils';
export function AnimatedValue({
    value,
    isCurrency = true,
}: {
    value: number;
    isCurrency?: boolean;
}) {
    return (
        <CountUp
            end={value}
            duration={1}
            separator="."
            formattingFn={(val) =>
                isCurrency
                    ? toRupiah(Math.round(val))
                    : Math.round(val).toLocaleString('id-ID')
            }
        />
    );
}