import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import TaskBillingFields from '@/Components/TaskBillingFields';
import AppLayout from '@/Layouts/AppLayout';
import Badge from '@/Components/ui/Badge';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import ClickableTableRow from '@/Components/ui/ClickableTableRow';
import DateInput from '@/Components/ui/DateInput';
import Empty from '@/Components/ui/Empty';
import Input from '@/Components/ui/Input';
import ListCacheBanner from '@/Components/ui/ListCacheBanner';
import Modal from '@/Components/ui/Modal';
import PageHeader from '@/Components/ui/PageHeader';
import Pagination from '@/Components/ui/Pagination';
import Select from '@/Components/ui/Select';
import { useOptimisticList } from '@/hooks/useOptimisticList';
import { invalidateListCache } from '@/lib/listCache';
import { routes } from '@/lib/routes';
import { formatMoney, optionsToSelect, recordToSelect } from '@/lib/utils';
import type { Paginated, SharedProps, Task } from '@/types';

type Props = {
    tasks: Paginated<Task>;
    filters: Record<string, string | undefined>;
    clients: Record<string, string>;
    filterProjects: Record<string, string>;
    defaults: { task_date: string; hourly_rate: number };
};

const emptyTask = (defaults: Props['defaults']) => ({
    client_id: '',
    project_id: '',
    title: '',
    description: '',
    status: 'novo',
    priority: 'normalan',
    task_date: defaults.task_date,
    due_date: '',
    billing_type: 'po_satu',
    hourly_rate: String(defaults.hourly_rate),
    fixed_price: '0',
    hours: '0',
    minutes: '0',
    is_billable: true,
    payment_status: 'za_naplatu',
    internal_note: '',
});

export default function TasksIndex({ tasks: serverTasks, filters, clients, filterProjects, defaults }: Props) {
    const { options } = usePage<SharedProps>().props;
    const { list: tasks, isStale, fetchList, optimisticRemove } = useOptimisticList('tasks', filters, serverTasks);
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState<Task | null>(null);

    const filterForm = useForm({
        q: filters.q ?? '',
        client_id: filters.client_id ?? '',
        project_id: filters.project_id ?? '',
        status: filters.status ?? '',
        priority: filters.priority ?? '',
        billing_type: filters.billing_type ?? '',
        payment_status: filters.payment_status ?? '',
        is_billable: filters.is_billable ?? '',
        date_from: filters.date_from ?? '',
        date_to: filters.date_to ?? '',
        month: filters.month ?? '',
        year: filters.year ?? '',
        showArchived: filters.showArchived ? '1' : '',
    });

    const form = useForm(emptyTask(defaults));

    const applyFilters = (e?: React.FormEvent) => {
        e?.preventDefault();
        const data = { ...filterForm.data };
        if (!data.showArchived) delete (data as Record<string, string>).showArchived;
        fetchList(routes.tasks.index(), data);
    };

    const onClientFilterChange = (clientId: string) => {
        filterForm.setData({ ...filterForm.data, client_id: clientId, project_id: '' });
        fetchList(routes.tasks.index(), { ...filterForm.data, client_id: clientId, project_id: '' });
    };

    const openCreate = () => {
        setEditing(null);
        form.setData(emptyTask(defaults));
        if (filterForm.data.client_id) form.setData('client_id', filterForm.data.client_id);
        form.clearErrors();
        setModalOpen(true);
    };

    const openEdit = (task: Task) => {
        setEditing(task);
        form.setData({
            client_id: String(task.client_id),
            project_id: task.project_id ? String(task.project_id) : '',
            title: task.title,
            description: task.description ?? '',
            status: task.status,
            priority: task.priority,
            task_date: task.task_date ?? '',
            due_date: task.due_date ?? '',
            billing_type: task.billing_type,
            hourly_rate: String(task.hourly_rate),
            fixed_price: String(task.fixed_price),
            hours: '0',
            minutes: '0',
            is_billable: task.is_billable,
            payment_status: task.payment_status,
            internal_note: task.internal_note ?? '',
        });
        form.clearErrors();
        setModalOpen(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = {
            ...form.data,
            client_id: Number(form.data.client_id),
            project_id: form.data.project_id ? Number(form.data.project_id) : '',
            hourly_rate: Number(form.data.hourly_rate),
            fixed_price: Number(form.data.fixed_price),
            hours: Number(form.data.hours),
            minutes: Number(form.data.minutes),
        };
        if (editing) {
            form.transform(() => payload);
            form.put(routes.tasks.update(editing.id), {
                onSuccess: () => {
                    invalidateListCache('tasks');
                    setModalOpen(false);
                },
            });
        } else {
            form.transform(() => payload);
            form.post(routes.tasks.store(), {
                onSuccess: () => {
                    invalidateListCache('tasks');
                    setModalOpen(false);
                },
            });
        }
    };

    const destroy = (task: Task) => {
        if (!confirm(`Obrisati task "${task.title}"?`)) return;
        optimisticRemove(task.id);
        router.delete(routes.tasks.destroy(task.id), {
            onError: () => invalidateListCache('tasks'),
        });
    };

    const duplicate = (task: Task) =>
        router.post(routes.tasks.duplicate(task.id), {}, { onSuccess: () => invalidateListCache('tasks') });

    const toggleArchive = (task: Task) =>
        router.post(routes.tasks.archive(task.id), {}, { onSuccess: () => invalidateListCache('tasks') });

    const years = Array.from({ length: 5 }, (_, i) => String(new Date().getFullYear() - i));

    return (
        <AppLayout>
            <Head title="Taskovi" />

            <PageHeader title="Taskovi" subtitle="Pregled i upravljanje poslovima">
                <Button onClick={openCreate}>+ Dodaj novi task</Button>
            </PageHeader>

            <ListCacheBanner show={isStale} />

            <Card className="mb-6">
                <form onSubmit={applyFilters} className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-4">
                        <Input
                            label="Pretraga"
                            value={filterForm.data.q}
                            onChange={(e) => filterForm.setData('q', e.target.value)}
                            placeholder="Naslov, opis..."
                        />
                        <Select
                            label="Klijent"
                            value={filterForm.data.client_id}
                            onChange={(e) => onClientFilterChange(e.target.value)}
                            options={recordToSelect(clients)}
                            placeholder="Svi"
                        />
                        <Select
                            label="Projekat"
                            value={filterForm.data.project_id}
                            onChange={(e) => filterForm.setData('project_id', e.target.value)}
                            options={recordToSelect(filterProjects)}
                            placeholder="Svi"
                        />
                        <Select
                            label="Status"
                            value={filterForm.data.status}
                            onChange={(e) => filterForm.setData('status', e.target.value)}
                            options={optionsToSelect(options.taskStatuses)}
                            placeholder="Svi"
                        />
                        <Select
                            label="Prioritet"
                            value={filterForm.data.priority}
                            onChange={(e) => filterForm.setData('priority', e.target.value)}
                            options={optionsToSelect(options.taskPriorities)}
                            placeholder="Svi"
                        />
                        <Select
                            label="Tip naplate"
                            value={filterForm.data.billing_type}
                            onChange={(e) => filterForm.setData('billing_type', e.target.value)}
                            options={optionsToSelect(options.taskBillingTypes)}
                            placeholder="Svi"
                        />
                        <Select
                            label="Status plaćanja"
                            value={filterForm.data.payment_status}
                            onChange={(e) => filterForm.setData('payment_status', e.target.value)}
                            options={optionsToSelect(options.paymentStatuses)}
                            placeholder="Svi"
                        />
                        <Select
                            label="Naplativo"
                            value={filterForm.data.is_billable}
                            onChange={(e) => filterForm.setData('is_billable', e.target.value)}
                            placeholder="Svi"
                        >
                            <option value="1">Da</option>
                            <option value="0">Ne</option>
                        </Select>
                        <DateInput
                            label="Datum od"
                            value={filterForm.data.date_from}
                            onChange={(e) => filterForm.setData('date_from', e.target.value)}
                        />
                        <DateInput
                            label="Datum do"
                            value={filterForm.data.date_to}
                            onChange={(e) => filterForm.setData('date_to', e.target.value)}
                        />
                        <Select
                            label="Mjesec"
                            value={filterForm.data.month}
                            onChange={(e) => filterForm.setData('month', e.target.value)}
                            options={optionsToSelect(options.months)}
                            placeholder="Svi"
                        />
                        <Select
                            label="Godina"
                            value={filterForm.data.year}
                            onChange={(e) => filterForm.setData('year', e.target.value)}
                            options={years.map((y) => ({ value: y, label: y }))}
                            placeholder="Sve"
                        />
                    </div>
                    <div className="flex flex-wrap items-center gap-4">
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={!!filterForm.data.showArchived}
                                onChange={(e) => filterForm.setData('showArchived', e.target.checked ? '1' : '')}
                                className="checkbox-base"
                            />
                            Prikaži arhivirane
                        </label>
                        <Button type="submit">Filtriraj</Button>
                        <Button type="button" variant="secondary" onClick={() => fetchList(routes.tasks.index(), {})}>
                            Reset
                        </Button>
                    </div>
                </form>
            </Card>

            <Card padding="p-0">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th className="px-4 py-3 text-left">Task</th>
                            <th className="px-4 py-3 text-left">Klijent</th>
                            <th className="px-4 py-3 text-left">Datum</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-left">Naplata</th>
                            <th className="px-4 py-3 text-right">Cijena</th>
                            <th className="px-4 py-3 text-right">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        {tasks.data.length === 0 ? (
                            <tr>
                                <td colSpan={7}>
                                    <Empty text="Nema taskova.">
                                        <Button size="sm" onClick={openCreate}>
                                            + Dodaj task
                                        </Button>
                                    </Empty>
                                </td>
                            </tr>
                        ) : (
                            tasks.data.map((t) => (
                                <ClickableTableRow key={t.id} href={routes.tasks.show(t.id)}>
                                    <td className="px-4 py-3 font-medium text-neutral-100">
                                        {t.title}
                                        {t.archived_at && (
                                            <span className="ml-2 text-xs font-normal text-neutral-400">(arhiva)</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">{t.client?.name ?? '-'}</td>
                                    <td className="px-4 py-3">{t.task_date_display ?? '-'}</td>
                                    <td className="px-4 py-3">
                                        <Badge value={t.status} map={options.taskStatuses} />
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge value={t.payment_status} map={options.paymentStatuses} />
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        {formatMoney(t.total_price, t.client?.currency)}
                                    </td>
                                    <td className="px-4 py-3" data-row-ignore>
                                        <div className="flex justify-end gap-1">
                                            <Button size="sm" variant="secondary" onClick={() => openEdit(t)}>
                                                Uredi
                                            </Button>
                                            <Button size="sm" variant="ghost" onClick={() => duplicate(t)}>
                                                Kopija
                                            </Button>
                                            <Button size="sm" variant="ghost" onClick={() => toggleArchive(t)}>
                                                {t.archived_at ? 'Vrati' : 'Arhiva'}
                                            </Button>
                                            <Button size="sm" variant="danger" onClick={() => destroy(t)}>
                                                Obriši
                                            </Button>
                                        </div>
                                    </td>
                                </ClickableTableRow>
                            ))
                        )}
                    </tbody>
                </table>
            </Card>

            <Pagination links={tasks.links as { url: string | null; label: string; active: boolean }[]} meta={tasks.meta} />

            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Uredi task' : 'Novi task'} maxWidth="max-w-2xl">
                <form onSubmit={submit} className="space-y-4">
                    <Select
                        label="Klijent *"
                        value={form.data.client_id}
                        onChange={(e) => form.setData('client_id', e.target.value)}
                        options={recordToSelect(clients)}
                        placeholder="Odaberi klijenta"
                        error={form.errors.client_id}
                    />
                    <Input
                        label="Naslov *"
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        error={form.errors.title}
                    />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Select
                            label="Status"
                            value={form.data.status}
                            onChange={(e) => form.setData('status', e.target.value)}
                            options={optionsToSelect(options.taskStatuses)}
                        />
                        <Select
                            label="Prioritet"
                            value={form.data.priority}
                            onChange={(e) => form.setData('priority', e.target.value)}
                            options={optionsToSelect(options.taskPriorities)}
                        />
                        <DateInput
                            label="Datum"
                            value={form.data.task_date}
                            onChange={(e) => form.setData('task_date', e.target.value)}
                        />
                        <DateInput
                            label="Rok"
                            value={form.data.due_date}
                            onChange={(e) => form.setData('due_date', e.target.value)}
                        />
                        <Select
                            label="Status plaćanja"
                            value={form.data.payment_status}
                            onChange={(e) => form.setData('payment_status', e.target.value)}
                            options={optionsToSelect(options.paymentStatuses)}
                        />
                        <TaskBillingFields
                            billingType={form.data.billing_type}
                            onBillingTypeChange={(value) => form.setData('billing_type', value)}
                            billingTypeOptions={options.taskBillingTypes}
                            hourlyRate={form.data.hourly_rate}
                            onHourlyRateChange={(value) => form.setData('hourly_rate', value)}
                            fixedPrice={form.data.fixed_price}
                            onFixedPriceChange={(value) => form.setData('fixed_price', value)}
                            hours={form.data.hours}
                            onHoursChange={(value) => form.setData('hours', value)}
                            minutes={form.data.minutes}
                            onMinutesChange={(value) => form.setData('minutes', value)}
                            errors={form.errors}
                            loggedMinutes={editing?.logged_minutes}
                            isEditing={!!editing}
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.data.is_billable}
                            onChange={(e) => form.setData('is_billable', e.target.checked)}
                            className="checkbox-base"
                        />
                        Naplativo
                    </label>
                    <div>
                        <label className="mb-1.5 block text-sm font-medium">Opis</label>
                        <textarea
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                            rows={2}
                            className="field-base"
                        />
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="secondary" onClick={() => setModalOpen(false)}>
                            Otkaži
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {editing ? 'Sačuvaj' : 'Dodaj'}
                        </Button>
                    </div>
                </form>
            </Modal>
        </AppLayout>
    );
}
