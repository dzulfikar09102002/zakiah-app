import { Pencil } from "lucide-react";
import { Form, Head, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Field,
    FieldSet,
    FieldGroup,
    FieldLabel,
    FieldError
} from "@/components/ui/field";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import products from "@/routes/products";

const title = 'Tambah Produk'

const breadcrumbs: BreadcrumbItem[] = [
    { href: products.index().url, title: 'Kelola Produk' },
    { href: products.create().url, title }
]



export default () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />


        </AppLayout>
    );
}