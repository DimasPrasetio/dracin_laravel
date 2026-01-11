<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Users Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 9px;
            color: #000;
            padding: 20px;
        }

        .header {
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            font-size: 18px;
            color: #000;
            margin-bottom: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-info {
            margin-top: 8px;
            font-size: 8px;
        }

        .header-info table {
            width: 100%;
            border: none;
            margin: 0;
        }

        .header-info td {
            padding: 2px 0;
            border: none;
        }

        .header-info .label {
            font-weight: bold;
            width: 100px;
        }

        .summary {
            background: #f5f5f5;
            padding: 8px 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
        }

        .summary-label {
            font-weight: bold;
            font-size: 8px;
            display: inline-block;
            margin-right: 5px;
        }

        .summary-value {
            font-size: 9px;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 10px;
        }

        .data-table thead th {
            background: #e0e0e0;
            color: #000;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #000;
        }

        .data-table tbody td {
            padding: 6px;
            font-size: 8px;
            color: #000;
            border: 1px solid #ccc;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #999;
            background: #fff;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 7px;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 9px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Users Report</h1>
        <div class="header-info">
            <table>
                <tr>
                    <td class="label">Report Type:</td>
                    <td>All Users Data</td>
                    <td class="label" style="text-align: right;">Generated:</td>
                    <td style="text-align: right;">{{ $generated_at }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="summary">
        <span class="summary-label">Total Users:</span>
        <span class="summary-value">{{ $total }} records</span>
    </div>

    @if($users->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">No</th>
                    <th style="width: 14%;">Username</th>
                    <th style="width: 20%;">Name</th>
                    <th style="width: 22%;">Email</th>
                    <th style="width: 14%;">Phone</th>
                    <th style="width: 12%;">Role</th>
                    <th style="width: 14%;">Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $index => $u)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?: '-' }}</td>
                        <td>
                            <span class="badge">{{ $u->role }}</span>
                        </td>
                        <td>{{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            No user records found.
        </div>
    @endif

    <div class="footer">
        This report is automatically generated by the system on {{ $generated_at }}
    </div>
</body>
</html>