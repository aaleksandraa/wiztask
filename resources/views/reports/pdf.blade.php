<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .muted { color: #64748b; }
        .meta { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; }
        td.num, th.num { text-align: right; }
        .totals { margin-top: 16px; width: 100%; }
        .totals td { border: none; padding: 3px 8px; }
        .totals .label { color: #64748b; }
        .totals .val { text-align: right; font-weight: bold; }
        .badge { font-size: 9px; padding: 2px 5px; background: #e2e8f0; border-radius: 8px; }
    </style>
</head>
<body>
    @php
        $client = $report['client'];
        $t = $report['totals'];
        $f = $report['filters'];
        $cur = $client->currency;
    @endphp

    <h1>Izvještaj — {{ $client->name }}</h1>
    <div class="meta muted">
        Period:
        {{ !empty($f['date_from']) ? \App\Support\Dates::formatOr($f['date_from']) : 'početak' }}
        –
        {{ !empty($f['date_to']) ? \App\Support\Dates::formatOr($f['date_to']) : \App\Support\Dates::formatOr(now()) }}
        <br>
        Generisano: {{ \App\Support\Dates::formatOr(now(), '-', withTime: true) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Datum</th><th>Projekat</th><th>Task</th><th>Status</th>
                <th class="num">Vrijeme</th><th class="num">Cijena</th><th>Plaćanje</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['tasks'] as $task)
                <tr>
                    <td>{{ \App\Support\Dates::formatOr($task->task_date) }}</td>
                    <td>{{ $task->project->name ?? '-' }}</td>
                    <td>
                        <strong>{{ $task->title }}</strong>
                        @if ($task->description)<br><span class="muted">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</span>@endif
                    </td>
                    <td>{{ \App\Support\Options::label(\App\Support\Options::TASK_STATUSES, $task->status) }}</td>
                    <td class="num">{{ \App\Support\Money::minutesToHuman($task->totalMinutes()) }}</td>
                    <td class="num">{{ \App\Support\Money::format($task->total_price, $cur) }}</td>
                    <td>{{ \App\Support\Options::label(\App\Support\Options::PAYMENT_STATUSES, $task->payment_status) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Nema taskova za zadane filtere.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Ukupno taskova</td><td class="val">{{ $t['count'] }}</td></tr>
        <tr><td class="label">Ukupno vrijeme</td><td class="val">{{ \App\Support\Money::minutesToHuman($t['minutes']) }} ({{ $t['minutes'] }} min)</td></tr>
        <tr><td class="label">Ukupno za naplatu</td><td class="val">{{ \App\Support\Money::format($t['billable'], $cur) }}</td></tr>
        <tr><td class="label">Ukupno plaćeno</td><td class="val">{{ \App\Support\Money::format($t['paid'], $cur) }}</td></tr>
        <tr><td class="label">Ukupno neplaćeno</td><td class="val">{{ \App\Support\Money::format($t['unpaid'], $cur) }}</td></tr>
    </table>
</body>
</html>
