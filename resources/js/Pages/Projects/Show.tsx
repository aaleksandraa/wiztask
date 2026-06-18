import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import TaskBillingFields from '@/Components/TaskBillingFields';
import AppLayout from '@/Layouts/AppLayout';
import AttachmentPanel from '@/Components/AttachmentPanel';
import Badge from '@/Components/ui/Badge';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import ClickableTableRow from '@/Components/ui/ClickableTableRow';
import DateInput from '@/Components/ui/DateInput';
import EditableTextarea from '@/Components/ui/EditableTextarea';
import Empty from '@/Components/ui/Empty';
import Input from '@/Components/ui/Input';
import Modal from '@/Components/ui/Modal';
import PageHeader from '@/Components/ui/PageHeader';
import Select from '@/Components/ui/Select';
import Stat from '@/Components/ui/Stat';
import { projectFormPayload, projectToFormData } from '@/lib/projectForm';
import { routes } from '@/lib/routes';
import { formatMoney, optionsToSelect } from '@/lib/utils';
import type { Attachment, Project, SharedProps, Task } from '@/types';

type Props = {
    project: Project;
    tasks: Task[];
    stats: { totalMinutes: string; totalValue: string };
    attachments: Attachment[];
    defaults: { task_date: string; hourly_rate: number };
};

export default function ProjectsShow({ project, tasks, stats, attachments, defaults }: Props) {
    const { options } = usePage<SharedProps>().props;
    const [taskModal, setTaskModal] = useState(false);

    const editForm = useForm(projectToFormData(project));

    const taskForm = useForm({
        client_id: project.client_id,
        project_id: project.id,
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

    const openTaskModal = () => {
        taskForm.setData({
            client_id: project.client_id,
            project_id: project.id,
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
        taskForm.clearErrors();
        setTaskModal(true);
    };

    const saveTask = (e: React.FormEvent) => {
        e.preventDefault();
        taskForm.transform((d) => ({
            ...d,
            client_id: Number(d.client_id),
            project_id: Number(d.project_id),
            hourly_rate: Number(d.hourly_rate),
            fixed_price: Number(d.fixed_price),
            hours: Number(d.hours),
            minutes: Number(d.minutes),
        }));
        taskForm.post(routes.tasks.store(), {
            onSuccess: () => setTaskModal(false),
        });
    };

    useEffect(() => {
        const data = projectToFormData(project);
        editForm.setDefaults(data);
        editForm.reset();
        // eslint-disable-next-line react-hooks/exhaustive-deps -- sync when server project changes
    }, [project.id, project.name, project.description, project.note, project.status]);

    const saveEdits = () => {
        if (!editForm.isDirty) return;

        editForm.transform(() => projectFormPayload(editForm.data));
        editForm.put(routes.projects.update(project.id), { preserveScroll: true });
    };

    return (
        <AppLayout>
            <Head title={editForm.data.name || project.name} />

            <PageHeader title={project.client?.name ?? 'Projekat'} subtitle="Uređivanje projekta">
                <Button variant="secondary" href={routes.projects.index()}>
                    ← Nazad
                </Button>
                {project.client && (
                    <Button variant="secondary" href={routes.clients.show(project.client.id)}>
                        Klijent
                    </Button>
                )}
                {editForm.isDirty && (
                    <>
                        <Button variant="secondary" onClick={() => editForm.reset()}>
                            Poništi
                        </Button>
                        <Button onClick={saveEdits} disabled={editForm.processing}>
                            {editForm.processing ? 'Spremanje...' : 'Sačuvaj promjene'}
                        </Button>
                    </>
                )}
            </PageHeader>

            <div className="mb-4">
                <Input
                    label="Naziv projekta"
                    value={editForm.data.name}
                    onChange={(e) => editForm.setData('name', e.target.value)}
                    error={editForm.errors.name}
                    className="text-lg font-semibold"
                />
            </div>

            <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat label="Ukupno sati" value={stats.totalMinutes} color="amber" />
                <Stat label="Ukupna vrijednost" value={stats.totalValue} color="green" />
                <Stat label="Taskovi" value={tasks.length} color="neutral" />
                <Stat label="Status" value={<Badge value={editForm.data.status} map={options.projectStatuses} />} color="blue" />
            </div>

            <div className="mb-6 grid gap-4 lg:grid-cols-2">
                <Card title="Detalji projekta">
                    <dl className="mb-4 grid grid-cols-3 gap-y-3 text-sm">
                        <dt className="text-neutral-500">Klijent</dt>
                        <dd className="col-span-2">
                            {project.client ? (
                                <Link href={routes.clients.show(project.client.id)} className="link-accent">
                                    {project.client.name}
                                </Link>
                            ) : (
                                '-'
                            )}
                        </dd>
                    </dl>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Select
                            label="Status"
                            value={editForm.data.status}
                            onChange={(e) => editForm.setData('status', e.target.value)}
                            options={optionsToSelect(options.projectStatuses)}
                        />
                        <Select
                            label="Tip naplate"
                            value={editForm.data.billing_type}
                            onChange={(e) => editForm.setData('billing_type', e.target.value)}
                            options={optionsToSelect(options.projectBillingTypes)}
                        />
                        <DateInput
                            label="Početak"
                            value={editForm.data.start_date}
                            onChange={(e) => editForm.setData('start_date', e.target.value)}
                            error={editForm.errors.start_date}
                        />
                        <DateInput
                            label="Rok"
                            value={editForm.data.due_date}
                            onChange={(e) => editForm.setData('due_date', e.target.value)}
                            error={editForm.errors.due_date}
                        />
                        <Input
                            label="Fiksna cijena"
                            type="number"
                            step="0.01"
                            min="0"
                            value={editForm.data.fixed_price}
                            onChange={(e) => editForm.setData('fixed_price', e.target.value)}
                            error={editForm.errors.fixed_price}
                        />
                        <Select
                            label="Valuta"
                            value={editForm.data.currency}
                            onChange={(e) => editForm.setData('currency', e.target.value)}
                            options={options.currencies.map((c) => ({ value: c, label: c }))}
                        />
                    </div>
                </Card>

                <Card
                    title="Opis i napomene"
                    action={
                        editForm.isDirty ? (
                            <Button size="sm" onClick={saveEdits} disabled={editForm.processing}>
                                Sačuvaj
                            </Button>
                        ) : null
                    }
                >
                    <div className="space-y-4">
                        <EditableTextarea
                            label="Opis projekta"
                            value={editForm.data.description}
                            onChange={(value) => editForm.setData('description', value)}
                            onBlurSave={saveEdits}
                            error={editForm.errors.description}
                            hint="Izmjene se automatski spremaju pri izlasku iz polja."
                            rows={6}
                            placeholder="Opis projekta, ciljevi, obim posla..."
                        />
                        <EditableTextarea
                            label="Napomena"
                            value={editForm.data.note}
                            onChange={(value) => editForm.setData('note', value)}
                            onBlurSave={saveEdits}
                            error={editForm.errors.note}
                            rows={4}
                            placeholder="Dodatne interne napomene..."
                        />
                    </div>
                </Card>
            </div>

            <Card
                title="Taskovi"
                padding="p-0"
                className="mb-6"
                action={
                    <Button size="sm" onClick={openTaskModal}>
                        + Dodaj novi task
                    </Button>
                }
            >
                <table className="data-table">
                    <thead>
                        <tr>
                            <th className="px-4 py-3 text-left">Task</th>
                            <th className="px-4 py-3 text-left">Datum</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-left">Prioritet</th>
                            <th className="px-4 py-3 text-right">Cijena</th>
                        </tr>
                    </thead>
                    <tbody>
                        {tasks.length === 0 ? (
                            <tr>
                                <td colSpan={5}>
                                    <Empty text="Nema taskova na ovom projektu.">
                                        <Button size="sm" onClick={openTaskModal}>
                                            + Dodaj novi task
                                        </Button>
                                    </Empty>
                                </td>
                            </tr>
                        ) : (
                            tasks.map((t) => (
                                <ClickableTableRow key={t.id} href={routes.tasks.show(t.id)}>
                                    <td className="px-4 py-3 font-medium text-neutral-100">{t.title}</td>
                                    <td className="px-4 py-3">{t.task_date_display ?? '-'}</td>
                                    <td className="px-4 py-3">
                                        <Badge value={t.status} map={options.taskStatuses} />
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge value={t.priority} map={options.taskPriorities} />
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        {formatMoney(t.total_price, t.client?.currency ?? project.currency)}
                                    </td>
                                </ClickableTableRow>
                            ))
                        )}
                    </tbody>
                </table>
            </Card>

            <AttachmentPanel type="project" id={project.id} initial={attachments} />

            <Modal open={taskModal} onClose={() => setTaskModal(false)} title="Novi task" maxWidth="max-w-2xl">
                <form onSubmit={saveTask} className="space-y-4">
                    <div className="rounded-xl border border-white/[0.06] bg-white/[0.02] px-4 py-3 text-sm">
                        <div className="text-neutral-400">Klijent</div>
                        <div className="font-medium text-neutral-100">{project.client?.name ?? '-'}</div>
                        <div className="mt-2 text-neutral-400">Projekat</div>
                        <div className="font-medium text-neutral-100">{project.name}</div>
                    </div>
                    <Input
                        label="Naslov *"
                        value={taskForm.data.title}
                        onChange={(e) => taskForm.setData('title', e.target.value)}
                        placeholder="Opis posla"
                        error={taskForm.errors.title}
                    />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <DateInput
                            label="Datum"
                            value={taskForm.data.task_date}
                            onChange={(e) => taskForm.setData('task_date', e.target.value)}
                        />
                        <Select
                            label="Status"
                            value={taskForm.data.status}
                            onChange={(e) => taskForm.setData('status', e.target.value)}
                            options={optionsToSelect(options.taskStatuses)}
                        />
                        <Select
                            label="Prioritet"
                            value={taskForm.data.priority}
                            onChange={(e) => taskForm.setData('priority', e.target.value)}
                            options={optionsToSelect(options.taskPriorities)}
                        />
                        <TaskBillingFields
                            billingType={taskForm.data.billing_type}
                            onBillingTypeChange={(value) => taskForm.setData('billing_type', value)}
                            billingTypeOptions={options.taskBillingTypes}
                            hourlyRate={taskForm.data.hourly_rate}
                            onHourlyRateChange={(value) => taskForm.setData('hourly_rate', value)}
                            fixedPrice={taskForm.data.fixed_price}
                            onFixedPriceChange={(value) => taskForm.setData('fixed_price', value)}
                            hours={taskForm.data.hours}
                            onHoursChange={(value) => taskForm.setData('hours', value)}
                            minutes={taskForm.data.minutes}
                            onMinutesChange={(value) => taskForm.setData('minutes', value)}
                            errors={taskForm.errors}
                        />
                        <DateInput
                            label="Rok"
                            value={taskForm.data.due_date}
                            onChange={(e) => taskForm.setData('due_date', e.target.value)}
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={taskForm.data.is_billable}
                            onChange={(e) => taskForm.setData('is_billable', e.target.checked)}
                            className="checkbox-base"
                        />
                        Naplativo
                    </label>
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-neutral-300">Opis</label>
                        <textarea
                            value={taskForm.data.description}
                            onChange={(e) => taskForm.setData('description', e.target.value)}
                            rows={2}
                            className="field-base"
                        />
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="secondary" onClick={() => setTaskModal(false)}>
                            Otkaži
                        </Button>
                        <Button type="submit" disabled={taskForm.processing}>
                            Sačuvaj task
                        </Button>
                    </div>
                </form>
            </Modal>
        </AppLayout>
    );
}
