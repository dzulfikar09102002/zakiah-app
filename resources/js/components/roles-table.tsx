import { router } from "@inertiajs/react"
import { CircleAlert, Pencil, Settings, Trash2 } from "lucide-react"
import { useState } from "react"
import { toast } from "sonner"
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogMedia,
    AlertDialogTitle,
} from "@/components/ui/alert-dialog"
import { Button } from "@/components/ui/button"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import type { Role } from "@/lib/model"
import RoleEditModal from "./partials/role-edit-modal"
import { Spinner } from "./ui/spinner"

type Props = {
    roles: {
        data: Role[]
        current_page: number
        last_page: number
        total: number
        per_page: number
    }
}

function RolesTable({ roles }: Props) {
    const [editRole, setEditRole] = useState<Role | null>(null)
    const [deleteRole, setdeleteRole] = useState<Role | null>(null);
    const [processing, setProcessing] = useState(false);
    const startIndex = (roles.current_page - 1) * roles.per_page
    const handleDelete = () => {
        if (!deleteRole) return

        setProcessing(true)

        router.delete(`/administrasi/role/${deleteRole.id}/delete`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Role berhasil dihapus", { position: "top-right" })
            },
            onFinish: () => {
                setProcessing(false)
                setdeleteRole(null)
            },
        })
    }
    return (
        <div className="relative w-full overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>No.</TableHead>
                        <TableHead>Nama</TableHead>
                        <TableHead className="text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {roles.data.map((role: Role, index: number) => (
                        <TableRow key={role.id ?? index}>
                            <TableCell>{startIndex + index + 1}.</TableCell>
                            <TableCell>{role.name}</TableCell>
                            <TableCell className="space-x-2 text-center">
                                <Button size="icon" variant="secondary">
                                    <Settings className="size-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    onClick={() => setEditRole(role)}
                                >
                                    <Pencil className="size-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="destructive"
                                    onClick={() => setdeleteRole(role)}
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    ))}

                    {roles.data.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={6} className="text-center py-8 text-muted-foreground">
                                Tidak ada data role
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
            {editRole && (
                <RoleEditModal
                    isOpen={!!editRole}
                    role={editRole}
                    submitUrl={`/administrasi/role/${editRole.id}/update`}
                    onClose={() => setEditRole(null)}
                    onSuccess={() => setEditRole(null)}
                />
            )}
            <AlertDialog open={!!deleteRole} onOpenChange={() => setdeleteRole(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogMedia className="size-19">
                            <CircleAlert />
                        </AlertDialogMedia>
                        <AlertDialogTitle>Yakin ingin menghapus role?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Role <b>{deleteRole?.name}</b> akan dihapus permanen dan tidak bisa dikembalikan.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel onClick={() => setdeleteRole(null)}>
                            Batal
                        </AlertDialogCancel>
                        <AlertDialogAction
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={processing}
                            className="flex items-center gap-2"
                        >
                            {processing && <Spinner />}
                            {processing ? "Menghapus..." : "Ya, Hapus"}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    )
}
export default RolesTable