<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 20px;
        }

        .header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0081C9;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            color: #0081C9;
            margin: 0 0 6px 0;
        }

        .meta {
            font-size: 10px;
            color: #6b7280;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #EEF7FF;
            color: #0f4c75;
            font-weight: 700;
        }

        .empty {
            color: #6b7280;
            font-style: italic;
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="meta">Outlet: {{ $meta['store'] ?? 'Semua Outlet' }} | Periode: {{ $meta['period'] ?? '-' }} |
            Dicetak: {{ $generatedAt }}</div>
    </div>

    @foreach ($rows as $section)
        <div class="section">
            <div class="section-title">{{ $section['title'] }}</div>
            @if (empty($section['rows']))
                <div class="empty">Tidak ada data.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            @foreach ($section['headers'] as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</body>

</html>
