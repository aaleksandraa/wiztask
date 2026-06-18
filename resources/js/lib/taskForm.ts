import type { Task } from '@/types';

export function taskToFormData(task: Task) {
    return {
        client_id: task.client_id,
        project_id: task.project_id ? String(task.project_id) : '',
        title: task.title,
        description: task.description ?? '',
        internal_note: task.internal_note ?? '',
        status: task.status,
        priority: task.priority,
        task_date: task.task_date ?? '',
        due_date: task.due_date ?? '',
        billing_type: task.billing_type,
        hourly_rate: String(task.hourly_rate),
        fixed_price: String(task.fixed_price),
        is_billable: task.is_billable,
        payment_status: task.payment_status,
        hours: '0',
        minutes: '0',
    };
}

export function taskFormPayload(data: ReturnType<typeof taskToFormData>) {
    return {
        ...data,
        client_id: Number(data.client_id),
        project_id: data.project_id ? Number(data.project_id) : '',
        hourly_rate: Number(data.hourly_rate),
        fixed_price: Number(data.fixed_price),
        hours: Number(data.hours),
        minutes: Number(data.minutes),
    };
}

export function projectsForClient(
    projectsByClient: Record<string, Record<string, string>>,
    clientId: string | number,
): Record<string, string> {
    if (!clientId) return {};
    const key = String(clientId);

    return projectsByClient[key] ?? {};
}
