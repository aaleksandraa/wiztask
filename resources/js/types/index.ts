export type Attachment = {
    id: number;
    original_name: string;
    url: string;
    download_url: string;
    mime_type: string;
    size: number;
    human_size: string;
    is_image: boolean;
    category: string;
    description: string | null;
    created_at: string;
};

export type Paginated<T> = {
    data: T[];
    meta: { current_page: number; last_page: number; per_page: number; total: number };
    links: unknown[];
};

export type SharedProps = {
    auth: { user: { id: number; name: string; email: string } | null };
    flash: { success?: string; error?: string };
    appName: string;
    options: {
        currencies: string[];
        clientStatuses: Record<string, string>;
        projectStatuses: Record<string, string>;
        projectBillingTypes: Record<string, string>;
        taskStatuses: Record<string, string>;
        taskPriorities: Record<string, string>;
        taskBillingTypes: Record<string, string>;
        paymentStatuses: Record<string, string>;
        attachmentCategories: Record<string, string>;
        months: Record<string, string>;
    };
    settings: {
        defaultHourlyRate: number;
        defaultCurrency: string;
        allowedFileTypes: string[];
    };
};

export type Client = {
    id: number;
    name: string;
    contact_person?: string;
    email?: string;
    phone?: string;
    website?: string;
    city?: string;
    country?: string;
    address?: string;
    note?: string;
    status: string;
    default_hourly_rate: number;
    currency: string;
    projects_count?: number;
    tasks_count?: number;
};

export type Project = {
    id: number;
    client_id: number;
    name: string;
    description?: string;
    status: string;
    start_date?: string | null;
    start_date_display?: string;
    due_date?: string | null;
    due_date_display?: string;
    billing_type: string;
    fixed_price: number;
    currency: string;
    note?: string;
    tasks_count?: number;
    client?: { id: number; name: string; currency: string };
};

export type Task = {
    id: number;
    client_id: number;
    project_id?: number | null;
    title: string;
    description?: string;
    status: string;
    priority: string;
    task_date?: string | null;
    task_date_display?: string;
    due_date?: string | null;
    due_date_display?: string;
    billing_type: string;
    hourly_rate: number;
    fixed_price: number;
    total_price: number;
    logged_minutes?: number | null;
    is_billable: boolean;
    payment_status: string;
    internal_note?: string;
    archived_at?: string | null;
    total_minutes?: number;
    due_overdue?: boolean;
    client?: { id: number; name: string; currency: string; default_hourly_rate?: number };
    project?: { id: number; name: string };
};

export type TimeEntry = {
    id: number;
    client_id: number;
    project_id?: number | null;
    task_id: number;
    work_date: string;
    work_date_display: string;
    description?: string;
    hours: number;
    minutes: number;
    total_minutes: number;
    hourly_rate: number;
    total_price: number;
    is_billable: boolean;
    client?: { id: number; name: string; currency: string };
    task?: { id: number; title: string };
};
