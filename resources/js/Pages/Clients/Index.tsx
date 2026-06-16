import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import Badge from '@/Components/ui/Badge';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import ClickableTableRow from '@/Components/ui/ClickableTableRow';
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
import { optionsToSelect } from '@/lib/utils';
import type { Client, Paginated, SharedProps } from '@/types';

type Props = {
    clients: Paginated<Client>;
    filters: { q?: string; status?: string; city?: string };
    defaults: { default_hourly_rate: number; currency: string };
};

const emptyClient = (defaults: Props['defaults']): Partial<Client> => ({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    website: '',
    city: '',
    country: '',
    address: '',
    note: '',
    status: 'aktivan',
    default_hourly_rate: defaults.default_hourly_rate,
    currency: defaults.currency,
});

export default function ClientsIndex({ clients: serverClients, filters, defaults }: Props) {
    const { options } = usePage<SharedProps>().props;
    const { list: clients, isStale, fetchList, optimisticRemove } = useOptimisticList(
        'clients',
        filters,
        serverClients,
    );
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState<Client | null>(null);

    const filterForm = useForm({
        q: filters.q ?? '',
        status: filters.status ?? '',
        city: filters.city ?? '',
    });

    const form = useForm({
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        website: '',
        city: '',
        country: '',
        address: '',
        note: '',
        status: 'aktivan',
        default_hourly_rate: String(defaults.default_hourly_rate),
        currency: defaults.currency,
    });

    const applyFilters = (e: React.FormEvent) => {
        e.preventDefault();
        fetchList(routes.clients.index(), filterForm.data);
    };

    const openCreate = () => {
        setEditing(null);
        const d = emptyClient(defaults);
        form.setData({
            name: d.name ?? '',
            contact_person: d.contact_person ?? '',
            email: d.email ?? '',
            phone: d.phone ?? '',
            website: d.website ?? '',
            city: d.city ?? '',
            country: d.country ?? '',
            address: d.address ?? '',
            note: d.note ?? '',
            status: d.status ?? 'aktivan',
            default_hourly_rate: String(d.default_hourly_rate),
            currency: d.currency ?? defaults.currency,
        });
        form.clearErrors();
        setModalOpen(true);
    };

    const openEdit = (client: Client) => {
        setEditing(client);
        form.setData({
            name: client.name,
            contact_person: client.contact_person ?? '',
            email: client.email ?? '',
            phone: client.phone ?? '',
            website: client.website ?? '',
            city: client.city ?? '',
            country: client.country ?? '',
            address: client.address ?? '',
            note: client.note ?? '',
            status: client.status,
            default_hourly_rate: String(client.default_hourly_rate),
            currency: client.currency,
        });
        form.clearErrors();
        setModalOpen(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = {
            ...form.data,
            default_hourly_rate: Number(form.data.default_hourly_rate),
        };
        if (editing) {
            form.transform(() => payload);
            form.put(routes.clients.update(editing.id), {
                onSuccess: () => {
                    invalidateListCache('clients');
                    setModalOpen(false);
                },
            });
        } else {
            form.transform(() => payload);
            form.post(routes.clients.store(), {
                onSuccess: () => {
                    invalidateListCache('clients');
                    setModalOpen(false);
                },
            });
        }
    };

    const destroy = (client: Client) => {
        if (!confirm(`Obrisati klijenta "${client.name}"?`)) return;
        optimisticRemove(client.id);
        router.delete(routes.clients.destroy(client.id), {
            onError: () => invalidateListCache('clients'),
        });
    };

    return (
        <AppLayout>
            <Head title="Klijenti" />

            <PageHeader title="Klijenti" subtitle="Upravljanje klijentima i kontaktima">
                <Button onClick={openCreate}>+ Dodaj novog klijenta</Button>
            </PageHeader>

            <ListCacheBanner show={isStale} />

            <Card className="mb-6">
                <form onSubmit={applyFilters} className="grid gap-4 sm:grid-cols-4">
                    <Input
                        label="Pretraga"
                        value={filterForm.data.q}
                        onChange={(e) => filterForm.setData('q', e.target.value)}
                        placeholder="Naziv, kontakt, email..."
                    />
                    <Select
                        label="Status"
                        value={filterForm.data.status}
                        onChange={(e) => filterForm.setData('status', e.target.value)}
                        options={optionsToSelect(options.clientStatuses)}
                        placeholder="Svi"
                    />
                    <Input
                        label="Grad"
                        value={filterForm.data.city}
                        onChange={(e) => filterForm.setData('city', e.target.value)}
                        placeholder="Sarajevo"
                    />
                    <div className="flex items-end gap-2">
                        <Button type="submit">Filtriraj</Button>
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() => fetchList(routes.clients.index(), {})}
                        >
                            Reset
                        </Button>
                    </div>
                </form>
            </Card>

            <Card padding="p-0">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th className="px-4 py-3 text-left">Klijent</th>
                            <th className="px-4 py-3 text-left">Kontakt</th>
                            <th className="px-4 py-3 text-left">Grad</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-center">Projekti</th>
                            <th className="px-4 py-3 text-center">Taskovi</th>
                            <th className="px-4 py-3 text-right">Akcije</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-white/[0.06]">
                        {clients.data.length === 0 ? (
                            <tr>
                                <td colSpan={7}>
                                    <Empty text="Nema klijenata.">
                                        <Button size="sm" onClick={openCreate}>
                                            + Dodaj klijenta
                                        </Button>
                                    </Empty>
                                </td>
                            </tr>
                        ) : (
                            clients.data.map((client) => (
                                <ClickableTableRow key={client.id} href={routes.clients.show(client.id)}>
                                    <td className="px-4 py-3 font-medium text-neutral-100">
                                        {client.name}
                                    </td>
                                    <td className="px-4 py-3 text-neutral-400">
                                        {client.contact_person || client.email || '-'}
                                    </td>
                                    <td className="px-4 py-3">{client.city || '-'}</td>
                                    <td className="px-4 py-3">
                                        <Badge value={client.status} map={options.clientStatuses} />
                                    </td>
                                    <td className="px-4 py-3 text-center">{client.projects_count ?? 0}</td>
                                    <td className="px-4 py-3 text-center">{client.tasks_count ?? 0}</td>
                                    <td className="px-4 py-3 text-right" data-row-ignore>
                                        <div className="flex justify-end gap-2">
                                            <Button size="sm" variant="secondary" onClick={() => openEdit(client)}>
                                                Uredi
                                            </Button>
                                            <Button size="sm" variant="danger" onClick={() => destroy(client)}>
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

            <Pagination links={clients.links as { url: string | null; label: string; active: boolean }[]} meta={clients.meta} />

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title={editing ? 'Uredi klijenta' : 'Novi klijent'}
                maxWidth="max-w-2xl"
            >
                <form onSubmit={submit} className="space-y-4">
                    <Input
                        label="Naziv *"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        error={form.errors.name}
                    />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Input
                            label="Kontakt osoba"
                            value={form.data.contact_person}
                            onChange={(e) => form.setData('contact_person', e.target.value)}
                        />
                        <Input
                            label="Email"
                            type="email"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                            error={form.errors.email}
                        />
                        <Input label="Telefon" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} />
                        <Input label="Web" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
                        <Input label="Grad" value={form.data.city} onChange={(e) => form.setData('city', e.target.value)} />
                        <Input label="Država" value={form.data.country} onChange={(e) => form.setData('country', e.target.value)} />
                        <Input
                            label="Satnica"
                            type="number"
                            step="0.01"
                            value={form.data.default_hourly_rate}
                            onChange={(e) => form.setData('default_hourly_rate', e.target.value)}
                            error={form.errors.default_hourly_rate}
                        />
                        <Select
                            label="Valuta"
                            value={form.data.currency}
                            onChange={(e) => form.setData('currency', e.target.value)}
                            options={options.currencies.map((c) => ({ value: c, label: c }))}
                        />
                        <Select
                            label="Status"
                            value={form.data.status}
                            onChange={(e) => form.setData('status', e.target.value)}
                            options={optionsToSelect(options.clientStatuses)}
                        />
                    </div>
                    <Input label="Adresa" value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} />
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-neutral-300">Napomena</label>
                        <textarea
                            value={form.data.note}
                            onChange={(e) => form.setData('note', e.target.value)}
                            rows={3}
                            className="field-base"
                        />
                    </div>
                    <div className="flex justify-end gap-2 pt-2">
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
