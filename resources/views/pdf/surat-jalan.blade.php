@php
    $company = $companyProfile ?? \App\Models\CompanyProfile::first();
    $shortDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->translatedFormat('d F Y') : '-';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 24px 28px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1b1b1b;
            font-size: 13px;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            border: 0;
            vertical-align: top;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
        }
        .company-line {
            font-size: 12px;
        }
        .title-bar {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            border: 2px solid #222;
            padding: 8px;
            margin: 14px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .info-table td {
            border: 0;
            padding: 3px 0;
            vertical-align: top;
        }
        .info-table .label {
            width: 30%;
        }
        .info-table .colon {
            width: 3%;
        }
        .barang-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .barang-table th, .barang-table td {
            border: 1px solid #222;
            padding: 6px 8px;
            font-size: 12px;
        }
        .barang-table th {
            background: #eee;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .signature-table td {
            border: 0;
            width: 33%;
            text-align: center;
            vertical-align: top;
        }
        .signature-box {
            height: 70px;
        }
        .signature-line {
            border-top: 1px solid #222;
            padding-top: 4px;
            margin: 0 20px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="company-name">{{ strtoupper($company->nama_perusahaan ?? 'PT. JANUR TANGGUH ABADI') }}</div>
                <div class="company-line">{{ $company->alamat ?? 'Jl. Ikan Sepat IV No. 26, Tanjung Perak, Surabaya' }}</div>
                <div class="company-line">Telp. {{ $company->telepon ?? '+6231 9901 8632' }}</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div class="company-line">No: {{ $suratJalan->no_surat_jalan }}</div>
                <div class="company-line">Tanggal: {{ $shortDate($suratJalan->tanggal) }}</div>
            </td>
        </tr>
    </table>

    <div class="title-bar">SURAT JALAN</div>

    <table class="info-table">
        <tr>
            <td class="label">Kepada Yth.</td>
            <td class="colon">:</td>
            <td>{{ $suratJalan->penerima_kepada ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi Penerima</td>
            <td class="colon">:</td>
            <td>{{ $suratJalan->lokasi_penerima ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kendaraan</td>
            <td class="colon">:</td>
            <td>{{ $suratJalan->kendaraan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">No. Polisi</td>
            <td class="colon">:</td>
            <td>{{ $suratJalan->nopol_kendaraan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Sopir</td>
            <td class="colon">:</td>
            <td>{{ $suratJalan->nama_sopir ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kode Booking</td>
            <td class="colon">:</td>
            <td>{{ $booking->kode_booking }}</td>
        </tr>
        <tr>
            <td class="label">Rute</td>
            <td class="colon">:</td>
            <td>{{ $booking->asal }} &rarr; {{ $booking->tujuan }}</td>
        </tr>
    </table>

    <table class="barang-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th>Nama Barang</th>
                <th style="width: 20%;">Kategori</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 15%;">Berat (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->barang as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kategori_barang ?? '-' }}</td>
                    <td style="text-align: center;">{{ $item->qty }}</td>
                    <td style="text-align: right;">{{ number_format($item->berat_kg, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-box"></div>
                <div class="signature-line">
                    {{ $suratJalan->nama_pengirim ?? '(...........................)' }}<br>
                    Pengirim
                </div>
            </td>
            <td>
                <div class="signature-box"></div>
                <div class="signature-line">
                    Sopir<br>
                    ({{ $suratJalan->nama_sopir ?? '...........................' }})
                </div>
            </td>
            <td>
                <div class="signature-box"></div>
                <div class="signature-line">
                    {{ $suratJalan->nama_penerima_ttd ?? '(...........................)' }}<br>
                    Penerima
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
