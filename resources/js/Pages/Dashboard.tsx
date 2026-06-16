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
import { optionsToSelect } from '@/lib/utils';
import type { SharedProps, Task } from '@/types';

type Props = {
    stats: {
        activeClients: number;
        tasksInProgress: number;
        tasksDoneThisMonth: number;
        tasksToBill: number;
        unpaidAmount: string;
        minutesThisMonth: string;
        valueThisMonth: string;
    };
    lists: {
        latestTasks: Task[];
        inProgress: Task[];
        waitingClient: Task[];
        toBill: Task[];
        withDueDate: (Task & { due_overdue?: boolean })[];
    };
    clients: { id: number; name: string }[];
    today: string;
    defaults: { task_date: string; hourly_rate: number };
};

function QuickAction({
    href,
    onClick,
    sub,
    children,
    icon,
}: {
    href?: string;
    onClick?: () => void;
    sub: string;
    children: React.ReactNode;
    icon: React.ReactNode;
}) {
    const className =
        'surface surface-hover group flex cursor-pointer items-center gap-4 p-4 text-left transition hover:-translate-y-0.5';
    const inner = (
        <>
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-500/15 text-brand-400 transition group-hover:bg-brand-500/25">
                {icon}
            </div>
            <div>
                <div className="font-semibold text-neutral-100">{children}</div>
                <div className="text-xs text-neutral-500">{sub}</div>
            </div>
        </>
    );
    if (href) return <Link href={href} className={className}>{inner}</Link>;
    return (
        <button type="button" onClick={onClick} className={className}>
            {inner}
        </button>
    );
}

function TaskList({ title, items, showDue }: { title: string; items: Task[]; showDue?: boolean }) {
    const { options } = usePage<SharedProps>().props;

    return (
        <Card title={title} padding="p-0">
            {items.length === 0 ? (
                <Empty text="Nema taskova." />
            ) : (
                items.map((task) => (
                    <Link
                        key={task.id}
                        href={routes.tasks.show(task.id)}
                        className="flex items-center justify-between gap-3 border-b border-white/[0.04] px-5 py-3.5 last:border-0 transition hover:bg-white/[0.03]"
                    >
                        <div className="min-w-0">
                            <div className="truncate font-medium text-neutral-100">{task.title}</div>
                            <div className="text-xs text-neutral-500">{task.client?.name ?? '-'}</div>
                        </div>
                        <div className="flex shrink-0 items-center gap-2">
                            {showDue && task.due_date_display && (
                                <span
                                    className={`text-xs ${(task as Task & { due_overdue?: boolean }).due_overdue ? 'text-red-500' : 'text-neutral-400'}`}
                                >
                                    {task.due_date_display}
                                </span>
                            )}
                            <Badge value={task.status} map={options.taskStatuses} />
                        </div>
                    </Link>
                ))
            )}
        </Card>
    );
}

export default function Dashboard({ stats, lists, clients, today, defaults }: Props) {
    const { options } = usePage<SharedProps>().props;
    const [showQuickTask, setShowQuickTask] = useState(false);

    const form = useForm({
        client_id: '',
        project_id: '',
        title: '',
        status: 'novo',
        priority: 'normalan',
        task_date: defaults.task_date,
        hourly_rate: String(defaults.hourly_rate),
        billing_type: 'po_satu',
        fixed_price: '0',
        hours: '0',
        minutes: '0',
        is_billable: true,
        payment_status: 'za_naplatu',
    });

    const submitQuickTask = (e: React.FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({
            ...data,
            client_id: Number(data.client_id),
            project_id: data.project_id ? Number(data.project_id) : '',
            hourly_rate: Number(data.hourly_rate),
            fixed_price: Number(data.fixed_price),
            hours: Number(data.hours),
            minutes: Number(data.minutes),
            redirect_show: true,
        }));
        form.post(routes.tasks.store(), {
            onSuccess: () => {
                setShowQuickTask(false);
                form.reset();
            },
        });
    };

    return (
        <AppLayout>
            <Head title="Dashboard" />

            <PageHeader title="Dashboard" subtitle={`Pregled stanja na ${today}`}>
                <Button onClick={() => setShowQuickTask(true)}>+ Brzi task</Button>
            </PageHeader>

            <div className="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <QuickAction onClick={() => setShowQuickTask(true)} sub="Naslov, klijent, status" icon={<span className="text-lg">+</span>}>
                    Dodaj task
                </QuickAction>
                <QuickAction href={routes.clients.index()} sub="Novi ili postojeći klijent" icon={<span>👥</span>}>
                    Klijenti
                </QuickAction>
                <QuickAction href={routes.time.index()} sub="Unos sati na task" icon={<span>⏱</span>}>
                    Unesi vrijeme
                </QuickAction>
                <QuickAction href={routes.reports.index()} sub="PDF, Excel, print" icon={<span>📊</span>}>
                    Izvještaj
                </QuickAction>
            </div>

            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat label="Aktivni klijenti" value={stats.activeClients} color="neutral" />
                <Stat label="Taskovi u toku" value={stats.tasksInProgress} color="blue" />
                <Stat label="Završeno ovaj mjesec" value={stats.tasksDoneThisMonth} color="green" />
                <Stat label="Taskovi za naplatu" value={stats.tasksToBill} color="purple" />
                <Stat label="Neplaćeni iznos" value={stats.unpaidAmount} color="red" />
                <Stat label="Sati ovaj mjesec" value={stats.minutesThisMonth} color="amber" />
                <Stat label="Vrijednost poslova (mjesec)" value={stats.valueThisMonth} color="green" />
            </div>

            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                <TaskList title="Najnoviji taskovi" items={lists.latestTasks} />
                <TaskList title="Taskovi u toku" items={lists.inProgress} />
                <TaskList title="Čekaju klijenta" items={lists.waitingClient} />
                <TaskList title="Za naplatu" items={lists.toBill} />
                <TaskList title="Taskovi sa rokom" items={lists.withDueDate} showDue />
            </div>

            <Modal open={showQuickTask} onClose={() => setShowQuickTask(false)} title="Brzi unos taska" maxWidth="max-w-lg">
                <form onSubmit={submitQuickTask} className="space-y-4">
                    <Select
                        label="Klijent *"
                        value={form.data.client_id}
                        onChange={(e) => form.setData('client_id', e.target.value)}
                        options={clients.map((c) => ({ value: String(c.id), label: c.name }))}
                        placeholder="Odaberi klijenta"
                        error={form.errors.client_id}
                    />
                    <Input
                        label="Naslov *"
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        placeholder="Šta treba uraditi?"
                        error={form.errors.title}
                        autoFocus
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
                            error={form.errors.task_date}
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
                        />
                    </div>
                    <div className="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="secondary" onClick={() => setShowQuickTask(false)}>
                            Otkaži
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            Sačuvaj i otvori
                        </Button>
                    </div>
                </form>
            </Modal>
        </AppLayout>
    );
}
