<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111827; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color:#6b7280; margin-bottom:18px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #d1d5db; padding:7px; text-align:left; }
        th { background:#f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ $report->title }}</h1>
    <div class="muted">Dicetak: {{ $generatedAt->format('d M Y H:i') }}</div>

    <table>
        <thead><tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Minimum</th><th>Lokasi</th></tr></thead>
        <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category }}</td>
                <td>{{ $product->stock }} {{ $product->unit }}</td>
                <td>{{ $product->minimum_stock }}</td>
                <td>{{ $product->location }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
