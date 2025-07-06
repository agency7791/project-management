<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timesheet Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .summary {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Timesheet Report</h1>
        <p>{{ $summary['date_range'] }}</p>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div>
                <strong>Total Hours:</strong> {{ $summary['total_hours'] }}
            </div>
            <div>
                <strong>Billable Hours:</strong> {{ $summary['billable_hours'] }}
            </div>
            <div>
                <strong>Total Entries:</strong> {{ $summary['total_entries'] }}
            </div>
            <div>
                <strong>Report Period:</strong> {{ $summary['date_range'] }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Project</th>
                <th>Client</th>
                <th>Task</th>
                <th>Start</th>
                <th>End</th>
                <th class="text-right">Hours</th>
                <th class="text-center">Billable</th>
            </tr>
        </thead>
        <tbody>
            @foreach($timeEntries as $entry)
            <tr>
                <td>{{ $entry->date }}</td>
                <td>{{ $entry->user->name }}</td>
                <td>{{ $entry->project->name }}</td>
                <td>{{ $entry->project->client->name }}</td>
                <td>{{ $entry->task_name }}</td>
                <td>{{ $entry->start_time }}</td>
                <td>{{ $entry->end_time }}</td>
                <td class="text-right">{{ round($entry->duration_minutes / 60, 2) }}</td>
                <td class="text-center">{{ $entry->is_billable ? 'Yes' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the Project Management System.</p>
    </div>
</body>
</html>