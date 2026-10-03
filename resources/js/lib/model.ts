export type Role = {
    id: number;
    name: string;
    parent_id: string;
};

export interface PaymentMethod {
    id: number;
    entity_id?: number;
    name: string;
    icon_image_url?: string;
    status: 'active' | 'inactive';
    kind: 'cash' | 'debit' | 'credit_card' | 'qris' | 'online_payment' | 'va';
    fixed_fee: number;
    variable_fee: number;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    updated_by?: number;
    created_by?: number;
}

export interface Employee {
    id: number;
    entity_id: number;
    user_id: number;
    role_id: number;
    code: string;
    first_name: string;
    last_name: string;
    select_all_location: boolean;
    entity_permission?: Record<string, unknown>;
    location_permission?: Record<string, unknown>;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    updated_by?: number;
    created_by?: number;
    role?: {
        id: number;
        name: string;
    };
    user?: {
        id: number;
        email: string;
    };
    employeeLocation?: {
        location_id: number;
        role_id: number;
    };
}

export interface EmployeeLocation {
    id: number;
    employee_id: number;
    location_id: number;
    role_id: number;
    code: string;
    entity_permission: Record<string, any>;
    location_permission: Record<string, any>;
    created_at: string;
    updated_at: string;
    updated_by?: number;
    created_by?: number;
    deleted_at?: string;
}

export type Category = {
    id: number;
    name: string;
    deleted_at?: string;
};

export interface Product {
    id: number;
    entity_id: number;
    product_category_id: number;
    product_category?: Category;
    product_unit_id: number;
    location_id: number;
    tax_id?: number;
    child_product_category_id?: number;
    product_sell_unit_id: number;
    parent_variance_id?: number;
    name: string;
    code: string;
    sku: string;
    barcode: string;
    description: string;
    image_url?: string | null;
    sell_to_customer: number | boolean; //
    service: number | boolean;
    modifier: number | boolean;
    has_variance: number | boolean;
    allow_custom_price: number | boolean;
    select_all_location: number | boolean;
    location_ids?: number[];
    exclude_location_ids?: number[];
    tax_setting?: unknown;
    sell_price: number;
    status: 'active' | 'archive';
    deleted_at?: string;
    created_at?: string;
    updated_at?: string;
    updated_by?: number;
    created_by?: number;
    cost_of_goods_sold: number;
    last_buying_price: number;
    total_stock: string | number; // Di data kamu berupa string "544"
    stock_movements?: StockMovement[];
    product_unit_conversions?: any[];
    product_sell_prices?: number[];
    product_location_stocks?: ProductStock[];
    supplier_name: string;
    supplier: Supplier;
}

export type Supplier = {
    id: number;
    entity_id: number;
    code: string;
    initial: string;
    name: string;
    contact_phone_number: string | null;
    contact_phone_number_country_code: string | null;
    contact_email: string | null;
    full_address: string;
    postal_code: string;
    city: string;
    province: string;
    country: string;
    status: 'active' | 'archived';
};
export type StockMovement = {
    location_id: number;
    buying_price: number;
    stock: number;
};
export type Option = {
    value: string;
    label: string;
};

export interface Top5Product {
    product_name: string;
    total_line_amount: number;
    quantity: number;
}

export interface Top5Category {
    product_category_name: string;
    total_line_amount: number;
    quantity: number;
}
export interface Top5Location {
    location_name: string;
    net_sales_after_tax: number;
}
export interface Top5Data {
    products: Top5Product[];
    categories: Top5Category[];
    locations: Top5Location[];
}

export type ProfitPotential = {
    stock: number;
    cogs: number;
    sell_price: number;
};
export type Pagination<T> = {
    data: T[];
    current_page: number;
    total: number;
    last_page: number;
    per_page: number;
    first_page_url: string;
    last_page_url: string;
    prev_page_url?: string;
    next_page_url?: string;
    path: string;
    links: {
        url?: string;
        label: string;
        active: boolean;
    }[];
};

export interface Customer {
    id: number;
    entity_id: number;
    user_id: number;
    customer_category_id: number;
    location_id: number;
    first_name: string;
    last_name: string;
    phone_number: string;
    phone_number_country_code: string;
    email: string;
    last_visit_at: string;
    last_spend_daily: number;
    last_spend_weekly: number;
    last_spend_monthly: number;
    last_visit_location_id: number;
    status: 'active' | 'inactive' | string;
    deleted_at: string;
    created_at: string;
    updated_at: string;
    updated_by: number;
    created_by: number;
    customer_category: CustomerCategory;
    location: Location;
}

export type EmployeeSalesPerformData = {
    employee_sales_name: string;
    sales_amount: number;
    refund_amount: number;
    net_sales_amount: number;
    sales_count: number;
    refund_count: number;
    net_count: number;
    sales_quantity: number;
    refund_quantity: number;
    net_quantity: number;
};

export type EmployeeSalesDetailData = {
    employee_sales_name: string;
    location_name: string;
    local_sales_date: string;
    sales_amount: number;
    refund_amount: number;
    net_sales_amount: number;
    sales_count: number;
    refund_count: number;
    net_count: number;
    sales_quantity: number;
    refund_quantity: number;
    net_quantity: number;
};

export interface Location {
    id: number;
    entity_id: number;
    code: string;
    initial: string;
    name: string;
    search_name: string;
    image_url?: string;
    icon_image_url?: string;
    backoffice_phone_number?: string;
    backoffice_phone_number_country_code?: string;
    backoffice_email?: string;
    contact_phone_number?: string;
    contact_phone_number_country_code?: string;
    contact_email?: string;
    kind: 'main_office' | 'outlet' | 'warehouse';
    warehouse: boolean;
    full_address?: string;
    postal_code?: string;
    district?: string;
    city?: string;
    province?: string;
    country?: string;
    timezone?: string;
    footer?: string;
    allow_transfer_stock: boolean;
    allow_external_supplier: boolean;
    franchise: boolean;
    status: 'active' | 'inactive';
    deleted_at?: string;
    created_at?: string;
    updated_at?: string;
    updated_by?: number;
    created_by?: number;
    checksum: string;
}

export interface Unit {
    id: number;
    entity_id: number;
    name: string;
    search_name: string;
    status: 'active' | 'inactive';
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    updated_by?: number;
    created_by?: number;
}
export interface OrderType {
    id: number;
    entity_id: number;
    payment_method_id: number;
    name: string;
    search_name: string;
    fixed_fee: number;
    variable_fee: number;
    require_customer_data: boolean;
    status: string;
    created_at: string;
    updated_at: string;
    deleted_at: string;
    updated_by: number;
    created_by: number;
    payment_method: PaymentMethod | null;
}

export interface CustomerCategory {
    id: number;
    entity_id: number;
    name: string;
    status: 'active' | 'archived';
    required: boolean;
    minimal_spend: number;
    last_reset_at: string;
    reset_every: 'daily' | 'weekly' | 'monthly' | 'annual';
    updated_by: number;
    created_by: number;
    created_at: string;
    updated_at: string;
    deleted_at: string;
    customerCategoryRule: CustomerCategoryRule;
}
export interface CustomerCategoryRule {
    id: number;
    customer_category_id: number;
    minimal_spend: number;
    include_tax: boolean;
    include_service_charge: boolean;
    include_promo: boolean;
    include_surcharge: boolean;
    include_free_of_charge: boolean;
    created_at: string;
    updated_at: string;
}
export interface ProductStock {
    id: number;
    product_id: number;
    location_id: number;
    product_unit_id: number;
    stock: number;
    last_in_stock: number;
    last_out_stock: number;
    last_buy_price: number;
    average_buy_price: number;
    lowest_buy_price: number;
    highest_buy_price: number;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    created_by?: number;
    updated_by?: number;
    checksum: string;
    product: Product;
    location: Location;
}

export interface Tax {
    id: number;
    entity_id: number;
    name: string;
    rate: number;
    status: 'active' | 'archived';
    created_at?: string;
    updated_at?: string;
}

export interface LoyaltyRewardProduct {
    id?: number;
    product_id: number;
    product_unit_id: number;
    point_needed: number;
    maximum_quantity: number | null;
    product?: {
        id: number;
        name: string;
        sku: string | null;
        barcode: string | null;
        sell_price: number;
    };
    product_unit?: { id: number; name: string };
}

export interface Loyalty {
    id: number;
    entity_id: number;
    code: string;
    name: string;
    description: string | null;
    miniminal_transaction_value: number;
    reward_point: number;
    allow_multiple: boolean;
    status: 'active' | 'in_active' | 'archived';
    reward_products_count?: number;
    reward_products?: LoyaltyRewardProduct[];
    created_at?: string;
    updated_at?: string;
}

export type StockDocumentStatus =
    | 'requested'
    | 'approved'
    | 'rejected'
    | 'cancelled';

export type EmployeeName = {
    id: number;
    first_name: string;
    last_name: string | null;
};

type StockDocumentProduct = {
    id: number;
    name: string;
    sku: string | null;
    barcode: string | null;
    sell_price: number;
};

/** Detail Stok Opname & Penyesuaian Stok (skema identik). */
export interface StockCountDetail {
    id: number;
    product_id: number;
    product_name: string;
    product_sku: string;
    product_unit_id: number;
    product_unit_name: string | null;
    product_category_name: string | null;
    recorded_stock: number;
    counted_stock: number;
    difference_stock: number;
    note: string | null;
    product?: StockDocumentProduct | null;
}

/** Stok Opname & Penyesuaian Stok. */
export interface StockCountDocument {
    id: number;
    code: string;
    location_id: number;
    status: StockDocumentStatus;
    note: string | null;
    auto_approve: boolean;
    local_requested_at: string | null;
    local_approved_at: string | null;
    local_rejected_at: string | null;
    approval_note: string | null;
    rejected_note: string | null;
    recorded_product_count: number;
    difference_product_count: number;
    recorded_stock: number;
    counted_stock: number;
    difference_stock: number;
    details_count?: number;
    location?: { id: number; name: string } | null;
    employee_requested_by?: EmployeeName | null;
    employee_approved_by?: EmployeeName | null;
    employee_rejected_by?: EmployeeName | null;
    product_opname_service_details?: StockCountDetail[];
    product_adjustment_stock_details?: StockCountDetail[];
}

export interface StockTransferDetail {
    id: number;
    product_id: number;
    product_name: string;
    product_sku: string;
    product_unit_name: string | null;
    quantity: number;
    buying_price: number;
    product?: StockDocumentProduct | null;
}

export interface StockTransfer {
    id: number;
    code: string;
    from_location_id: number;
    to_location_id: number;
    status: StockDocumentStatus;
    request_note: string | null;
    approval_note: string | null;
    rejected_note: string | null;
    cancelled_note: string | null;
    local_requested_at: string | null;
    local_approved_at: string | null;
    local_rejected_at: string | null;
    local_cancelled_at: string | null;
    details_count?: number;
    total_quantity?: number | string | null;
    from_location?: { id: number; name: string } | null;
    to_location?: { id: number; name: string } | null;
    employee_requested_by?: EmployeeName | null;
    employee_approved_by?: EmployeeName | null;
    employee_rejected_by?: EmployeeName | null;
    employee_cancelled_by?: EmployeeName | null;
    product_transfer_service_details?: StockTransferDetail[];
}

export type PromoTemplate = 'discount_percentage' | 'discount_fixed';

export interface Promo {
    id: number;
    code: string;
    name: string;
    description: string | null;
    owner_location_id: number;
    start_at: string;
    end_at: string | null;
    status: string;
    owner_location?: { id: number; name: string } | null;
    promo_rule?: {
        minimum_sales_purchase: number | null;
        promo_rule_customer_categories?: {
            customer_category_id: number;
            customer_category_name: string;
        }[];
    } | null;
    promo_reward?: {
        template: PromoTemplate | string;
        reward_amount: number;
        reward_maximum_amount: number | null;
    } | null;
}
