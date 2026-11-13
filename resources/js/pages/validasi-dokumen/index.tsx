import { Head } from '@inertiajs/react';
import { ShieldCheck, ShieldX } from 'lucide-react';
import { GlassBrand, GlassPage, glassMuted } from '@/components/glass-page';
import {
    formatSessionDate,
    formatSessionTime,
} from '@/components/stock-opname-session/helpers';

type ValidatedDocument = {
    entity: string;
    logo: string | null;
    type: string;
    code: string;
    location: string | null;
    opname_date: string;
    signed_at: string;
    signer: string;
    signer_role: string;
    summary: { label: string; value: string }[];
};

function Row({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4 py-2.5">
            <dt className={`shrink-0 ${glassMuted}`}>{label}</dt>
            <dd className="min-w-0 text-right font-semibold break-words">
                {value}
            </dd>
        </div>
    );
}

/** Halaman publik tujuan QR tanda tangan elektronik, tanpa login dan tanpa menu. */
export default function ValidasiDokumen({
    document,
}: {
    document: ValidatedDocument | null;
}) {
    return (
        <GlassPage>
            <Head title="Validasi Dokumen" />

            {document ? (
                <>
                    <GlassBrand logo={document.logo} name={document.entity} />

                    <div className="my-5 flex flex-col items-center gap-2 text-center">
                        <span className="flex size-16 items-center justify-center rounded-2xl bg-emerald-600/15">
                            <ShieldCheck className="size-9 text-emerald-700" />
                        </span>
                        <h1 className="text-2xl font-extrabold text-emerald-800">
                            Dokumen Asli
                        </h1>
                        <p className={`text-sm leading-relaxed ${glassMuted}`}>
                            Ditandatangani secara elektronik. Pastikan isi
                            dokumen sama dengan ringkasan berikut.
                        </p>
                    </div>

                    <dl className="divide-y divide-[var(--g-ink)]/10 text-sm">
                        <Row label="Jenis" value={document.type} />
                        <Row label="No. Dokumen" value={document.code} />
                        <Row label="Toko" value={document.location ?? '-'} />
                        <Row
                            label="Tanggal SO"
                            value={formatSessionDate(document.opname_date)}
                        />
                        <Row
                            label="Ditandatangani"
                            value={formatSessionTime(document.signed_at)}
                        />
                        <Row label="Penanda tangan" value={document.signer} />
                        <Row label="Sebagai" value={document.signer_role} />
                        {document.summary.map((item) => (
                            <Row
                                key={item.label}
                                label={item.label}
                                value={item.value}
                            />
                        ))}
                    </dl>
                </>
            ) : (
                <div className="flex flex-col items-center gap-3 py-6 text-center">
                    <span className="flex size-16 items-center justify-center rounded-2xl bg-red-600/15">
                        <ShieldX className="size-9 text-red-700" />
                    </span>
                    <h1 className="text-2xl font-extrabold text-red-800">
                        Dokumen Tidak Valid
                    </h1>
                    <p className={`text-sm leading-relaxed ${glassMuted}`}>
                        QR tidak dikenali atau sudah diubah. Jangan gunakan
                        dokumen ini sebagai dasar apa pun.
                    </p>
                </div>
            )}
        </GlassPage>
    );
}
