import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    CheckSquare,
    Clock,
    FolderKanban,
    LayoutDashboard,
    LogOut,
    Menu,
    Settings,
    User,
    Users,
    X,
} from 'lucide-react';
import { useState, type ReactNode } from 'react';
import Flash from '@/Components/ui/Flash';
import { routes } from '@/lib/routes';
import { cn } from '@/lib/utils';
import type { SharedProps } from '@/types';

type NavItem = {
    href: string;
    label: string;
    icon: ReactNode;
    match: (path: string) => boolean;
};

const nav: NavItem[] = [
    { href: routes.dashboard(), label: 'Dashboard', icon: <LayoutDashboard className="h-5 w-5" strokeWidth={1.75} />, match: (p) => p === '/dashboard' },
    { href: routes.clients.index(), label: 'Klijenti', icon: <Users className="h-5 w-5" strokeWidth={1.75} />, match: (p) => p.startsWith('/klijenti') },
    { href: routes.projects.index(), label: 'Projekti', icon: <FolderKanban className="h-5 w-5" strokeWidth={1.75} />, match: (p) => p.startsWith('/projekti') },
    { href: routes.tasks.index(), label: 'Taskovi', icon: <CheckSquare className="h-5 w-5" strokeWidth={1.75} />, match: (p) => p.startsWith('/taskovi') },
    { href: routes.time.index(), label: 'Vrijeme', icon: <Clock className="h-5 w-5" strokeWidth={1.75} />, match: (p) => p.startsWith('/vrijeme') },
    { href: routes.reports.index(), label: 'Izvještaji', icon: <BarChart3 className="h-5 w-5" strokeWidth={1.75} />, match: (p) => p.startsWith('/izvjestaji') },
    { href: routes.settings.edit(), label: 'Podešavanja', icon: <Settings className="h-5 w-5" strokeWidth={1.75} />, match: (p) => p.startsWith('/podesavanja') },
];

type Props = {
    children: ReactNode;
};

export default function AppLayout({ children }: Props) {
    const { auth, appName } = usePage<SharedProps>().props;
    const { url } = usePage();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);
    const path = url.split('?')[0];

    const userInitial = auth.user?.name?.charAt(0).toUpperCase() ?? '?';

    return (
        <div className="min-h-screen">
            {sidebarOpen && (
                <div className="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden" onClick={() => setSidebarOpen(false)} aria-hidden />
            )}

            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-40 flex w-[17.5rem] transform flex-col border-r border-white/[0.06] bg-[#0c0c0e]/95 shadow-float backdrop-blur-xl transition-transform duration-300 lg:translate-x-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                <div className="flex h-16 items-center gap-3 border-b border-white/[0.06] px-5">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-bold text-white shadow-soft">
                        W
                    </div>
                    <div className="min-w-0 flex-1">
                        <span className="block truncate text-base font-bold tracking-tight text-white">{appName}</span>
                        <span className="block text-[11px] font-medium text-neutral-500">Task manager</span>
                    </div>
                    <button type="button" className="rounded-lg p-1.5 text-neutral-500 hover:bg-white/5 lg:hidden" onClick={() => setSidebarOpen(false)}>
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <p className="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-neutral-600">Meni</p>
                    {nav.map((item) => {
                        const active = item.match(path);
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                prefetch="hover"
                                className={cn('nav-item', active ? 'nav-item-active' : 'nav-item-inactive')}
                                onClick={() => setSidebarOpen(false)}
                            >
                                {item.icon}
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>

                <div className="border-t border-white/[0.06] px-4 py-4">
                    <div className="flex items-center gap-3 rounded-xl border border-white/[0.06] bg-white/[0.03] px-3 py-2.5">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-semibold text-white">
                            {userInitial}
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-semibold text-neutral-100">{auth.user?.name}</p>
                            <p className="truncate text-xs text-neutral-500">{auth.user?.email}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <div className="lg:pl-[17.5rem]">
                <header className="sticky top-0 z-20 border-b border-white/[0.06] bg-[#09090b]/80 backdrop-blur-xl">
                    <div className="flex h-14 items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(true)}
                            className="rounded-xl p-2 text-neutral-400 transition hover:bg-white/5 lg:hidden"
                        >
                            <Menu className="h-5 w-5" />
                        </button>
                        <div className="flex-1" />
                        <div className="relative">
                            <button
                                type="button"
                                onClick={() => setMenuOpen(!menuOpen)}
                                className="flex items-center gap-2 rounded-xl border border-white/10 bg-neutral-900/80 px-2 py-1.5 pl-1.5 text-sm shadow-sm transition hover:border-white/15 hover:bg-neutral-900"
                            >
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white">
                                    {userInitial}
                                </span>
                                <span className="hidden max-w-[120px] truncate font-medium text-neutral-200 sm:block">{auth.user?.name}</span>
                            </button>
                            {menuOpen && (
                                <>
                                    <div className="fixed inset-0 z-10" onClick={() => setMenuOpen(false)} />
                                    <div className="absolute right-0 top-full z-20 mt-2 w-52 animate-scale-in overflow-hidden rounded-xl border border-white/10 bg-neutral-900 py-1 shadow-float">
                                        <Link href={routes.profile()} className="flex items-center gap-2 px-4 py-2.5 text-sm text-neutral-300 transition hover:bg-white/5" onClick={() => setMenuOpen(false)}>
                                            <User className="h-4 w-4 text-neutral-500" />
                                            Profil
                                        </Link>
                                        <Link href={routes.settings.edit()} className="flex items-center gap-2 px-4 py-2.5 text-sm text-neutral-300 transition hover:bg-white/5" onClick={() => setMenuOpen(false)}>
                                            <Settings className="h-4 w-4 text-neutral-500" />
                                            Podešavanja
                                        </Link>
                                        <div className="my-1 border-t border-white/[0.06]" />
                                        <Link
                                            href={routes.logout()}
                                            method="post"
                                            as="button"
                                            className="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-400 transition hover:bg-red-500/10"
                                            onClick={() => setMenuOpen(false)}
                                        >
                                            <LogOut className="h-4 w-4" />
                                            Odjava
                                        </Link>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                </header>

                <div className="page-container px-4 pt-4 sm:px-6 lg:px-8">
                    <Flash />
                </div>

                <main className="page-container animate-fade-in px-4 pb-10 pt-2 sm:px-6 lg:px-8">{children}</main>
            </div>
        </div>
    );
}
