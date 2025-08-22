<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Log Aktivitas</title>
    <style>
        body {
            font-family: 'sans-serif';
            font-size: 10px;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .periode {
            text-align: center;
            font-size: 12px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            padding: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Log Aktivitas</h1>
        <p class="periode">
            Dicetak pada: {{ now()->isoFormat('D MMMM Y, HH:mm') }}
        </p>

        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('d M Y, H:i') }}</td>
                        <td>{{ $activity->causer->name ?? 'Sistem' }}</td>
                        <td>{{ $activity->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">Tidak ada aktivitas yang tercatat pada periode yang dipilih.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>