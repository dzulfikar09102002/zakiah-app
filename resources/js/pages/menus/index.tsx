import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { useState } from 'react';
import { toast } from 'sonner';
import HeaderDialog from '@/components/menus/header-dialog';
import ItemDialog from '@/components/menus/item-dialog';
import type {
    MenuGroupRow,
    MenuHeaderRow,
    MenuSubmenuRow,
} from '@/components/menus/types';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { renderNavIcon } from '@/lib/nav-icons';
import menuGroups from '@/routes/menu-groups';
import menuHeaders from '@/routes/menu-headers';
import menuSubmenus from '@/routes/menu-submenus';
import type { BreadcrumbItem } from '@/types';

type Tab = 'headers' | 'submenus' | 'groups';

type Props = {
    tab: Tab;
    headers: MenuHeaderRow[];
    submenus: MenuSubmenuRow[];
    groups: MenuGroupRow[];
    routeOptions: string[];
};

type Deleting = { tab: Tab; id: number; name: string } | null;

const title = 'Menu';

const breadcrumbs: BreadcrumbItem[] = [
    { title, href: menuHeaders.index().url },
];

const tabs: { value: Tab; label: string; href: string }[] = [
    { value: 'headers', label: 'Header', href: menuHeaders.index().url },
    { value: 'submenus', label: 'Sub Menu', href: menuSubmenus.index().url },
    { value: 'groups', label: 'Group', href: menuGroups.index().url },
];

const destroyUrl = (tab: Tab, id: number) =>
    ({
        headers: menuHeaders.destroy(id).url,
        submenus: menuSubmenus.destroy(id).url,
        groups: menuGroups.destroy(id).url,
    })[tab];

function IconName({ name }: { name: string | null }) {
    const icon = renderNavIcon(name);

    return icon ? (
        <span className="flex items-center gap-2">
            {icon}
            <span className="text-xs text-muted-foreground">{name}</span>
        </span>
    ) : (
        <span className="text-muted-foreground">-</span>
    );
}

function ActiveBadge({ active }: { active: boolean }) {
    return active ? (
        <Badge variant="secondary">Tampil</Badge>
    ) : (
        <Badge variant="outline">Disembunyikan</Badge>
    );
}

/**
 * Konfigurasi > Menu: susunan sidebar (header, sub menu, group). Berlaku untuk semua
 * entity; siapa yang boleh membuka menu diatur di Hak Akses.
 */
export default function MenuIndex({
    tab,
    headers,
    submenus,
    groups,
    routeOptions,
}: Props) {
    const [editingHeader, setEditingHeader] = useState<{
        open: boolean;
        item: MenuHeaderRow | null;
    }>({ open: false, item: null });
    const [editingSubmenu, setEditingSubmenu] = useState<{
        open: boolean;
        item: MenuSubmenuRow | null;
    }>({ open: false, item: null });
    const [editingGroup, setEditingGroup] = useState<{
        open: boolean;
        item: MenuGroupRow | null;
    }>({ open: false, item: null });
    const [deleting, setDeleting] = useState<Deleting>(null);
    const [processing, setProcessing] = useState(false);

    const submenuName = (id: number) =>
        submenus.find((submenu) => submenu.id === id)?.name ?? '-';

    const openCreate = () => {
        if (tab === 'headers') {
            setEditingHeader({ open: true, item: null });
        } else if (tab === 'submenus') {
            setEditingSubmenu({ open: true, item: null });
        } else {
            setEditingGroup({ open: true, item: null });
        }
    };

    const destroy = () => {
        if (!deleting) {
            return;
        }

        router.delete(destroyUrl(deleting.tab, deleting.id), {
            preserveScroll: true,
            onBefore: () => setProcessing(true),
            onSuccess: () => toast.success(`${deleting.name} dihapus`),
            onError: (errors) => toast.error(errors.menu ?? 'Gagal menghapus'),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    };

    const actions = (
        onEdit: () => void,
        onDelete: () => void,
        name: string,
    ) => (
        <div className="flex justify-center gap-2">
            <Button
                size="icon"
                variant="outline"
                className="size-11 md:size-9"
                aria-label={`Edit ${name}`}
                onClick={onEdit}
            >
                <Pencil />
            </Button>
            <Button
                size="icon"
                variant="destructive"
                className="size-11 md:size-9"
                aria-label={`Hapus ${name}`}
                onClick={onDelete}
            >
                <X />
            </Button>
        </div>
    );

    const table = (head: string[], body: ReactNode) => (
        <Table>
            <TableHeader>
                <TableRow>
                    {head.map((label) => (
                        <TableHead
                            key={label}
                            className={label === 'Aksi' ? 'text-center' : ''}
                        >
                            {label}
                        </TableHead>
                    ))}
                </TableRow>
            </TableHeader>
            <TableBody>{body}</TableBody>
        </Table>
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <HeaderDialog
                open={editingHeader.open}
                header={editingHeader.item}
                submenus={submenus}
                groups={groups}
                routeOptions={routeOptions}
                onClose={() => setEditingHeader({ open: false, item: null })}
            />
            <ItemDialog
                kind="submenu"
                open={editingSubmenu.open}
                item={editingSubmenu.item}
                routeOptions={routeOptions}
                onClose={() => setEditingSubmenu({ open: false, item: null })}
            />
            <ItemDialog
                kind="group"
                open={editingGroup.open}
                item={editingGroup.item}
                onClose={() => setEditingGroup({ open: false, item: null })}
            />

            <AlertDialog
                open={deleting !== null}
                onOpenChange={(open) =>
                    !open && !processing && setDeleting(null)
                }
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Hapus {deleting?.name}?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {deleting?.tab === 'headers'
                                ? 'Sub menu di dalamnya tetap ada, hanya tidak tampil di header ini lagi.'
                                : 'Sub menu yang punya route Hak Akses tidak bisa dihapus; nonaktifkan saja.'}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={processing}>
                            Batal
                        </AlertDialogCancel>
                        <Button
                            variant="destructive"
                            disabled={processing}
                            onClick={destroy}
                        >
                            {processing && <Spinner />}
                            Hapus
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <div className="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <Tabs value={tab}>
                    <TabsList>
                        {tabs.map((item) => (
                            <TabsTrigger
                                key={item.value}
                                value={item.value}
                                asChild
                            >
                                <Link href={item.href}>{item.label}</Link>
                            </TabsTrigger>
                        ))}
                    </TabsList>
                </Tabs>
                <Button className="h-11 md:h-9" onClick={openCreate}>
                    <Plus />{' '}
                    {tab === 'headers'
                        ? 'Header Baru'
                        : tab === 'submenus'
                          ? 'Sub Menu Baru'
                          : 'Group Baru'}
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardContent className="p-0 lg:px-6">
                    {tab === 'headers' &&
                        table(
                            [
                                'Urutan',
                                'Header',
                                'Ikon',
                                'Isi',
                                'Status',
                                'Aksi',
                            ],
                            headers.map((header) => (
                                <TableRow key={header.id}>
                                    <TableCell>{header.seq}</TableCell>
                                    <TableCell>
                                        <p className="font-medium">
                                            {header.name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {header.section === 'setting'
                                                ? 'Pengaturan'
                                                : 'Menu utama'}
                                        </p>
                                    </TableCell>
                                    <TableCell>
                                        <IconName name={header.icon} />
                                    </TableCell>
                                    <TableCell className="max-w-md whitespace-normal">
                                        {header.route_name
                                            ? `Langsung: ${header.route_name}`
                                            : [...header.details]
                                                  .sort((a, b) => a.seq - b.seq)
                                                  .map((detail) =>
                                                      submenuName(
                                                          detail.menu_submenu_id,
                                                      ),
                                                  )
                                                  .join(', ') || '-'}
                                    </TableCell>
                                    <TableCell>
                                        <ActiveBadge
                                            active={header.is_active}
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {actions(
                                            () =>
                                                setEditingHeader({
                                                    open: true,
                                                    item: header,
                                                }),
                                            () =>
                                                setDeleting({
                                                    tab: 'headers',
                                                    id: header.id,
                                                    name: header.name,
                                                }),
                                            header.name,
                                        )}
                                    </TableCell>
                                </TableRow>
                            )),
                        )}

                    {tab === 'submenus' &&
                        table(
                            [
                                'Sub Menu',
                                'Ikon',
                                'Halaman',
                                'Aksi Hak Akses',
                                'Dipakai',
                                'Status',
                                'Aksi',
                            ],
                            submenus.map((submenu) => (
                                <TableRow key={submenu.id}>
                                    <TableCell className="font-medium">
                                        {submenu.name}
                                    </TableCell>
                                    <TableCell>
                                        <IconName name={submenu.icon} />
                                    </TableCell>
                                    <TableCell className="text-xs">
                                        {submenu.route_name}
                                    </TableCell>
                                    <TableCell className="max-w-xs text-xs whitespace-normal">
                                        {submenu.actions.join(', ') || '-'}
                                    </TableCell>
                                    <TableCell>
                                        {submenu.used
                                            ? `${submenu.used} header`
                                            : 'Belum'}
                                    </TableCell>
                                    <TableCell>
                                        <ActiveBadge
                                            active={submenu.is_active}
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {actions(
                                            () =>
                                                setEditingSubmenu({
                                                    open: true,
                                                    item: submenu,
                                                }),
                                            () =>
                                                setDeleting({
                                                    tab: 'submenus',
                                                    id: submenu.id,
                                                    name: submenu.name,
                                                }),
                                            submenu.name,
                                        )}
                                    </TableCell>
                                </TableRow>
                            )),
                        )}

                    {tab === 'groups' &&
                        table(
                            ['Group', 'Ikon', 'Dipakai', 'Status', 'Aksi'],
                            groups.map((group) => (
                                <TableRow key={group.id}>
                                    <TableCell className="font-medium">
                                        {group.name}
                                    </TableCell>
                                    <TableCell>
                                        <IconName name={group.icon} />
                                    </TableCell>
                                    <TableCell>
                                        {group.used
                                            ? `${group.used} sub menu`
                                            : 'Belum'}
                                    </TableCell>
                                    <TableCell>
                                        <ActiveBadge active={group.is_active} />
                                    </TableCell>
                                    <TableCell>
                                        {actions(
                                            () =>
                                                setEditingGroup({
                                                    open: true,
                                                    item: group,
                                                }),
                                            () =>
                                                setDeleting({
                                                    tab: 'groups',
                                                    id: group.id,
                                                    name: group.name,
                                                }),
                                            group.name,
                                        )}
                                    </TableCell>
                                </TableRow>
                            )),
                        )}
                </CardContent>
            </Card>
        </AppLayout>
    );
}
