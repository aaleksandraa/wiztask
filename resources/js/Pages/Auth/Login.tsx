import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import Button from '@/Components/ui/Button';
import Input from '@/Components/ui/Input';
import { routes } from '@/lib/routes';
import type { SharedProps } from '@/types';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    const { appName } = usePage<SharedProps>().props;

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(routes.login(), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Prijava" />

            <div className="mb-6">
                <h2 className="text-lg font-bold text-heading">Prijava</h2>
                <p className="mt-1 text-sm text-muted">Pristup {appName} admin panelu</p>
            </div>

            {status && (
                <div className="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <Input
                    label="Email"
                    id="email"
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    autoComplete="username"
                    autoFocus
                    required
                />

                <Input
                    label="Lozinka"
                    id="password"
                    type="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                    autoComplete="current-password"
                    required
                />

                <label className="flex cursor-pointer items-center gap-2">
                    <input
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                        className="checkbox-base"
                    />
                    <span className="text-sm text-neutral-400">Zapamti me</span>
                </label>

                <Button type="submit" className="w-full" disabled={processing}>
                    {processing ? 'Prijava...' : 'Prijavi se'}
                </Button>

                {canResetPassword && (
                    <p className="text-center text-sm">
                        <Link href={routes.password.request()} className="text-neutral-400 hover:text-neutral-200 hover:underline">
                            Zaboravljena lozinka?
                        </Link>
                    </p>
                )}
            </form>

            <p className="mt-6 border-t border-white/[0.06] pt-4 text-center text-xs text-neutral-500">
                Demo: admin@wiztask.test / password
            </p>
        </GuestLayout>
    );
}
