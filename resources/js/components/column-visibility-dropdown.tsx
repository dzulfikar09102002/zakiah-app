import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Table } from '@tanstack/react-table';
import { ChevronDown } from 'lucide-react';

export default function <T>({ table }: { table: Table<T> }) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant={'outline'}>Pilih Kolom <ChevronDown /></Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {{ ...table }
                    .getAllColumns().map((column) => {
                        return (
                            <DropdownMenuCheckboxItem
                                key={column.id}
                                className="capitalize"
                                checked={column.getIsVisible()}
                                onCheckedChange={(value) =>
                                    column.toggleVisibility(!!value)
                                }
                            >
                                {column.columnDef.header?.toString()}
                            </DropdownMenuCheckboxItem>
                        )
                    })}
            </DropdownMenuContent>
        </DropdownMenu>
    )
}