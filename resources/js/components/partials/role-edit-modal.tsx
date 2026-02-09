"use client"

import { Form } from "@inertiajs/react"
import { useState, useEffect } from "react"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Spinner } from "@/components/ui/spinner"

type Props = {
    isOpen: boolean
    role: { id: number; name: string }
    submitUrl: string
    onClose?: () => void
    onSuccess?: () => void
}

export default function RoleEditModal({ isOpen, role, submitUrl, onClose, onSuccess }: Props) {
    const [name, setName] = useState(role.name)

    useEffect(() => {
        setName(role.name)
    }, [role])

    if (!isOpen) return null

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center bg-black/50 pt-16">
            <div className="bg-white rounded-xl w-full max-w-md p-6 shadow-lg">
                <h2 className="text-lg font-semibold mb-2">Edit Role</h2>
                <p className="text-sm text-gray-500 mb-4">Edit data role.</p>

                <Form
                    action={submitUrl}
                    method="post"
                    className="space-y-3"
                    onSuccess={() => {
                        toast.success("Role berhasil diupdate", { position: "top-right" })
                        onSuccess?.()
                    }}
                >
                    {({ processing, errors }) => (
                        <>
                            <Input
                                name="name"
                                placeholder="Nama role"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                            />
                            {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}

                            <div className="flex justify-end gap-2 mt-6">
                                <Button type="button" variant="outline" onClick={onClose}>
                                    Batal
                                </Button>
                                <Button type="submit" disabled={processing} className="flex items-center gap-2">
                                    {processing && <Spinner />}
                                    {processing ? "Menyimpan..." : "Simpan"}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </div>
    )
}