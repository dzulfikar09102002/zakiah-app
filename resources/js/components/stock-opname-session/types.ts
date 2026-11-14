import type { Pagination } from '@/lib/model';

export type SessionStatus = 'scheduled' | 'started' | 'review' | 'finished';

export type SessionEmployee = {
    id: number;
    first_name: string;
    last_name: string;
};

export type OpnameSession = {
    id: number;
    code: string;
    type: 1 | 2;
    status: SessionStatus;
    location_id: number;
    opname_date: string;
    pic_employee_id: number;
    start_at: string | null;
    review_at: string | null;
    end_at: string | null;
    note: string | null;
    location?: { id: number; name: string } | null;
    pic?: SessionEmployee | null;
    employees?: SessionEmployee[];
    employees_count?: number;
    can_enter?: boolean;
};

export type Viewer = {
    employee_id: number | null;
    is_pic: boolean;
    can_manage_schedule: boolean;
    can_cancel: boolean;
    can_correct: boolean;
};

export type EmployeeOption = {
    value: number;
    label: string;
};

export type LiveItem = {
    product_id: number;
    name: string;
    barcode: string | null;
    sku: string | null;
    unit_name: string | null;
    counted: number;
    last_id: number;
};

export type LiveEmployee = {
    employee_id: number;
    name: string;
    qty: number;
};

export type LiveResponse = {
    status: SessionStatus;
    cursor: number;
    items: LiveItem[];
    employees: LiveEmployee[];
};

export type ReviewRow = {
    product_id: number;
    name: string;
    barcode: string | null;
    sku: string | null;
    unit_name: string | null;
    recorded_stock: number;
    counted_stock: number;
    difference_stock: number;
    scanned: number;
    decision: number | null;
    note: string | null;
    /** Hanya terisi di tabel review. */
    scanners?: { name: string; qty: number }[];
    rapid_repeats?: number;
    cancelled_scans?: number;
};

export type ProductScan = {
    id: number;
    barcode: string;
    scanned_at: string;
    employee: string;
    /** Ditambah manual lewat tombol Tambah Scan, bukan dari barcode. */
    manual: boolean;
    cancelled: boolean;
    reason: string | null;
    cancelled_at: string | null;
    cancelled_by: string | null;
};

export type ReviewSummary = {
    difference: number;
    matched: number;
    all: number;
    pending: number;
    accepted: number;
    rejected: number;
    unscanned: number;
    difference_stock: number;
};

export type ReviewFilter = 'all' | 'difference' | 'matched';

export type ReviewData = {
    pagination: Pagination<ReviewRow>;
    summary: ReviewSummary;
    suspect_count: number;
    filter: ReviewFilter;
    search: string;
};

/** Pakai stok hasil SO: stok sistem disesuaikan saat sesi ditutup. */
export const DECISION_ACCEPTED = 1;
/** Pakai stok sistem yang sudah ada: stok tidak berubah. */
export const DECISION_REJECTED = 2;
