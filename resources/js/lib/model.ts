export type Role = {
    id: number
    name: string
}

export type Employee = {
    id: number
    first_name: string
    last_name: string
    role_id?: number
    role_name?: string
    deleted_at: string
}

export type Location = {
    id: number
    name: string
}

export type Category = {
    id: number
    name: string
    deleted_at?: string
}

export interface Product {
    id: number;
    entity_id: number;
    product_category_id: number;
    product_category?: Category;
    product_unit_id: number;
    location_id: number;
    tax_id: number | null;
    child_product_category_id: number | null;
    product_sell_unit_id: number;
    parent_variance_id: number | null;
    name: string;
    code: string;
    sku: string;
    barcode: string;
    description: string;
    image_url: string | null;
    sell_to_customer: number | boolean; // 
    service: number | boolean;
    modifier: number | boolean;
    has_variance: number | boolean;
    allow_custom_price: number | boolean;
    select_all_location: number | boolean;
    location_ids: number[] | null;
    exclude_location_ids: number[] | null;
    tax_setting: unknown | null; // Bisa dispesifikkan jika ada strukturnya
    sell_price: number;
    status: "active" | "inactive" | string;
    deleted_at: string | null;
    created_at: string;
    updated_at: string;
    updated_by: number | null;
    created_by: number | null;
    cost_of_goods_sold: number;
    last_buying_price: number;
    total_stock: string | number; // Di data kamu berupa string "544"
}

export type Pagination<T> = {
    data: T[]
    current_page: number
    total: number
    last_page: number
    per_page: number
    first_page_url: string
    last_page_url: string
    prev_page_url?: string
    next_page_url?: string
    path: string
    links: {
        url?: string
        label: string
        active: boolean
    }[]
}