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
    parent_session_id: number | null;
    start_at: string | null;
    review_at: string | null;
    end_at: string | null;
    note: string | null;
    location?: { id: number; name: string } | null;
    pic?: SessionEmployee | null;
    employees?: SessionEmployee[];
    employees_count?: number;
    can_enter?: boolean;
    parent?: { id: number; code: string } | null;
    children?: Pick<
        OpnameSession,
        'id' | 'code' | 'type' | 'opname_date' | 'status'
    >[];
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
    /** Hanya terisi di tabel review, tidak di lampiran revisi. */
    scanners?: { name: string; qty: number }[];
    rapid_repeats?: number;
    cancelled_scans?: number;
};

export type ProductScan = {
    id: number;
    barcode: string;
    scanned_at: string;
    employee: string;
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

export type RevisionRow = {
    product_id: number;
    seq: number;
    name: string;
    barcode: string | null;
    sku: string | null;
    adjustment_qty: number;
    reason: number;
    reason_label: string;
    note: string | null;
};

export type RevisionData = {
    products: ReviewRow[];
    rows: RevisionRow[];
    reasons: { value: number; label: string }[];
};

export const DECISION_ACCEPTED = 1;
export const DECISION_REJECTED = 2;
export const REASON_LOST = 3;
