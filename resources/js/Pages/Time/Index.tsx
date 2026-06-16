import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import DateInput from '@/Components/ui/DateInput';
import Empty from '@/Components/ui/Empty';
import Input from '@/Components/ui/Input';
import ListCacheBanner from '@/Components/ui/ListCacheBanner';
import Modal from '@/Components/ui/Modal';
import PageHeader from '@/Components/ui/PageHeader';
import Pagination from '@/Components/ui/Pagination';
import Select from '@/Components/ui/Select';
import Stat from '@/Components/ui/Stat';
import { useOptimisticList } from '@/hooks/useOptimisticList';
import { invalidateListCache } from '@/lib/listCache';
import { routes } from '@/lib/routes';
import { formatMoney, minutesToHuman, recordToSelect } from '@/lib/utils';
import type { Paginated, SharedProps, TimeEntry } from '@/types';

type Props = {
    entries: Paginated<TimeEntry>;
    filters: { client_id?: string; date_from?: string; date_to?: string; is_billable?: string };
    summary: { minutes: string; price: string };
    clients: Record<string, string>;
    tasks: Record<string, string>;
    defaults: { work_date: string; hourly_rate: number };
};

const emptyEntry = (defaults: Props['defaults']) => ({
    task_id: '',
    work_date: defaults.work_date,
    description: '',
    hours: '0',
    minutes: '0',
    hourly_rate: String(defaults.hourly_rate),
    is_billable: true,
});

export default function TimeIndex({ entries: serverEntries, filters, summary, clients, tasks, defaults }: Props) {
    const { list: entries, isStale, fetchList, optimisticRemove } = useOptimisticList('time', filters, serverEntries);
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState<TimeEntry | null>(null);

    const filterForm = useForm({
        client_id: filters.client_id ?? '',
        date_from: filters.date_from ?? '',
        date_to: filters.date_to ?? '',
        is_billable: filters.is_billable ?? '',
    });

    const form = useForm(emptyEntry(defaults));

    const applyFilters = (e: React.FormEvent) => {
        e.preventDefault();
        fetchList(routes.time.index(), filterForm.data);
    };

    const openCreate = () => {
        setEditing(null);
        form.setData(emptyEntry(defaults));
        form.clearErrors();
        setModalOpen(true);
    };

    const openEdit = (entry: TimeEntry) => {
        setEditing(entry);
        form.setData({
            task_id: String(entry.task_id),
            work_date: entry.work_date,
            description: entry.description ?? '',
            hours: String(entry.hours),
            minutes: String(entry.minutes),
            hourly_rate: String(entry.hourly_rate),
            is_billable: entry.is_billable,
        });
        form.clearErrors();
        setModalOpen(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = {
            ...form.data,
            task_id: Number(form.data.task_id),
            hours: Number(form.data.hours),
            minutes: Number(form.data.minutes),
            hourly_rate: Number(form.data.hourly_rate),
        };
        if (editing) {
            form.transform(() => payload);
            form.put(routes.time.update(editing.id), {
                onSuccess: () => {
                    invalidateListCache('time');
                    setModalOpen(false);
                },
            });
        } else {
            form.transform(() => payload);
            form.post(routes.time.store(), {
                onSuccess: () => {
                    invalidateListCache('time');
                    setModalOpen(false);
                },
            });
        }
    };

    const destroy = (entry: TimeEntry) => {
        if (!confirm('Obrisati unos vremena?')) return;
        optimisticRemove(entry.id);
        router.delete(routes.time.destroy(entry.id), {
            onError: () => invalidateListCache('time'),
        });
    };

    return (
        <AppLayout>
            <Head title="Vrijeme" />

            <PageHeader title="Vrijeme" subtitle="Evidencija rada i sati">
                <Button onClick={openCreate}>+ Novi unos</Button>
            </PageHeader>

            <ListCacheBanner show={isStale} />

            <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat label="Ukupno sati (filter)" value={summary.minutes} color="amber" />
                <Stat label="Ukupna cijena (filter)" value={summary.price} color="green" />
                <Stat label="Unosa" value={entries.meta.total} color="neutral" />
            </div>

            <Card className="mb-6">
                <form onSubmit={applyFilters} className="grid gap-4 sm:grid-cols-5">
                    <Select
                        label="Klijent"
                        value={filterForm.data.client_id}
                        onChange={(e) => filterForm.setData('client_id', e.target.value)}
                        options={recordToSelect(clients)}
                        placeholder="Svi"
                    />
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
                        label="Naplativo"
                        value={filterForm.data.is_billable}
                        onChange={(e) => filterForm.setData('is_billable', e.target.value)}
                        placeholder="Svi"
                    >
                        <option value="1">Da</option>
                        <option value="0">Ne</option>
                    </Select>
                    <div className="flex items-end gap-2">
                        <Button type="submit">Filtriraj</Button>
                        <Button type="button" variant="secondary" onClick={() => fetchList(routes.time.index(), {})}>
                            Reset
                        </Button>
                    </div>
                </form>
            </Card>

            <Card padding="p-0">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th className="px-4 py-3 text-left">Datum</th>
                            <th className="px-4 py-3 text-left">Klijent</th>
                            <th className="px-4 py-3 text-left">Task</th>
                            <th className="px-4 py-3 text-left">Opis</th>
                            <th className="px-4 py-3 text-left">Vrijeme</th>
                            <th className="px-4 py-3 text-right">Cijena</th>
                            <th className="px-4 py-3 text-right">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        {entries.data.length === 0 ? (
                            <tr>
                                <td colSpan={7}>
                                    <Empty text="Nema unosa vremena.">
                                        <Button size="sm" onClick={openCreate}>
                                            + Dodaj unos
                                        </Button>
                                    </Empty>
                                </td>
                            </tr>
                        ) : (
                            entries.data.map((e) => (
                                <tr key={e.id}>
                                    <td className="px-4 py-3">{e.work_date_display}</td>
                                    <td className="px-4 py-3">{e.client?.name ?? '-'}</td>
                                    <td className="px-4 py-3">
                                        {e.task ? (
                                            <Link href={routes.tasks.show(e.task.id)} className="link-accent">
                                                {e.task.title}
                                            </Link>
                                        ) : (
                                            '-'
                                        )}
                                    </td>
                                    <td className="px-4 py-3">{e.description || '-'}</td>
                                    <td className="px-4 py-3">{minutesToHuman(e.total_minutes)}</td>
                                    <td className="px-4 py-3 text-right">
                                        {formatMoney(e.total_price, e.client?.currency)}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button size="sm" variant="secondary" onClick={() => openEdit(e)}>
                                                Uredi
                                            </Button>
                                            <Button size="sm" variant="danger" onClick={() => destroy(e)}>
                                                Obriši
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </Card>

            <Pagination links={entries.links as { url: string | null; label: string; active: boolean }[]} meta={entries.meta} />

            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Uredi unos' : 'Novi unos vremena'}>
                <form onSubmit={submit} className="space-y-4">
                    <Select
                        label="Task *"
                        value={form.data.task_id}
                        onChange={(e) => form.setData('task_id', e.target.value)}
                        options={recordToSelect(tasks)}
                        placeholder="Odaberi task"
                        error={form.errors.task_id}
                    />
                    <DateInput
                        label="Datum *"
                        value={form.data.work_date}
                        onChange={(e) => form.setData('work_date', e.target.value)}
                        error={form.errors.work_date}
                    />
                    <Input
                        label="Opis"
                        value={form.data.description}
                        onChange={(e) => form.setData('description', e.target.value)}
                    />
                    <div className="grid gap-4 sm:grid-cols-3">
                        <Input
                            label="Sati"
                            type="number"
                            min="0"
                            value={form.data.hours}
                            onChange={(e) => form.setData('hours', e.target.value)}
                            error={form.errors.hours}
                        />
                        <Input
                            label="Minute"
                            type="number"
                            min="0"
                            max="59"
                            value={form.data.minutes}
                            onChange={(e) => form.setData('minutes', e.target.value)}
                            error={form.errors.minutes}
                        />
                        <Input
                            label="Satnica"
                            type="number"
                            step="0.01"
                            value={form.data.hourly_rate}
                            onChange={(e) => form.setData('hourly_rate', e.target.value)}
                            error={form.errors.hourly_rate}
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
