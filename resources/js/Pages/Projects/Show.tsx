import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
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
};

export default function ProjectsShow({ project, tasks, stats, attachments }: Props) {
    const { options } = usePage<SharedProps>().props;

    const editForm = useForm(projectToFormData(project));

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

            <Card title="Taskovi" padding="p-0" className="mb-6">
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
                                    <Empty text="Nema taskova na ovom projektu." />
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
        </AppLayout>
    );
}
