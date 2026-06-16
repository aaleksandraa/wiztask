import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { filtersKey, readListCache, writeListCache } from '@/lib/listCache';
import type { Paginated } from '@/types';

type FetchOptions = {
    preserveState?: boolean;
    preserveScroll?: boolean;
};

export function useOptimisticList<T extends { id: number }>(
    namespace: string,
    filters: Record<string, unknown>,
    serverData: Paginated<T>,
) {
    const filterKey = useMemo(() => filtersKey(filters), [filters]);
    const fullKey = `${namespace}:${filterKey}`;

    const [list, setList] = useState<Paginated<T>>(() => readListCache(fullKey) ?? serverData);
    const [isStale, setIsStale] = useState(false);

    useEffect(() => {
        writeListCache(fullKey, serverData);
        setList(serverData);
        setIsStale(false);
    }, [fullKey, serverData]);

    const fetchList = useCallback(
        (url: string, params: Record<string, string | undefined>, options: FetchOptions = {}) => {
            const cleanParams = Object.fromEntries(
                Object.entries(params).filter(([, v]) => v !== undefined && v !== ''),
            ) as Record<string, string>;

            const nextKey = `${namespace}:${filtersKey(cleanParams)}`;
            const cached = readListCache<Paginated<T>>(nextKey);

            if (cached) {
                setList(cached);
                setIsStale(true);
            }

            router.get(url, cleanParams, {
                preserveState: options.preserveState ?? true,
                preserveScroll: options.preserveScroll ?? false,
                onFinish: () => setIsStale(false),
            });
        },
        [namespace],
    );

    const optimisticRemove = useCallback((id: number) => {
        setList((prev) => ({
            ...prev,
            data: prev.data.filter((item) => item.id !== id),
            meta: { ...prev.meta, total: Math.max(0, prev.meta.total - 1) },
        }));
    }, []);

    const optimisticPatch = useCallback((id: number, patch: Partial<T>) => {
        setList((prev) => ({
            ...prev,
            data: prev.data.map((item) => (item.id === id ? { ...item, ...patch } : item)),
        }));
    }, []);

    return { list, isStale, fetchList, optimisticRemove, optimisticPatch, setList };
}
