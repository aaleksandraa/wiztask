import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import TaskBillingFields from '@/Components/TaskBillingFields';
import AppLayout from '@/Layouts/AppLayout';
import Badge from '@/Components/ui/Badge';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import DateInput from '@/Components/ui/DateInput';
import Empty from '@/Components/ui/Empty';
import Input from '@/Components/ui/Input';
import Modal from '@/Components/ui/Modal';
import PageHeader from '@/Components/ui/PageHeader';
import Select from '@/Components/ui/Select';
import Stat from '@/Components/ui/Stat';
import { routes } from '@/lib/routes';
import { formatMoney, label, minutesToHuman, optionsToSelect, recordToSelect } from '@/lib/utils';
import type { Attachment, Client, Project, SharedProps, Task, TimeEntry } from '@/types';

type Props = {
    client: Client;
    tab: string;
    projects: Project[];
    tasks: Task[];
    timeEntries: TimeEntry[];
    attachments: Attachment[];
    stats: {
        projectsCount: number;
        totalMinutes: number;
        totalBillable: number;
        totalPaid: number;
        totalUnpaid: number;
    };
    clientProjects: Record<string, string>;
    defaults: {
        start_date: string;
        task_date: string;
        hourly_rate: number;
        currency: string;
    };
};

const tabs: Record<string, string> = {
    pregled: 'Pregled',
    projekti: 'Projekti',
    taskovi: 'Taskovi',
    vrijeme: 'Vrijeme',
    naplata: 'Naplata',
    fajlovi: 'Fajlovi',
};

export default function ClientsShow({
    client,
    tab,
    projects,
    tasks,
    timeEntries,
    attachments,
    stats,
    clientProjects,
    defaults,
}: Props) {
    const { options } = usePage<SharedProps>().props;
    const [projectModal, setProjectModal] = useState(false);
    const [taskModal, setTaskModal] = useState(false);

    const setTab = (key: string) => {
        router.get(routes.clients.show(client.id), { tab: key }, { preserveState: false });
    };

    const projectForm = useForm({
        client_id: client.id,
        name: '',
        description: '',
        status: 'planirano',
        start_date: defaults.start_date,
        due_date: '',
        billing_type: 'po_satu',
        fixed_price: '0',
        currency: defaults.currency,
        note: '',
    });

    const taskForm = useForm({
        client_id: client.id,
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

    const saveProject = (e: React.FormEvent) => {
        e.preventDefault();
        projectForm.transform((d) => ({
            ...d,
            fixed_price: Number(d.fixed_price),
        }));
        projectForm.post(routes.projects.store(), {
            onSuccess: () => setProjectModal(false),
        });
    };

    const saveTask = (e: React.FormEvent) => {
        e.preventDefault();
        taskForm.transform((d) => ({
            ...d,
            project_id: d.project_id ? Number(d.project_id) : '',
            hourly_rate: Number(d.hourly_rate),
            fixed_price: Number(d.fixed_price),
            hours: Number(d.hours),
            minutes: Number(d.minutes),
        }));
        taskForm.post(routes.tasks.store(), {
            onSuccess: () => setTaskModal(false),
        });
    };

    const subtitle = [client.city, client.country].filter(Boolean).join(', ');

    return (
        <AppLayout>
            <Head title={client.name} />

            <PageHeader title={client.name} subtitle={subtitle || undefined}>
                <Button variant="secondary" href={routes.clients.index()}>
                    ← Nazad
                </Button>
                <Button variant="secondary" onClick={() => setProjectModal(true)}>
                    + Projekat
                </Button>
                <Button onClick={() => setTaskModal(true)}>+ Task</Button>
                <Button variant="secondary" href={`${routes.reports.index()}?client_id=${client.id}`}>
                    Izvještaj
                </Button>
            </PageHeader>

            <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat label="Projekti" value={stats.projectsCount} color="neutral" />
                <Stat label="Ukupno sati" value={minutesToHuman(stats.totalMinutes)} color="amber" />
                <Stat label="Za naplatu" value={formatMoney(stats.totalUnpaid, client.currency)} color="red" />
                <Stat label="Plaćeno" value={formatMoney(stats.totalPaid, client.currency)} color="green" />
            </div>

            <div className="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-white/10">
                <div className="flex flex-wrap gap-1">
                    {Object.entries(tabs).map(([key, labelText]) => (
                        <button
                            key={key}
                            type="button"
                            onClick={() => setTab(key)}
                            className={`border-b-2 px-4 py-2 text-sm font-medium transition ${
                                tab === key ? 'tab-active' : 'tab-inactive'
                            }`}
                        >
                            {labelText}
                        </button>
                    ))}
                </div>
                {tab === 'projekti' && (
                    <Button size="sm" onClick={() => setProjectModal(true)}>
                        + Dodaj projekat
                    </Button>
                )}
                {tab === 'taskovi' && (
                    <Button size="sm" onClick={() => setTaskModal(true)}>
                        + Dodaj task
                    </Button>
                )}
            </div>

            {tab === 'pregled' && (
                <div className="grid gap-4 lg:grid-cols-2">
                    <Card title="Podaci o klijentu">
                        <dl className="grid grid-cols-3 gap-y-3 text-sm">
                            <dt className="text-neutral-500">Kontakt osoba</dt>
                            <dd className="col-span-2">{client.contact_person || '-'}</dd>
                            <dt className="text-neutral-500">Email</dt>
                            <dd className="col-span-2">{client.email || '-'}</dd>
                            <dt className="text-neutral-500">Telefon</dt>
                            <dd className="col-span-2">{client.phone || '-'}</dd>
                            <dt className="text-neutral-500">Web</dt>
                            <dd className="col-span-2">{client.website || '-'}</dd>
                            <dt className="text-neutral-500">Adresa</dt>
                            <dd className="col-span-2">{client.address || '-'}</dd>
                            <dt className="text-neutral-500">Grad / Država</dt>
                            <dd className="col-span-2">{subtitle || '-'}</dd>
                            <dt className="text-neutral-500">Status</dt>
                            <dd className="col-span-2">
                                <Badge value={client.status} map={options.clientStatuses} />
                            </dd>
                            <dt className="text-neutral-500">Satnica</dt>
                            <dd className="col-span-2">{formatMoney(client.default_hourly_rate, client.currency)}</dd>
                        </dl>
                    </Card>
                    <Card title="Napomena">
                        <p className="whitespace-pre-line text-sm text-neutral-300">
                            {client.note || 'Nema napomene.'}
                        </p>
                    </Card>
                </div>
            )}

            {tab === 'projekti' && (
                <Card padding="p-0">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th className="px-4 py-3 text-left">Projekat</th>
                                <th className="px-4 py-3 text-left">Status</th>
                                <th className="px-4 py-3 text-left">Tip naplate</th>
                                <th className="px-4 py-3 text-center">Taskovi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/[0.06]">
                            {projects.length === 0 ? (
                                <tr>
                                    <td colSpan={4}>
                                        <Empty text="Nema projekata.">
                                            <Button size="sm" onClick={() => setProjectModal(true)}>
                                                + Dodaj projekat
                                            </Button>
                                        </Empty>
                                    </td>
                                </tr>
                            ) : (
                                projects.map((p) => (
                                    <tr key={p.id}>
                                        <td className="px-4 py-3">
                                            <Link href={routes.projects.show(p.id)} className="link-accent">
                                                {p.name}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge value={p.status} map={options.projectStatuses} />
                                        </td>
                                        <td className="px-4 py-3">{label(options.projectBillingTypes, p.billing_type)}</td>
                                        <td className="px-4 py-3 text-center">{p.tasks_count ?? 0}</td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </Card>
            )}

            {tab === 'taskovi' && (
                <Card padding="p-0">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th className="px-4 py-3 text-left">Task</th>
                                <th className="px-4 py-3 text-left">Projekat</th>
                                <th className="px-4 py-3 text-left">Datum</th>
                                <th className="px-4 py-3 text-left">Status</th>
                                <th className="px-4 py-3 text-right">Cijena</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/[0.06]">
                            {tasks.length === 0 ? (
                                <tr>
                                    <td colSpan={5}>
                                        <Empty text="Nema taskova.">
                                            <Button size="sm" onClick={() => setTaskModal(true)}>
                                                + Dodaj task
                                            </Button>
                                        </Empty>
                                    </td>
                                </tr>
                            ) : (
                                tasks.map((t) => (
                                    <tr key={t.id}>
                                        <td className="px-4 py-3">
                                            <Link href={routes.tasks.show(t.id)} className="link-accent">
                                                {t.title}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3">{t.project?.name ?? '-'}</td>
                                        <td className="px-4 py-3">{t.task_date_display ?? '-'}</td>
                                        <td className="px-4 py-3">
                                            <Badge value={t.status} map={options.taskStatuses} />
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {formatMoney(t.total_price, client.currency)}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </Card>
            )}

            {tab === 'vrijeme' && (
                <Card padding="p-0">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th className="px-4 py-3 text-left">Datum</th>
                                <th className="px-4 py-3 text-left">Task</th>
                                <th className="px-4 py-3 text-left">Opis</th>
                                <th className="px-4 py-3 text-left">Vrijeme</th>
                                <th className="px-4 py-3 text-right">Cijena</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/[0.06]">
                            {timeEntries.length === 0 ? (
                                <tr>
                                    <td colSpan={5}>
                                        <Empty text="Nema unosa vremena." />
                                    </td>
                                </tr>
                            ) : (
                                timeEntries.map((te) => (
                                    <tr key={te.id}>
                                        <td className="px-4 py-3">{te.work_date_display}</td>
                                        <td className="px-4 py-3">{te.task?.title ?? '-'}</td>
                                        <td className="px-4 py-3">{te.description || '-'}</td>
                                        <td className="px-4 py-3">{minutesToHuman(te.total_minutes)}</td>
                                        <td className="px-4 py-3 text-right">
                                            {formatMoney(te.total_price, client.currency)}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </Card>
            )}

            {tab === 'naplata' && (
                <>
                    <div className="mb-4 grid gap-4 lg:grid-cols-3">
                        <Stat label="Ukupno naplativo" value={formatMoney(stats.totalBillable, client.currency)} />
                        <Stat label="Plaćeno" value={formatMoney(stats.totalPaid, client.currency)} color="green" />
                        <Stat label="Neplaćeno" value={formatMoney(stats.totalUnpaid, client.currency)} color="red" />
                    </div>
                    <Card title="Taskovi i status plaćanja" padding="p-0">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th className="px-4 py-3 text-left">Task</th>
                                    <th className="px-4 py-3 text-left">Cijena</th>
                                    <th className="px-4 py-3 text-left">Status plaćanja</th>
                                </tr>
                            </thead>
                            <tbody>
                                {tasks.filter((t) => t.is_billable).length === 0 ? (
                                    <tr>
                                        <td colSpan={3}>
                                            <Empty text="Nema naplativih taskova." />
                                        </td>
                                    </tr>
                                ) : (
                                    tasks
                                        .filter((t) => t.is_billable)
                                        .map((t) => (
                                            <tr key={t.id}>
                                                <td className="px-4 py-3">
                                                    <Link href={routes.tasks.show(t.id)} className="link-accent">
                                                        {t.title}
                                                    </Link>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {formatMoney(t.total_price, client.currency)}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <Badge value={t.payment_status} map={options.paymentStatuses} />
                                                </td>
                                            </tr>
                                        ))
                                )}
                            </tbody>
                        </table>
                    </Card>
                </>
            )}

            {tab === 'fajlovi' && (
                <Card title="Svi fajlovi klijenta (projekti i taskovi)">
                    {attachments.length === 0 ? (
                        <Empty text="Nema fajlova." />
                    ) : (
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                            {attachments.map((att) => (
                                <div
                                    key={att.id}
                                    className="overflow-hidden rounded-lg border border-white/10"
                                >
                                    <div className="flex h-24 items-center justify-center bg-neutral-950/80">
                                        {att.is_image ? (
                                            <img src={att.url} loading="lazy" alt="" className="h-full w-full object-cover" />
                                        ) : (
                                            <span className="text-3xl text-neutral-500">📄</span>
                                        )}
                                    </div>
                                    <div className="p-2">
                                        <div className="truncate text-xs">{att.original_name}</div>
                                        <a href={att.download_url} className="text-[11px] font-medium link-accent">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </Card>
            )}

            <Modal open={projectModal} onClose={() => setProjectModal(false)} title="Novi projekat">
                <form onSubmit={saveProject} className="space-y-4">
                    <Input
                        label="Naziv projekta *"
                        value={projectForm.data.name}
                        onChange={(e) => projectForm.setData('name', e.target.value)}
                        error={projectForm.errors.name}
                    />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Select
                            label="Status"
                            value={projectForm.data.status}
                            onChange={(e) => projectForm.setData('status', e.target.value)}
                            options={optionsToSelect(options.projectStatuses)}
                        />
                        <Select
                            label="Tip naplate"
                            value={projectForm.data.billing_type}
                            onChange={(e) => projectForm.setData('billing_type', e.target.value)}
                            options={optionsToSelect(options.projectBillingTypes)}
                        />
                        <DateInput
                            label="Datum početka"
                            value={projectForm.data.start_date}
                            onChange={(e) => projectForm.setData('start_date', e.target.value)}
                        />
                        <DateInput
                            label="Rok"
                            value={projectForm.data.due_date}
                            onChange={(e) => projectForm.setData('due_date', e.target.value)}
                        />
                        <Input
                            label="Fiksna cijena"
                            type="number"
                            step="0.01"
                            value={projectForm.data.fixed_price}
                            onChange={(e) => projectForm.setData('fixed_price', e.target.value)}
                        />
                        <Select
                            label="Valuta"
                            value={projectForm.data.currency}
                            onChange={(e) => projectForm.setData('currency', e.target.value)}
                            options={options.currencies.map((c) => ({ value: c, label: c }))}
                        />
                    </div>
                    <div>
                        <label className="mb-1.5 block text-sm font-medium">Opis</label>
                        <textarea
                            value={projectForm.data.description}
                            onChange={(e) => projectForm.setData('description', e.target.value)}
                            rows={2}
                            className="field-base"
                        />
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="secondary" onClick={() => setProjectModal(false)}>
                            Otkaži
                        </Button>
                        <Button type="submit" disabled={projectForm.processing}>
                            Sačuvaj projekat
                        </Button>
                    </div>
                </form>
            </Modal>

            <Modal open={taskModal} onClose={() => setTaskModal(false)} title="Novi task">
                <form onSubmit={saveTask} className="space-y-4">
                    <Input
                        label="Naslov *"
                        value={taskForm.data.title}
                        onChange={(e) => taskForm.setData('title', e.target.value)}
                        placeholder="Opis posla"
                        error={taskForm.errors.title}
                    />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Select
                            label="Projekat"
                            value={taskForm.data.project_id}
                            onChange={(e) => taskForm.setData('project_id', e.target.value)}
                            options={recordToSelect(clientProjects)}
                            placeholder="Bez projekta"
                        />
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
                    <div>
                        <label className="mb-1.5 block text-sm font-medium">Opis</label>
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
