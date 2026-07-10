import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Table } from '@tanstack/react-table';
import { ChevronDown } from 'lucide-react';
import '@tanstack/react-table';

declare module '@tanstack/react-table' {
    interface ColumnMeta<TData, TValue> {
        label?: string;
    }
}
export default function <T>({ table }: { table: Table<T> }) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant={'outline'} className="flex justify-between">
                    Kolom
                    <ChevronDown />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
                {table.getAllColumns().map((column) => {
                    const header = column.columnDef.header;
                    const label =
                        column.columnDef.meta?.label ??
                        (typeof header === 'string' ? header : column.id);

                    return (
                        <DropdownMenuCheckboxItem
                            key={column.id}
                            className="capitalize"
                            checked={column.getIsVisible()}
                            onCheckedChange={(value) =>
                                column.toggleVisibility(!!value)
                            }
                        >
                            {label}
                        </DropdownMenuCheckboxItem>
                    );
                })}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
