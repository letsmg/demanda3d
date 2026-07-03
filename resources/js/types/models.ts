export type Client = {
    id: number;
    tenant_id: number;
    first_name: string;
    last_name: string;
    display_name: string | null;
    name: string | null;
    doc_type: string;
    doc: string;
    address: string;
    number: string;
    state: string;
    zipcode: string;
    city: string;
    phone1: string;
    phone2: string;
    contact1: string | null;
    contact2: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};

export type Order = {
    id: number;
    client_id: number;
    order_date: string;
    delivery_date: string;
    price: string | number;
    contracted_description: string;
    created_at: string;
    updated_at: string;
};

export type Input = {
    id: number;
    tenant_id: number;
    supplier_id: number;
    description: string;
    brand: string;
    purchase_date: string;
    quantity: number;
    shipping_cost: string | number;
    cost_value: string | number;
    created_at: string;
    updated_at: string;
};

export type Product = {
    id: number;
    tenant_id: number;
    name: string;
    description: string | null;
    sale_price: string | number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};
