import type { Project } from '@/types';

export function projectToFormData(project: Project) {
    return {
        client_id: project.client_id,
        name: project.name,
        description: project.description ?? '',
        status: project.status,
        start_date: project.start_date ?? '',
        due_date: project.due_date ?? '',
        billing_type: project.billing_type,
        fixed_price: String(project.fixed_price),
        currency: project.currency,
        note: project.note ?? '',
    };
}

export function projectFormPayload(data: ReturnType<typeof projectToFormData>) {
    return {
        ...data,
        client_id: Number(data.client_id),
        fixed_price: Number(data.fixed_price),
    };
}
