<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota {{ $transaction->code }}</title>
    <style>
        @page {
            margin: 0;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
            width: 280px;
            margin: 0 auto;
            padding: 16px;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .muted { color: #6b7280; font-size: 10px; }
        hr {
            border: none;
            border-top: 1px dashed #9ca3af;
            margin: 8px 0;
        }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .item-name { width: 60%; }
        .item-qty { width: 15%; text-align: center; }
        .item-price { width: 25%; text-align: right; }
        .total-row td { padding-top: 4px; }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #1f2937;
            border-bottom: 3px double #1f2937;
            padding: 6px 0;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            background: #dbeafe;
            color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="center">
        <div class="bold" style="font-size: 15px;">{{ setting('business_name', 'Laundry Desa') }}</div>
        <div class="muted">{{ setting('business_address', '') }}</div>
        <div class="muted">{{ setting('business_whatsapp', '') }}</div>
    </div>

    <hr>

    <table>
        <tr>
            <td>No. Transaksi</td>
            <td class="right bold">{{ $transaction->code }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td class="right">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td class="right">{{ $transaction->cashier->name }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td class="right">{{ $transaction->customer->name ?? 'Pelanggan Umum' }}</td>
        </tr>
        @if($transaction->customer && $transaction->customer->phone)
        <tr>
            <td>No. HP</td>
            <td class="right">{{ $transaction->customer->phone }}</td>
        </tr>
        @endif
    </table>

    <hr>

    <table>
        @foreach($transaction->items as $item)
        <tr>
            <td class="item-name">
                {{ $item->service_name }}<br>
                <span class="muted">{{ $item->qty }} {{ $item->unit_type }} x Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</span>
            </td>
            <td class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <hr>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->discount > 0)
        <tr>
            <td>Diskon</td>
            <td class="right">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->delivery_fee > 0)
        <tr>
            <td>Ongkir @if($transaction->use_pickup && $transaction->use_delivery) (Jemput + Antar) @elseif($transaction->use_pickup) (Jemput) @elseif($transaction->use_delivery) (Antar) @endif</td>
            <td class="right">Rp {{ number_format($transaction->delivery_fee, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="grand-total">TOTAL</td>
            <td class="right grand-total">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Dibayar</td>
            <td class="right">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->remaining > 0)
        <tr>
            <td class="bold">Sisa Tagihan</td>
            <td class="right bold">Rp {{ number_format($transaction->remaining, 0, ',', '.') }}</td>
        </tr>
        @elseif($transaction->change > 0)
        <tr>
            <td>Kembalian</td>
            <td class="right">Rp {{ number_format($transaction->change, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <hr>

    <table>
        <tr>
            <td>Status Pembayaran</td>
            <td class="right">
                <span class="badge">{{ ucwords(str_replace('_', ' ', $transaction->payment_status)) }}</span>
            </td>
        </tr>
        <tr>
            <td>Status Laundry</td>
            <td class="right">
                <span class="badge">{{ ucwords(str_replace('_', ' ', $transaction->status)) }}</span>
            </td>
        </tr>
        @if($transaction->estimated_done_at)
        <tr>
            <td>Estimasi Selesai</td>
            <td class="right">{{ $transaction->estimated_done_at->translatedFormat('d M Y') }}</td>
        </tr>
        @endif
    </table>

    <hr>

    <div class="center muted">
        Terima kasih telah menggunakan jasa kami.<br>
        Simpan nota ini sebagai bukti transaksi.
    </div>

    @if(isset($qrCodeSvg))
    <div class="center" style="margin-top: 10px;">
        {!! $qrCodeSvg !!}
        <div class="muted">Scan untuk cek status laundry</div>
    </div>
    @endif
</body>
</html>