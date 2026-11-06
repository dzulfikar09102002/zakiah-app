import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { navIconNames, navIcons } from '@/lib/nav-icons';

const NONE = 'none';

type Props = {
    id?: string;
    value: string | null;
    disabled?: boolean;
    onChange: (value: string | null) => void;
};

/** Pilihan ikon menu dari daftar ikon yang didukung sidebar. */
export function IconSelect({ id, value, disabled, onChange }: Props) {
    return (
        <Select
            value={value ?? NONE}
            onValueChange={(next) => onChange(next === NONE ? null : next)}
            disabled={disabled}
        >
            <SelectTrigger id={id} className="h-11 md:h-9">
                <SelectValue />
            </SelectTrigger>
            <SelectContent className="max-h-72">
                <SelectItem value={NONE}>Tanpa ikon</SelectItem>
                {navIconNames.map((name) => {
                    const Icon = navIcons[name];

                    return (
                        <SelectItem key={name} value={name}>
                            <Icon className="size-4" /> {name}
                        </SelectItem>
                    );
                })}
            </SelectContent>
        </Select>
    );
}
