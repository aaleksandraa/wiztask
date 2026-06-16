import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
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
import { label, optionsToSelect, recordToSelect } from '@/lib/utils';
import type { Paginated, Project, SharedProps } from '@/types';

type Props = {
    projects: Paginated<Project>;
    filters: { q?: string; client_id?: string; status?: string; billing_type?: string };
    clients: Record<string, string>;
    defaults: { currency: string };
};

const emptyProject = (defaults: Props['defaults'], clientId = '') => ({
    client_id: clientId,
    name: '',
    description: '',
    status: 'planirano',
    start_date: '',
    due_date: '',
    billing_type: 'po_satu',
    fixed_price: '0',
    currency: defaults.currency,
    note: '',
});

export default function ProjectsIndex({ projects: serverProjects, filters, clients, defaults }: Props) {
    const { options } = usePage<SharedProps>().props;
    const { list: projects, isStale, fetchList, optimisticRemove } = useOptimisticList(
        'projects',
        filters,
        serverProjects,
    );
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState<Project | null>(null);

    const filterForm = useForm({
        q: filters.q ?? '',
        client_id: filters.client_id ?? '',
        status: filters.status ?? '',
        billing_type: filters.billing_type ?? '',
    });

    const form = useForm(emptyProject(defaults));

    const applyFilters = (e: React.FormEvent) => {
        e.preventDefault();
        fetchList(routes.projects.index(), filterForm.data);
    };

    const openCreate = () => {
        setEditing(null);
        form.setData(emptyProject(defaults, filterForm.data.client_id));
        form.clearErrors();
        setModalOpen(true);
    };

    const openEdit = (project: Project) => {
        setEditing(project);
        form.setData({
            client_id: String(project.client_id),
            name: project.name,
            description: project.description ?? '',
            status: project.status,
            start_date: project.start_date ?? '',
            due_date: project.due_date ?? '',
            billing_type: project.billing_type,
            fixed_price: String(project.fixed_price),
            currency: project.currency,
            note: project.note ?? '',
        });
        form.clearErrors();
        setModalOpen(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = {
            ...form.data,
            client_id: Number(form.data.client_id),
            fixed_price: Number(form.data.fixed_price),
        };
        if (editing) {
            form.transform(() => payload);
            form.put(routes.projects.update(editing.id), {
                onSuccess: () => {
                    invalidateListCache('projects');
                    setModalOpen(false);
                },
            });
        } else {
            form.transform(() => payload);
            form.post(routes.projects.store(), {
                onSuccess: () => {
                    invalidateListCache('projects');
                    setModalOpen(false);
                },
            });
        }
    };

    const destroy = (project: Project) => {
        if (!confirm(`Obrisati projekat "${project.name}"?`)) return;
        optimisticRemove(project.id);
        router.delete(routes.projects.destroy(project.id), {
            onError: () => invalidateListCache('projects'),
        });
    };

    return (
        <AppLayout>
            <Head title="Projekti" />

            <PageHeader title="Projekti" subtitle="Pregled i upravljanje projektima">
                <Button onClick={openCreate}>+ Dodaj novi projekat</Button>
            </PageHeader>

            <ListCacheBanner show={isStale} />

            <Card className="mb-6">
                <form onSubmit={applyFilters} className="grid gap-4 sm:grid-cols-5">
                    <Input
                        label="Pretraga"
                        value={filterForm.data.q}
                        onChange={(e) => filterForm.setData('q', e.target.value)}
                        placeholder="Naziv projekta..."
                    />
                    <Select
                        label="Klijent"
                        value={filterForm.data.client_id}
                        onChange={(e) => filterForm.setData('client_id', e.target.value)}
                        options={recordToSelect(clients)}
                        placeholder="Svi"
                    />
                    <Select
                        label="Status"
                        value={filterForm.data.status}
                        onChange={(e) => filterForm.setData('status', e.target.value)}
                        options={optionsToSelect(options.projectStatuses)}
                        placeholder="Svi"
                    />
                    <Select
                        label="Tip naplate"
                        value={filterForm.data.billing_type}
                        onChange={(e) => filterForm.setData('billing_type', e.target.value)}
                        options={optionsToSelect(options.projectBillingTypes)}
                        placeholder="Svi"
                    />
                    <div className="flex items-end gap-2">
                        <Button type="submit">Filtriraj</Button>
                        <Button type="button" variant="secondary" onClick={() => fetchList(routes.projects.index(), {})}>
                            Reset
                        </Button>
                    </div>
                </form>
            </Card>

            <Card padding="p-0">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th className="px-4 py-3 text-left">Projekat</th>
                            <th className="px-4 py-3 text-left">Klijent</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-left">Tip naplate</th>
                            <th className="px-4 py-3 text-center">Taskovi</th>
                            <th className="px-4 py-3 text-right">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        {projects.data.length === 0 ? (
                            <tr>
                                <td colSpan={6}>
                                    <Empty text="Nema projekata.">
                                        <Button size="sm" onClick={openCreate}>
                                            + Dodaj projekat
                                        </Button>
                                    </Empty>
                                </td>
                            </tr>
                        ) : (
                            projects.data.map((p) => (
                                <ClickableTableRow key={p.id} href={routes.projects.show(p.id)}>
                                    <td className="px-4 py-3 font-medium text-neutral-100">{p.name}</td>
                                    <td className="px-4 py-3">
                                        {p.client ? (
                                            <Link href={routes.clients.show(p.client.id)} className="link-accent">
                                                {p.client.name}
                                            </Link>
                                        ) : (
                                            '-'
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge value={p.status} map={options.projectStatuses} />
                                    </td>
                                    <td className="px-4 py-3">{label(options.projectBillingTypes, p.billing_type)}</td>
                                    <td className="px-4 py-3 text-center">{p.tasks_count ?? 0}</td>
                                    <td className="px-4 py-3 text-right" data-row-ignore>
                                        <div className="flex justify-end gap-2">
                                            <Button size="sm" variant="secondary" onClick={() => openEdit(p)}>
                                                Uredi
                                            </Button>
                                            <Button size="sm" variant="danger" onClick={() => destroy(p)}>
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

            <Pagination links={projects.links as { url: string | null; label: string; active: boolean }[]} meta={projects.meta} />

            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Uredi projekat' : 'Novi projekat'}>
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
                        label="Naziv *"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        error={form.errors.name}
                    />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Select
                            label="Status"
                            value={form.data.status}
                            onChange={(e) => form.setData('status', e.target.value)}
                            options={optionsToSelect(options.projectStatuses)}
                        />
                        <Select
                            label="Tip naplate"
                            value={form.data.billing_type}
                            onChange={(e) => form.setData('billing_type', e.target.value)}
                            options={optionsToSelect(options.projectBillingTypes)}
                        />
                        <DateInput
                            label="Datum početka"
                            value={form.data.start_date}
                            onChange={(e) => form.setData('start_date', e.target.value)}
                        />
                        <DateInput
                            label="Rok"
                            value={form.data.due_date}
                            onChange={(e) => form.setData('due_date', e.target.value)}
                        />
                        <Input
                            label="Fiksna cijena"
                            type="number"
                            step="0.01"
                            value={form.data.fixed_price}
                            onChange={(e) => form.setData('fixed_price', e.target.value)}
                        />
                        <Select
                            label="Valuta"
                            value={form.data.currency}
                            onChange={(e) => form.setData('currency', e.target.value)}
                            options={options.currencies.map((c) => ({ value: c, label: c }))}
                        />
                    </div>
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
