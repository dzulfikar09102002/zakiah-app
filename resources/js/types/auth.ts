import { Employee } from "@/lib/model";

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    entity: Entity;
    employee: Employee;
    [key: string]: unknown;
};

export type Entity = {
    id: number;
    code: string;
    initial: string;
    name: string;
    image_url: string;
    icon_image_url: string | null;
    phone_number: string;
    phone_number_country_code: string;
    email: string;
    website: string | null;
    full_address: string;
    postal_code: string;
    city: string;
    province: string;
    country: string;
    timezone: string;
    status: 'active' | 'archived'; // Gunakan union type jika status sudah pasti
    deleted_at: string | null; // Biasanya berupa ISO Date string atau null
    created_at: string;        // ISO Date string
    updated_at: string;        // ISO Date string
    laravel_through_key: number;
}

export type Auth = {
    user: User;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
