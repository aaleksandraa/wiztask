@php $client = $report['client']; $t = $report['totals']; $cur = $client->currency; @endphp
<table>
    <thead>
        <tr><th colspan="7">Izvještaj — {{ $client->name }}</th></tr>
        <tr>
            <th>Datum</th><th>Projekat</th><th>Task</th><th>Opis</th>
            <th>Status</th><th>Vrijeme (min)</th><th>Cijena ({{ $cur }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report['tasks'] as $task)
            <tr>
                <td>{{ \App\Support\Dates::formatOr($task->task_date) }}</td>
                <td>{{ $task->project->name ?? '-' }}</td>
                <td>{{ $task->title }}</td>
                <td>{{ $task->description }}</td>
                <td>{{ \App\Support\Options::label(\App\Support\Options::TASK_STATUSES, $task->status) }}</td>
                <td>{{ $task->totalMinutes() }}</td>
                <td>{{ number_format((float) $task->total_price, 2, '.', '') }}</td>
            </tr>
        @endforeach
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="5">Ukupno taskova</td><td colspan="2">{{ $t['count'] }}</td></tr>
        <tr><td colspan="5">Ukupno minuta</td><td colspan="2">{{ $t['minutes'] }}</td></tr>
        <tr><td colspan="5">Ukupno za naplatu ({{ $cur }})</td><td colspan="2">{{ number_format($t['billable'], 2, '.', '') }}</td></tr>
        <tr><td colspan="5">Ukupno plaćeno ({{ $cur }})</td><td colspan="2">{{ number_format($t['paid'], 2, '.', '') }}</td></tr>
        <tr><td colspan="5">Ukupno neplaćeno ({{ $cur }})</td><td colspan="2">{{ number_format($t['unpaid'], 2, '.', '') }}</td></tr>
    </tbody>
</table>
