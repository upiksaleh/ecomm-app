export interface ITenant {
    id: string;
    created_at: string;
    updated_at: string;
    data?: Record<string, any>;
    domains?: Array<{
        id: number;
        domain: string;
        tenant_id: string;
        created_at: string;
        updated_at: string;
    }>;
}
