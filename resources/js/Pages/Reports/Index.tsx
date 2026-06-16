import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Badge from '@/Components/ui/Badge';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import DateInput from '@/Components/ui/DateInput';
import Empty from '@/Components/ui/Empty';
import PageHeader from '@/Components/ui/PageHeader';
import Select from '@/Components/ui/Select';
import Stat from '@/Components/ui/Stat';
import { routes } from '@/lib/routes';
import { buildQuery, formatMoney, minutesToHuman, optionsToSelect, recordToSelect } from '@/lib/utils';
import type { SharedProps } from '@/types';

type ReportTask = {
    id: number;
    title: string;
    task_date_display: string;
    project: string | null;
    status: string;
    payment_status: string;
    minutes_human: string;
    total_price_formatted: string;
};

type Report = {
    client: { id: number; name: string; currency: string };
    tasks: ReportTask[];
    totals: {
        count: number;
        minutes: number;
        billable: number;
        paid: number;
        unpaid: number;
    };
    filters: Record<string, unknown>;
};

type Props = {
    report: Report | null;
    filters: {
        client_id: string;
        project_id: string;
        status: string;
        date_from: string;
        date_to: string;
        only_billable: boolean;
        only_unpaid: boolean;
    };
    clients: Record<string, string>;
    projects: Record<string, string>;
    exportParams: Record<string, string | number | boolean>;
};

export default function ReportsIndex({ report, filters, clients, projects, exportParams }: Props) {
    const { options } = usePage<SharedProps>().props;

    const form = useForm({
        client_id: filters.client_id ?? '',
        project_id: filters.project_id ?? '',
        status: filters.status ?? '',
        date_from: filters.date_from ?? '',
        date_to: filters.date_to ?? '',
        only_billable: filters.only_billable ?? false,
        only_unpaid: filters.only_unpaid ?? false,
    });

    const onClientChange = (clientId: string) => {
        form.setData({ ...form.data, client_id: clientId, project_id: '' });
        router.get(
            routes.reports.index(),
            { ...form.data, client_id: clientId, project_id: '' },
            { preserveState: true },
        );
    };

    const generate = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(routes.reports.generate());
    };

    const exportQuery = buildQuery(exportParams);

    return (
        <AppLayout>
            <Head title="Izvještaji" />

            <PageHeader title="Izvještaji" subtitle="Generisanje izvještaja rada i naplate po klijentu" />

            <Card className="mb-6">
                <form onSubmit={generate} className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-3">
                        <Select
                            label="Klijent *"
                            value={form.data.client_id}
                            onChange={(e) => onClientChange(e.target.value)}
                            options={recordToSelect(clients)}
                            placeholder="Odaberi klijenta"
                            error={form.errors.client_id}
                        />
                        <Select
                            label="Projekat"
                            value={form.data.project_id}
                            onChange={(e) => form.setData('project_id', e.target.value)}
                            options={recordToSelect(projects)}
                            placeholder="Svi projekti"
                        />
                        <Select
                            label="Status taska"
                            value={form.data.status}
                            onChange={(e) => form.setData('status', e.target.value)}
                            options={optionsToSelect(options.taskStatuses)}
                            placeholder="Svi"
                        />
                        <DateInput
                            label="Datum od"
                            value={form.data.date_from}
                            onChange={(e) => form.setData('date_from', e.target.value)}
                            error={form.errors.date_from}
                        />
                        <DateInput
                            label="Datum do"
                            value={form.data.date_to}
                            onChange={(e) => form.setData('date_to', e.target.value)}
                            error={form.errors.date_to}
                        />
                    </div>
                    <div className="flex flex-wrap gap-4">
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.only_billable}
                                onChange={(e) => form.setData('only_billable', e.target.checked)}
                                className="checkbox-base"
                            />
                            Samo naplativo
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.only_unpaid}
                                onChange={(e) => form.setData('only_unpaid', e.target.checked)}
                                className="checkbox-base"
                            />
                            Samo neplaćeno
                        </label>
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        Generiši izvještaj
                    </Button>
                </form>
            </Card>

            {report ? (
                <>
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h2 className="text-lg font-semibold text-heading">
                            Izvještaj: {report.client.name}
                        </h2>
                        <div className="flex flex-wrap gap-2">
                            <Button variant="secondary" href={`${routes.reports.exportPdf()}${exportQuery}`}>
                                Export PDF
                            </Button>
                            <Button variant="secondary" href={`${routes.reports.exportExcel()}${exportQuery}`}>
                                Export Excel
                            </Button>
                            <Button variant="secondary" href={`${routes.reports.print()}${exportQuery}`}>
                                Print
                            </Button>
                        </div>
                    </div>

                    <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
                        <Stat label="Taskova" value={report.totals.count} />
                        <Stat label="Ukupno sati" value={minutesToHuman(report.totals.minutes)} color="amber" />
                        <Stat
                            label="Naplativo"
                            value={formatMoney(report.totals.billable, report.client.currency)}
                            color="green"
                        />
                        <Stat
                            label="Plaćeno"
                            value={formatMoney(report.totals.paid, report.client.currency)}
                            color="green"
                        />
                        <Stat
                            label="Neplaćeno"
                            value={formatMoney(report.totals.unpaid, report.client.currency)}
                            color="red"
                        />
                    </div>

                    <Card title="Taskovi" padding="p-0">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th className="px-4 py-3 text-left">Task</th>
                                    <th className="px-4 py-3 text-left">Datum</th>
                                    <th className="px-4 py-3 text-left">Projekat</th>
                                    <th className="px-4 py-3 text-left">Status</th>
                                    <th className="px-4 py-3 text-left">Plaćanje</th>
                                    <th className="px-4 py-3 text-left">Sati</th>
                                    <th className="px-4 py-3 text-right">Cijena</th>
                                </tr>
                            </thead>
                            <tbody>
                                {report.tasks.length === 0 ? (
                                    <tr>
                                        <td colSpan={7}>
                                            <Empty text="Nema taskova za odabrane filtere." />
                                        </td>
                                    </tr>
                                ) : (
                                    report.tasks.map((t) => (
                                        <tr key={t.id}>
                                            <td className="px-4 py-3">
                                                <Link href={routes.tasks.show(t.id)} className="link-accent">
                                                    {t.title}
                                                </Link>
                                            </td>
                                            <td className="px-4 py-3">{t.task_date_display}</td>
                                            <td className="px-4 py-3">{t.project ?? '-'}</td>
                                            <td className="px-4 py-3">
                                                <Badge value={t.status} map={options.taskStatuses} />
                                            </td>
                                            <td className="px-4 py-3">
                                                <Badge value={t.payment_status} map={options.paymentStatuses} />
                                            </td>
                                            <td className="px-4 py-3">{t.minutes_human}</td>
                                            <td className="px-4 py-3 text-right">{t.total_price_formatted}</td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </Card>
                </>
            ) : (
                <Card>
                    <Empty text="Odaberite klijenta i generišite izvještaj." />
                </Card>
            )}
        </AppLayout>
    );
}
