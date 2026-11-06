export type MenuHeaderRow = {
    id: number;
    code: string;
    section: 'main' | 'setting';
    name: string;
    icon: string | null;
    route_name: string | null;
    seq: number;
    is_active: boolean;
    details: {
        menu_submenu_id: number;
        menu_group_id: number | null;
        seq: number;
    }[];
};

export type MenuSubmenuRow = {
    id: number;
    code: string;
    name: string;
    icon: string | null;
    route_name: string;
    is_active: boolean;
    actions: string[];
    used: number;
};

export type MenuGroupRow = {
    id: number;
    code: string;
    name: string;
    icon: string | null;
    is_active: boolean;
    used: number;
};
