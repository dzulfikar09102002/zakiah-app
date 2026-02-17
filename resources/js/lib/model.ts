export type Role = {
    id: number
    name: string
}

export interface PaymentMethod {
    id: number
    entity_id: number | null
    name: string
    icon_image_url: string | null
    status: 'active' | 'inactive'
    kind: 'cash' | 'debit' | 'credit_card' | 'qris' | 'online_payment' | 'va'
    fixed_fee: number
    variable_fee: number
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
    updated_by: number | null
    created_by: number | null
}


export interface Employee {
    id: number
    entity_id: number
    user_id: number
    role_id: number
    code: string
    first_name: string
    last_name: string
    select_all_location: boolean
    entity_permission: Record<string, unknown> | null
    location_permission: Record<string, unknown> | null
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
    updated_by: number | null
    created_by: number | null
    role?: {
        id: number
        name: string
    } | null
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
export interface Location {
    id: number
    entity_id: number
    code: string
    initial: string
    name: string
    search_name: string
    image_url?: string | null
    icon_image_url?: string | null
    backoffice_phone_number?: string | null
    backoffice_phone_number_country_code?: string | null
    backoffice_email?: string | null
    contact_phone_number?: string | null
    contact_phone_number_country_code?: string | null
    contact_email?: string | null
    kind: string
    warehouse: boolean
    full_address?: string | null
    postal_code?: string | null
    city?: string | null
    province?: string | null
    country?: string | null
    timezone?: string | null
    footer?: string | null
    allow_transfer_stock: boolean
    allow_external_supplier: boolean
    franchise: boolean
    status: 'active' | 'inactive'
    deleted_at?: string | null
    created_at?: string | null
    updated_at?: string | null
    updated_by?: number | null
    created_by?: number | null
    checksum: string
}

export interface Unit {
    id: number
    entity_id: number
    name: string
    search_name: string
    status: 'active' | 'inactive'
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
    updated_by: number | null
    created_by: number | null
}

export interface ProductStock {
    id: number
    product_id: number
    location_id: number
    product_unit_id: number
    stock: number
    last_in_stock: number
    last_out_stock: number
    last_buy_price: number
    average_buy_price: number
    lowest_buy_price: number
    highest_buy_price: number
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
    created_by: number | null
    updated_by: number | null
    checksum: string
    product: Product
    location: Location
}
