export type AccessRoleRow = {
    id: number;
    name: string;
    note: string | null;
    is_system: boolean;
    is_active: boolean;
    /** Label role template yang memakai Hak Akses ini secara otomatis. */
    default_for: string | null;
    employees_count: number;
    actions_count: number;
    action_ids: number[];
};

export type MatrixAction = {
    id: number;
    code: string;
    name: string;
};

export type MatrixHeader = {
    id: number;
    name: string;
    rows: {
        submenu_id: number;
        name: string;
        group: string | null;
        actions: MatrixAction[];
    }[];
};
