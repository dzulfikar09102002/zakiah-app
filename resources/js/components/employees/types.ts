export type RoleOption = {
    value: number;
    label: string;
    allow_pos: boolean;
    allow_backoffice: boolean;
};

export type AccessRoleOption = {
    value: number;
    label: string;
};

export type LocationOption = {
    id: number;
    name: string;
};

export type EmployeeAssignment = {
    location_id: number;
    location: string;
    roles: { role_id: number; label: string }[];
};

export type EmployeeRow = {
    id: number;
    user_id: number;
    first_name: string;
    last_name: string;
    name: string;
    role_id: number;
    role_label: string;
    access_role_id: number | null;
    access_role_label: string | null;
    deleted_at: string | null;
    has_login: boolean;
    email: string | null;
    /** Data lama: akun ini dipakai lebih dari satu karyawan. */
    shares_account: boolean;
    assignments: EmployeeAssignment[];
};

export type UserAccountRow = {
    id: number;
    name: string;
    email: string | null;
    deleted_email: string | null;
    deleted_at: string | null;
    is_self: boolean;
    employees: { id: number; name: string }[];
};

export type EmployeeOption = {
    value: number;
    label: string;
};
