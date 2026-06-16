import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/ui/Button';
import Card from '@/Components/ui/Card';
import Input from '@/Components/ui/Input';
import PageHeader from '@/Components/ui/PageHeader';
import Select from '@/Components/ui/Select';
import { routes } from '@/lib/routes';
import type { SharedProps } from '@/types';

type Props = {
    form: {
        app_name: string;
        default_currency: string;
        default_hourly_rate: string;
        allowed_file_types: string;
    };
};

export default function SettingsEdit({ form: initial }: Props) {
    const { options } = usePage<SharedProps>().props;

    const form = useForm({
        app_name: initial.app_name,
        default_currency: initial.default_currency,
        default_hourly_rate: initial.default_hourly_rate,
        allowed_file_types: initial.allowed_file_types,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.transform((d) => ({
            ...d,
            default_hourly_rate: Number(d.default_hourly_rate),
        }));
        form.put(routes.settings.update());
    };

    return (
        <AppLayout>
            <Head title="Podešavanja" />

            <PageHeader title="Podešavanja" subtitle="Opća podešavanja aplikacije" />

            <Card className="max-w-2xl">
                <form onSubmit={submit} className="space-y-4">
                    <Input
                        label="Naziv aplikacije *"
                        value={form.data.app_name}
                        onChange={(e) => form.setData('app_name', e.target.value)}
                        error={form.errors.app_name}
                    />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Select
                            label="Zadana valuta *"
                            value={form.data.default_currency}
                            onChange={(e) => form.setData('default_currency', e.target.value)}
                            options={options.currencies.map((c) => ({ value: c, label: c }))}
                            error={form.errors.default_currency}
                        />
                        <Input
                            label="Zadana satnica *"
                            type="number"
                            step="0.01"
                            min="0"
                            value={form.data.default_hourly_rate}
                            onChange={(e) => form.setData('default_hourly_rate', e.target.value)}
                            error={form.errors.default_hourly_rate}
                        />
                    </div>
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-neutral-300">
                            Dozvoljeni tipovi fajlova *
                        </label>
                        <textarea
                            value={form.data.allowed_file_types}
                            onChange={(e) => form.setData('allowed_file_types', e.target.value)}
                            rows={3}
                            placeholder="jpg,jpeg,png,pdf,..."
                            className="field-base"
                        />
                        {form.errors.allowed_file_types && (
                            <p className="mt-1 text-xs text-red-400">{form.errors.allowed_file_types}</p>
                        )}
                        <p className="mt-1 text-xs text-neutral-500">Odvojite ekstenzije zarezom (npr. jpg,png,pdf)</p>
                    </div>
                    <div className="flex justify-end pt-2">
                        <Button type="submit" disabled={form.processing}>
                            Sačuvaj podešavanja
                        </Button>
                    </div>
                </form>
            </Card>
        </AppLayout>
    );
}
