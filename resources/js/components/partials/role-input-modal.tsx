import { Form } from "@inertiajs/react"
import { Plus } from "lucide-react"
import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Spinner } from "@/components/ui/spinner"

type Props = {
    triggerText?: string
    submitUrl: string
    onSuccess?: () => void
}

export default function RoleInputModal({
    triggerText = "Role Baru",
    submitUrl,
    onSuccess,
}: Props) {
    const [isOpen, setIsOpen] = useState(false)
    const [name, setName] = useState("")

    return (
        <>
            <Button className="mb-4" onClick={() => setIsOpen(true)}>
                <Plus />
                {triggerText}
            </Button>

            {isOpen && (
                <div className="fixed inset-0 z-50 flex items-start justify-center bg-black/50 pt-16">
                    <div className="bg-white rounded-xl w-full max-w-md p-6 shadow-lg">
                        <h2 className="text-lg font-semibold mb-2">
                            Tambah Role Baru
                        </h2>
                        <p className="text-sm text-gray-500 mb-4">
                            Masukkan data role baru.
                        </p>

                        <Form
                            action={submitUrl}
                            method="post"
                            className="space-y-3"
                            onSuccess={() => {
                                setIsOpen(false)
                                setName("")
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

                                    {errors.name && (
                                        <p className="text-sm text-red-600">
                                            {errors.name}
                                        </p>
                                    )}

                                    <div className="flex justify-end gap-2 mt-6">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setIsOpen(false)}
                                        >
                                            Batal
                                        </Button>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="flex items-center gap-2"
                                        >
                                            {processing && <Spinner />}
                                            {processing ? "Menyimpan..." : "Simpan"}
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </div>
            )}
        </>
    )
}