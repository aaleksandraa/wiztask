<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <title>Izvještaj — {{ $report['client']->name }}</title>
    <style>
        * { font-family: Arial, sans-serif; }
        body { font-size: 13px; color: #1e293b; margin: 30px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #64748b; }
        .meta { margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 7px 9px; text-align: left; }
        th { background: #f1f5f9; font-size: 11px; text-transform: uppercase; }
        td.num, th.num { text-align: right; }
        .totals { margin-top: 18px; width: 320px; margin-left: auto; }
        .totals td { border: none; padding: 3px 8px; }
        .totals .val { text-align: right; font-weight: bold; }
        .toolbar { margin-bottom: 16px; }
        button { padding: 8px 16px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    @php $client = $report['client']; $t = $report['totals']; $f = $report['filters']; $cur = $client->currency; @endphp

    <div class="toolbar">
        <button onclick="window.print()">🖨 Štampaj</button>
    </div>

    <h1>Izvještaj — {{ $client->name }}</h1>
    <div class="meta muted">
        Period:
        {{ !empty($f['date_from']) ? \App\Support\Dates::formatOr($f['date_from']) : 'početak' }}
        –
        {{ !empty($f['date_to']) ? \App\Support\Dates::formatOr($f['date_to']) : \App\Support\Dates::formatOr(now()) }}
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
                    <td><strong>{{ $task->title }}</strong>@if ($task->description)<br><span class="muted">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</span>@endif</td>
                    <td>{{ \App\Support\Options::label(\App\Support\Options::TASK_STATUSES, $task->status) }}</td>
                    <td class="num">{{ \App\Support\Money::minutesToHuman($task->totalMinutes()) }}</td>
                    <td class="num">{{ \App\Support\Money::format($task->total_price, $cur) }}</td>
                    <td>{{ \App\Support\Options::label(\App\Support\Options::PAYMENT_STATUSES, $task->payment_status) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Nema taskova.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Ukupno taskova</td><td class="val">{{ $t['count'] }}</td></tr>
        <tr><td>Ukupno vrijeme</td><td class="val">{{ \App\Support\Money::minutesToHuman($t['minutes']) }}</td></tr>
        <tr><td>Za naplatu</td><td class="val">{{ \App\Support\Money::format($t['billable'], $cur) }}</td></tr>
        <tr><td>Plaćeno</td><td class="val">{{ \App\Support\Money::format($t['paid'], $cur) }}</td></tr>
        <tr><td>Neplaćeno</td><td class="val">{{ \App\Support\Money::format($t['unpaid'], $cur) }}</td></tr>
    </table>
</body>
</html>
