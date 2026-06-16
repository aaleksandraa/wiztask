const PREFIX = 'wiztask:list:';
const TTL_MS = 5 * 60 * 1000;

type CacheEntry<T> = {
    data: T;
    savedAt: number;
};

export function filtersKey(filters: Record<string, unknown>): string {
    return Object.keys(filters)
        .sort()
        .map((key) => `${key}=${String(filters[key] ?? '')}`)
        .join('&');
}

export function readListCache<T>(key: string): T | null {
    try {
        const raw = sessionStorage.getItem(PREFIX + key);
        if (!raw) return null;

        const entry = JSON.parse(raw) as CacheEntry<T>;
        if (Date.now() - entry.savedAt > TTL_MS) {
            sessionStorage.removeItem(PREFIX + key);
            return null;
        }

        return entry.data;
    } catch {
        return null;
    }
}

export function writeListCache<T>(key: string, data: T): void {
    try {
        const entry: CacheEntry<T> = { data, savedAt: Date.now() };
        sessionStorage.setItem(PREFIX + key, JSON.stringify(entry));
    } catch {
        // sessionStorage pun ili nedostupan
    }
}

export function invalidateListCache(namespace: string): void {
    try {
        const keys: string[] = [];
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key?.startsWith(PREFIX + namespace + ':')) {
                keys.push(key);
            }
        }
        keys.forEach((k) => sessionStorage.removeItem(k));
    } catch {
        //
    }
}
