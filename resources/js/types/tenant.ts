export interface ITenant {
    id: string;
    created_at: string;
    updated_at: string;
    data?: Record<string, any>;
    products_count?: number;
    customers_count?: number;
    domains?: Array<{
        id: number;
        domain: string;
        tenant_id: string;
        created_at: string;
        updated_at: string;
    }>;
}
