import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import AttachmentPanel from '@/Components/AttachmentPanel';
import TaskBillingFields from '@/Components/TaskBillingFields';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import DateInput from '@/Components/ui/DateInput';
import EditableTextarea from '@/Components/ui/EditableTextarea';
import Empty from '@/Components/ui/Empty';
import Input from '@/Components/ui/Input';
import Modal from '@/Components/ui/Modal';
import PageHeader from '@/Components/ui/PageHeader';
import Select from '@/Components/ui/Select';
import Stat from '@/Components/ui/Stat';
import { routes } from '@/lib/routes';
import { taskFormPayload, taskToFormData } from '@/lib/taskForm';
import { formatMoney, label, minutesToHuman, optionsToSelect } from '@/lib/utils';
import type { Attachment, SharedProps, Task, TimeEntry } from '@/types';

type Props = {
    task: Task;
    timeEntries: TimeEntry[];
    stats: { totalMinutes: string; totalPrice: string };
    attachments: Attachment[];
    defaults: { work_date: string; hourly_rate: number };
};

export default function TasksShow({ task, timeEntries, stats, attachments, defaults }: Props) {
    const { options } = usePage<SharedProps>().props;
    const [timeModal, setTimeModal] = useState(false);
    const [editingEntry, setEditingEntry] = useState<TimeEntry | null>(null);

    const editForm = useForm(taskToFormData(task));

    const timeForm = useForm({
        work_date: defaults.work_date,
        description: '',
        hours: '0',
        minutes: '0',
        hourly_rate: String(defaults.hourly_rate),
        is_billable: true,
    });

    useEffect(() => {
        const data = taskToFormData(task);
        editForm.setDefaults(data);
        editForm.reset();
        // eslint-disable-next-line react-hooks/exhaustive-deps -- sync when server task changes
    }, [task.id, task.title, task.description, task.internal_note, task.status, task.payment_status]);

    const saveEdits = (options?: { preserveScroll?: boolean }) => {
        if (!editForm.isDirty) return;

        editForm.transform(() => taskFormPayload(editForm.data));
        editForm.put(routes.tasks.update(task.id), {
            preserveScroll: options?.preserveScroll ?? true,
        });
    };

    const updateStatus = (status: string) => {
        editForm.setData('status', status);
        router.patch(
            routes.tasks.status(task.id),
            { status },
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (status === 'zavrseno' && task.billing_type === 'po_satu' && timeEntries.length === 0) {
                        openTimeCreate();
                    }
                },
            },
        );
    };

    const updatePayment = (payment_status: string) => {
        editForm.setData('payment_status', payment_status);
        router.patch(routes.tasks.paymentStatus(task.id), { payment_status }, { preserveScroll: true });
    };

    const openTimeCreate = () => {
        timeForm.setData({
            work_date: defaults.work_date,
            description: '',
            hours: '0',
            minutes: '0',
            hourly_rate: String(defaults.hourly_rate),
            is_billable: true,
        });
        timeForm.clearErrors();
        setTimeModal(true);
    };

    const openTimeEdit = (entry: TimeEntry) => {
        setEditingEntry(entry);
        timeForm.setData({
            work_date: entry.work_date,
            description: entry.description ?? '',
            hours: String(entry.hours),
            minutes: String(entry.minutes),
            hourly_rate: String(entry.hourly_rate),
            is_billable: entry.is_billable,
        });
        timeForm.clearErrors();
        setTimeModal(true);
    };

    const submitTime = (e: React.FormEvent) => {
        e.preventDefault();
        timeForm.transform(() => ({
            ...timeForm.data,
            hours: Number(timeForm.data.hours),
            minutes: Number(timeForm.data.minutes),
            hourly_rate: Number(timeForm.data.hourly_rate),
        }));
        if (editingEntry) {
            timeForm.put(routes.tasks.time.update(task.id, editingEntry.id), {
                onSuccess: () => setTimeModal(false),
            });
        } else {
            timeForm.post(routes.tasks.time.store(task.id), {
                onSuccess: () => setTimeModal(false),
            });
        }
    };

    const destroyTime = (entry: TimeEntry) => {
        if (!confirm('Obrisati unos vremena?')) return;
        router.delete(routes.tasks.time.destroy(task.id, entry.id));
    };

    return (
        <AppLayout>
            <Head title={editForm.data.title || task.title} />

            <PageHeader title={task.client?.name ?? 'Task'} subtitle="Uređivanje taska">
                <Button variant="secondary" href={routes.tasks.index()}>
                    ← Nazad
                </Button>
                {task.client && (
                    <Button variant="secondary" href={routes.clients.show(task.client.id)}>
                        Klijent
                    </Button>
                )}
                {task.project && (
                    <Button variant="secondary" href={routes.projects.show(task.project.id)}>
                        Projekat
                    </Button>
                )}
                {editForm.isDirty && (
                    <>
                        <Button variant="secondary" onClick={() => editForm.reset()}>
                            Poništi
                        </Button>
                        <Button onClick={() => saveEdits()} disabled={editForm.processing}>
                            {editForm.processing ? 'Spremanje...' : 'Sačuvaj promjene'}
                        </Button>
                    </>
                )}
            </PageHeader>

            <div className="mb-4">
                <Input
                    label="Naslov taska"
                    value={editForm.data.title}
                    onChange={(e) => editForm.setData('title', e.target.value)}
                    error={editForm.errors.title}
                    className="text-lg font-semibold"
                />
            </div>

            <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat label="Ukupno sati" value={stats.totalMinutes} color="amber" />
                <Stat label="Ukupna cijena" value={stats.totalPrice} color="green" />
                <Stat label="Datum" value={task.task_date_display ?? '-'} color="neutral" />
                <Stat label="Rok" value={task.due_date_display ?? '-'} color="neutral" />
            </div>

            <div className="mb-6 grid gap-4 lg:grid-cols-2">
                <Card title="Detalji">
                    <dl className="mb-4 grid grid-cols-3 gap-y-3 text-sm">
                        <dt className="text-neutral-500">Klijent</dt>
                        <dd className="col-span-2">
                            {task.client ? (
                                <Link href={routes.clients.show(task.client.id)} className="link-accent">
                                    {task.client.name}
                                </Link>
                            ) : (
                                '-'
                            )}
                        </dd>
                        <dt className="text-neutral-500">Projekat</dt>
                        <dd className="col-span-2">
                            {task.project ? (
                                <Link href={routes.projects.show(task.project.id)} className="link-accent">
                                    {task.project.name}
                                </Link>
                            ) : (
                                '-'
                            )}
                        </dd>
                        <dt className="text-neutral-500">Tip naplate</dt>
                        <dd className="col-span-2">{label(options.taskBillingTypes, task.billing_type)}</dd>
                        <dt className="text-neutral-500">Naplativo</dt>
                        <dd className="col-span-2">{task.is_billable ? 'Da' : 'Ne'}</dd>
                    </dl>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Select
                            label="Status"
                            value={editForm.data.status}
                            onChange={(e) => updateStatus(e.target.value)}
                            options={optionsToSelect(options.taskStatuses)}
                        />
                        <Select
                            label="Status plaćanja"
                            value={editForm.data.payment_status}
                            onChange={(e) => updatePayment(e.target.value)}
                            options={optionsToSelect(options.paymentStatuses)}
                        />
                        <Select
                            label="Prioritet"
                            value={editForm.data.priority}
                            onChange={(e) => editForm.setData('priority', e.target.value)}
                            options={optionsToSelect(options.taskPriorities)}
                        />
                        <DateInput
                            label="Datum"
                            value={editForm.data.task_date}
                            onChange={(e) => editForm.setData('task_date', e.target.value)}
                            error={editForm.errors.task_date}
                        />
                        <DateInput
                            label="Rok"
                            value={editForm.data.due_date}
                            onChange={(e) => editForm.setData('due_date', e.target.value)}
                            error={editForm.errors.due_date}
                        />
                        <TaskBillingFields
                            billingType={editForm.data.billing_type}
                            onBillingTypeChange={(value) => editForm.setData('billing_type', value)}
                            billingTypeOptions={options.taskBillingTypes}
                            hourlyRate={editForm.data.hourly_rate}
                            onHourlyRateChange={(value) => editForm.setData('hourly_rate', value)}
                            fixedPrice={editForm.data.fixed_price}
                            onFixedPriceChange={(value) => editForm.setData('fixed_price', value)}
                            hours={editForm.data.hours}
                            onHoursChange={(value) => editForm.setData('hours', value)}
                            minutes={editForm.data.minutes}
                            onMinutesChange={(value) => editForm.setData('minutes', value)}
                            errors={editForm.errors}
                            loggedMinutes={task.logged_minutes ?? task.total_minutes}
                            isEditing
                        />
                    </div>
                    <label className="mt-4 flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={editForm.data.is_billable}
                            onChange={(e) => editForm.setData('is_billable', e.target.checked)}
                            className="checkbox-base"
                        />
                        Naplativo
                    </label>
                </Card>

                <Card
                    title="Opis i napomene"
                    action={
                        editForm.isDirty ? (
                            <Button size="sm" onClick={() => saveEdits()} disabled={editForm.processing}>
                                Sačuvaj
                            </Button>
                        ) : null
                    }
                >
                    <div className="space-y-4">
                        <EditableTextarea
                            label="Opis taska"
                            value={editForm.data.description}
                            onChange={(value) => editForm.setData('description', value)}
                            onBlurSave={() => saveEdits()}
                            error={editForm.errors.description}
                            hint="Izmjene se automatski spremaju pri izlasku iz polja."
                            rows={6}
                            placeholder="Opis posla, zahtjeva, napomene za klijenta..."
                        />
                        <EditableTextarea
                            label="Interna napomena"
                            value={editForm.data.internal_note}
                            onChange={(value) => editForm.setData('internal_note', value)}
                            onBlurSave={() => saveEdits()}
                            error={editForm.errors.internal_note}
                            hint="Vidljivo samo interno."
                            rows={4}
                            placeholder="Interne bilješke..."
                        />
                    </div>
                </Card>
            </div>

            <Card
                title={task.billing_type === 'po_satu' ? 'Unosi vremena (obračun po satu)' : 'Unosi vremena'}
                padding="p-0"
                className="mb-6"
            >
                {task.billing_type === 'po_satu' && timeEntries.length === 0 && (
                    <div className="border-b border-amber-500/30 bg-amber-500/10 px-5 py-3 text-sm text-amber-200">
                        Task se naplaćuje po satu — unesite broj sati kad završite posao ili odmah ako znate trajanje.
                    </div>
                )}
                <div className="flex justify-end border-b border-white/[0.06] px-5 py-3">
                    <Button size="sm" onClick={() => { setEditingEntry(null); openTimeCreate(); }}>
                        + Dodaj vrijeme
                    </Button>
                </div>
                <table className="data-table">
                    <thead>
                        <tr>
                            <th className="px-4 py-3 text-left">Datum</th>
                            <th className="px-4 py-3 text-left">Opis</th>
                            <th className="px-4 py-3 text-left">Vrijeme</th>
                            <th className="px-4 py-3 text-right">Cijena</th>
                            <th className="px-4 py-3 text-right">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        {timeEntries.length === 0 ? (
                            <tr>
                                <td colSpan={5}>
                                    <Empty text="Nema unosa vremena.">
                                        <Button size="sm" onClick={() => { setEditingEntry(null); openTimeCreate(); }}>
                                            + Dodaj vrijeme
                                        </Button>
                                    </Empty>
                                </td>
                            </tr>
                        ) : (
                            timeEntries.map((e) => (
                                <tr key={e.id}>
                                    <td className="px-4 py-3">{e.work_date_display}</td>
                                    <td className="px-4 py-3">{e.description || '-'}</td>
                                    <td className="px-4 py-3">{minutesToHuman(e.total_minutes)}</td>
                                    <td className="px-4 py-3 text-right">
                                        {formatMoney(e.total_price, task.client?.currency)}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button size="sm" variant="secondary" onClick={() => openTimeEdit(e)}>
                                                Uredi
                                            </Button>
                                            <Button size="sm" variant="danger" onClick={() => destroyTime(e)}>
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

            <AttachmentPanel type="task" id={task.id} initial={attachments} />

            <Modal open={timeModal} onClose={() => setTimeModal(false)} title={editingEntry ? 'Uredi vrijeme' : 'Novo vrijeme'}>
                <form onSubmit={submitTime} className="space-y-4">
                    <DateInput
                        label="Datum *"
                        value={timeForm.data.work_date}
                        onChange={(e) => timeForm.setData('work_date', e.target.value)}
                        error={timeForm.errors.work_date}
                    />
                    <Input
                        label="Opis"
                        value={timeForm.data.description}
                        onChange={(e) => timeForm.setData('description', e.target.value)}
                    />
                    <div className="grid gap-4 sm:grid-cols-3">
                        <Input
                            label="Sati"
                            type="number"
                            min="0"
                            value={timeForm.data.hours}
                            onChange={(e) => timeForm.setData('hours', e.target.value)}
                            error={timeForm.errors.hours}
                        />
                        <Input
                            label="Minute"
                            type="number"
                            min="0"
                            max="59"
                            value={timeForm.data.minutes}
                            onChange={(e) => timeForm.setData('minutes', e.target.value)}
                            error={timeForm.errors.minutes}
                        />
                        <Input
                            label="Satnica"
                            type="number"
                            step="0.01"
                            value={timeForm.data.hourly_rate}
                            onChange={(e) => timeForm.setData('hourly_rate', e.target.value)}
                            error={timeForm.errors.hourly_rate}
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={timeForm.data.is_billable}
                            onChange={(e) => timeForm.setData('is_billable', e.target.checked)}
                            className="checkbox-base"
                        />
                        Naplativo
                    </label>
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="secondary" onClick={() => setTimeModal(false)}>
                            Otkaži
                        </Button>
                        <Button type="submit" disabled={timeForm.processing}>
                            Sačuvaj
                        </Button>
                    </div>
                </form>
            </Modal>
        </AppLayout>
    );
}
