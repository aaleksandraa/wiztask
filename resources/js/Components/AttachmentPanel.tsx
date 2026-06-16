import { useCallback, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import axios from '@/bootstrap';
import { cn, label } from '@/lib/utils';
import { Attachment, SharedProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { FileText, Loader2, Trash2, Upload, X, ZoomIn } from 'lucide-react';
import Badge from './ui/Badge';
import Card from './ui/Card';
import Select from './ui/Select';

type Props = {
    type: 'task' | 'project';
    id: number;
    initial: Attachment[];
    title?: string;
};

type UploadItem = {
    key: string;
    name: string;
    progress: 'uploading' | 'error';
    error?: string;
};

export default function AttachmentPanel({ type, id, initial, title = 'Fajlovi i dokazi rada' }: Props) {
    const { options } = usePage<SharedProps>().props;
    const [items, setItems] = useState<Attachment[]>(initial);
    const [category, setCategory] = useState('dokaz');
    const [description, setDescription] = useState('');
    const [uploading, setUploading] = useState<UploadItem[]>([]);
    const [preview, setPreview] = useState<Attachment | null>(null);

    const uploadFile = useCallback(
        async (file: File) => {
            const key = `${file.name}-${Date.now()}`;
            setUploading((prev) => [...prev, { key, name: file.name, progress: 'uploading' }]);

            const form = new FormData();
            form.append('type', type);
            form.append('id', String(id));
            form.append('file', file);
            form.append('category', category);
            if (description) form.append('description', description);

            try {
                const { data } = await axios.post<{ attachment: Attachment }>('/api/attachments', form, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                setItems((prev) => [data.attachment, ...prev]);
                setUploading((prev) => prev.filter((u) => u.key !== key));
            } catch (err: unknown) {
                const message =
                    axios.isAxiosError(err) && err.response?.data?.message
                        ? String(err.response.data.message)
                        : 'Upload nije uspio.';
                setUploading((prev) =>
                    prev.map((u) => (u.key === key ? { ...u, progress: 'error', error: message } : u)),
                );
                setTimeout(() => setUploading((prev) => prev.filter((u) => u.key !== key)), 4000);
            }
        },
        [category, description, id, type],
    );

    const onDrop = useCallback(
        (accepted: File[]) => {
            accepted.forEach((file) => uploadFile(file));
        },
        [uploadFile],
    );

    const { getRootProps, getInputProps, isDragActive, open } = useDropzone({
        onDrop,
        noClick: true,
        multiple: true,
    });

    const remove = async (attachment: Attachment) => {
        if (!confirm('Obrisati fajl?')) return;
        await axios.delete(`/api/attachments/${attachment.id}`);
        setItems((prev) => prev.filter((a) => a.id !== attachment.id));
    };

    const categories = Object.entries(options.attachmentCategories).map(([value, lbl]) => ({
        value,
        label: lbl,
    }));

    return (
        <Card title={title}>
            <div className="space-y-4">
                <div className="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-neutral-500">
                            Kategorija
                        </label>
                        <Select
                            value={category}
                            onChange={(e) => setCategory(e.target.value)}
                            options={categories}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-neutral-500">
                            Opis (opcionalno)
                        </label>
                        <input
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                            placeholder="Kratak opis fajlova"
                            className="field-base"
                        />
                    </div>
                </div>

                <div
                    {...getRootProps()}
                    className={cn(
                        'relative rounded-2xl border-2 border-dashed p-10 text-center transition duration-200',
                        isDragActive
                            ? 'border-brand-500 bg-brand-500/10 scale-[1.01]'
                            : 'border-white/10 bg-white/[0.02] hover:border-brand-500/40 hover:bg-brand-500/5',
                    )}
                >
                    <input {...getInputProps()} />
                    <Upload className={cn('mx-auto h-9 w-9 transition', isDragActive ? 'text-brand-400' : 'text-neutral-500')} />
                    <p className="mt-3 text-sm font-semibold text-neutral-200">Prevuci fajlove ovdje</p>
                    <p className="mt-1 text-xs text-neutral-500">ili klikni za odabir — upload odmah</p>
                    <p className="mt-1 text-xs text-neutral-400">Slike, PDF, dokumenti · max 20MB</p>
                    <button
                        type="button"
                        onClick={open}
                        className="mt-4 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-medium text-neutral-900 hover:bg-neutral-100"
                    >
                        Odaberi fajlove
                    </button>
                </div>

                {uploading.length > 0 && (
                    <div className="space-y-2">
                        {uploading.map((u) => (
                            <div
                                key={u.key}
                                className="flex items-center gap-2 rounded-lg border border-white/[0.06] bg-white/[0.03] px-3 py-2 text-sm text-neutral-300"
                            >
                                {u.progress === 'uploading' ? (
                                    <Loader2 className="h-4 w-4 animate-spin text-neutral-500" />
                                ) : (
                                    <X className="h-4 w-4 text-red-500" />
                                )}
                                <span className="truncate">{u.name}</span>
                                {u.error && <span className="text-xs text-red-500">{u.error}</span>}
                            </div>
                        ))}
                    </div>
                )}

                {items.length === 0 && uploading.length === 0 ? (
                    <p className="py-6 text-center text-sm text-neutral-400">Još nema priloženih fajlova.</p>
                ) : (
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        {items.map((att) => (
                            <div
                                key={att.id}
                                className="group overflow-hidden rounded-xl border border-white/10 bg-neutral-900/60 shadow-sm transition hover:border-white/15 hover:shadow-md"
                            >
                                <div className="relative flex h-32 items-center justify-center bg-neutral-950/80">
                                    {att.is_image ? (
                                        <>
                                            <img
                                                src={att.url}
                                                alt={att.original_name}
                                                className="h-full w-full object-cover"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setPreview(att)}
                                                className="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition group-hover:bg-black/30 group-hover:opacity-100"
                                            >
                                                <ZoomIn className="h-8 w-8 text-white" />
                                            </button>
                                        </>
                                    ) : (
                                        <FileText className="h-10 w-10 text-neutral-400" />
                                    )}
                                </div>
                                <div className="p-2.5">
                                    <p className="truncate text-xs font-medium" title={att.original_name}>
                                        {att.original_name}
                                    </p>
                                    <div className="mt-1.5 flex items-center justify-between gap-1">
                                        <Badge value={att.category} map={options.attachmentCategories} className="text-[10px]" />
                                        <span className="text-[10px] text-neutral-400">{att.human_size}</span>
                                    </div>
                                    {att.description && (
                                        <p className="mt-1 truncate text-[11px] text-neutral-400">{att.description}</p>
                                    )}
                                    <div className="mt-2 flex items-center justify-between">
                                        <a
                                            href={att.download_url}
                                            className="text-xs font-medium text-neutral-200 hover:underline"
                                        >
                                            Download
                                        </a>
                                        <button
                                            type="button"
                                            onClick={() => remove(att)}
                                            className="text-neutral-400 hover:text-red-600"
                                        >
                                            <Trash2 className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {preview && (
                <div
                    className="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4"
                    onClick={() => setPreview(null)}
                >
                    <button
                        type="button"
                        className="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20"
                        onClick={() => setPreview(null)}
                    >
                        <X className="h-6 w-6" />
                    </button>
                    <img
                        src={preview.url}
                        alt={preview.original_name}
                        className="max-h-[90vh] max-w-full rounded-lg object-contain"
                        onClick={(e) => e.stopPropagation()}
                    />
                </div>
            )}
        </Card>
    );
}
