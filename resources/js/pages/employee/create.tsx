import { Head } from '@inertiajs/react';
import { Eye, EyeOff, Plus, Save, X } from "lucide-react"
import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import AppLayout from '@/layouts/app-layout';
import type { Location, Role } from '@/lib/model';
import type { BreadcrumbItem } from '@/types';
import employees from '@/routes/employees';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Karyawan Baru',
        href: employees.create().url,
    },
];

type Props = {
    roles: Role[]
    locations: Location[]
}

type LocationRoleRow = {
    location: string
    role: string
    saved: boolean
}

function CreateEmployee({ roles, locations }: Props) {
    const [locationRoles, setLocationRoles] = useState<LocationRoleRow[]>([
        { location: "", role: "", saved: false },
    ])

    const addLocation = () => {
        setLocationRoles((prev) => [
            ...prev,
            { location: "", role: "", saved: false },
        ])
    }

    const removeLocation = (index: number) => {
        setLocationRoles((prev) => prev.filter((_, i) => i !== index))
    }

    const saveRow = (index: number) => {
        setLocationRoles((prev) => {
            const updated = [...prev]
            updated[index].saved = true
            return updated
        })
    }

    const updateLocation = (
        index: number,
        key: "location" | "role",
        value: string
    ) => {
        setLocationRoles((prev) => {
            const updated = [...prev]
            updated[index] = { ...updated[index], [key]: value }
            return updated
        })
    }

    const getLocationName = (id: string) =>
        locations.find((l) => String(l.id) === id)?.name ?? "-"

    const getRoleName = (id: string) =>
        roles.find((r) => String(r.id) === id)?.name ?? "-"

    const [showPassword, setShowPassword] = useState(false)
    const [showConfirm, setShowConfirm] = useState(false)

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Karyawan Baru" />
            <div className="p-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Form Karyawan</CardTitle>
                        <p className="text-sm text-muted-foreground">Data Karyawan Baru</p>
                    </CardHeader>

                    <CardContent className="space-y-6">
                        {/* Row 1 */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>Nama depan</Label>
                                <Input placeholder="Masukkan nama depan" />
                            </div>
                            <div>
                                <Label>Nama belakang</Label>
                                <Input placeholder="Masukkan nama belakang" />
                            </div>
                        </div>

                        {/* Row 2 */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>Email</Label>
                                <Input type="email" placeholder="Masukkan email" />
                            </div>
                            <div>
                                <Label>Role</Label>
                                <Select>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Masukkan role" />
                                    </SelectTrigger>
                                    <SelectContent className="max-h-60 overflow-y-auto">
                                        {roles.map((r) => (
                                            <SelectItem key={r.id} value={String(r.id)}>
                                                {r.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Password */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>Password</Label>
                                <div className="relative">
                                    <Input
                                        type={showPassword ? "text" : "password"}
                                        placeholder="Masukkan password"
                                        className="pr-10"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword((v) => !v)}
                                        className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                    >
                                        {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                    </button>
                                </div>
                            </div>

                            <div>
                                <Label>Konfirmasi Password</Label>
                                <div className="relative">
                                    <Input
                                        type={showConfirm ? "text" : "password"}
                                        placeholder="Masukkan konfirmasi password"
                                        className="pr-10"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowConfirm((v) => !v)}
                                        className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                    >
                                        {showConfirm ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Locations */}
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <Label>Locations</Label>
                                <Button variant="secondary" size="sm" onClick={addLocation}>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Tambah Location
                                </Button>
                            </div>

                            <div className="border rounded-lg p-4 space-y-3">
                                <div className="grid grid-cols-12 gap-3 text-sm font-medium text-muted-foreground">
                                    <div className="col-span-5">Location</div>
                                    <div className="col-span-5">Role</div>
                                    <div className="col-span-2 text-center">Action</div>
                                </div>

                                {locationRoles.map((item, index) => (
                                    <div key={index} className="grid grid-cols-12 gap-3 items-center">
                                        {/* LOCATION */}
                                        <div className="col-span-5">
                                            {item.saved ? (
                                                <div className="font-medium">
                                                    {getLocationName(item.location)}
                                                </div>
                                            ) : (
                                                <Select
                                                    value={item.location}
                                                    onValueChange={(val) =>
                                                        updateLocation(index, "location", val)
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Pilih Lokasi" />
                                                    </SelectTrigger>
                                                    <SelectContent className="max-h-60 overflow-y-auto">
                                                        {locations.map((r) => (
                                                            <SelectItem key={r.id} value={String(r.id)}>
                                                                {r.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        </div>

                                        {/* ROLE */}
                                        <div className="col-span-5">
                                            {item.saved ? (
                                                <div>{getRoleName(item.role)}</div>
                                            ) : (
                                                <Select
                                                    value={item.role}
                                                    onValueChange={(val) =>
                                                        updateLocation(index, "role", val)
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Pilih Role" />
                                                    </SelectTrigger>
                                                    <SelectContent className="max-h-60 overflow-y-auto">
                                                        {roles.map((r) => (
                                                            <SelectItem key={r.id} value={String(r.id)}>
                                                                {r.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        </div>

                                        {/* ACTION */}
                                        <div className="col-span-2 flex justify-center gap-2">
                                            {!item.saved ? (
                                                <>
                                                    <Button
                                                        size="icon"
                                                        variant="default"
                                                        disabled={!item.location || !item.role}
                                                        onClick={() => saveRow(index)}
                                                        title="Simpan"
                                                    >
                                                        <Save className="w-4 h-4" />
                                                    </Button>

                                                    <Button
                                                        size="icon"
                                                        variant="destructive"
                                                        onClick={() => removeLocation(index)}
                                                        title="Hapus"
                                                    >
                                                        <X className="w-4 h-4" />
                                                    </Button>
                                                </>
                                            ) : (
                                                <Button
                                                    size="icon"
                                                    variant="outline"
                                                    onClick={() => removeLocation(index)}
                                                    title="Hapus"
                                                >
                                                    <X className="w-4 h-4 text-red-500" />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="justify-end flex gap-3">
                            <Button variant="outline">Batal</Button>
                            <Button className="bg-blue-600 hover:bg-blue-700">
                                Simpan
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

export default CreateEmployee;
