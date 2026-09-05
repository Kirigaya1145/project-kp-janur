@php
    $company = $companyProfile ?? \App\Models\CompanyProfile::first();
    $container = $booking->container->first();
    $origin = $booking->asal ?: optional($booking->rute)->pelabuhan_asal;
    $destination = $booking->tujuan ?: optional($booking->rute)->pelabuhan_tujuan;
    $preferredLogo = public_path($company->logo ?? 'images/logo-janur-nobg.png');
    $fallbackLogo = public_path('images/logo-janur-nobg.png');
    $logoSrc = function_exists('imagecreatefrompng') && file_exists($preferredLogo) ? $preferredLogo : $fallbackLogo;
    $money = fn ($value) => number_format((float) $value, 2, ',', '.');
    $shortDate = fn ($date) => $date ? $date->format('j-M-y') : '-';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 18px 22px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1b1b1b;
            font-size: 14px;
            line-height: 1.25;
        }
        .invoice {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .invoice td,
        .invoice th {
            border: 2px solid #222;
            vertical-align: top;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            border: 0;
            vertical-align: top;
        }
        .logo {
            width: 190px;
            height: auto;
            margin-top: 8px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 6px 0 8px;
        }
        .company-line {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .label-bar {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 4px 6px;
        }
        .bill-name {
            font-size: 22px;
            font-weight: bold;
            padding: 28px 8px;
        }
        .meta td {
            border: 0;
            padding: 4px 6px;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 17px;
            font-weight: bold;
        }
        .meta .value {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            text-align: center;
        }
        .details {
            padding: 18px 14px;
            height: 145px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            border: 0;
            padding: 2px 0;
            font-size: 15px;
        }
        .details-table .colon {
            width: 16px;
            text-align: center;
        }
        .section-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 4px 6px;
        }
        .description {
            height: 105px;
            padding: 22px 14px;
            font-size: 15px;
        }
        .amount-cell {
            height: 105px;
            padding: 18px 14px;
            font-size: 17px;
        }
        .summary-label {
            padding: 8px 14px;
            font-size: 15px;
            height: 30px;
        }
        .summary-amount {
            padding: 4px 14px;
            font-size: 23px;
            height: 30px;
        }
        .payment {
            height: 150px;
            padding: 28px 14px;
            font-size: 16px;
            font-weight: bold;
        }
        .payment table td {
            border: 0;
            padding: 2px 0;
        }
        .signature {
            height: 150px;
            position: relative;
            padding: 0 14px;
        }
        .signature-name {
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 22px;
            text-align: center;
            font-size: 15px;
        }
        .signature-label {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            border-top: 2px solid #222;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            padding-top: 2px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 25%;" class="center">
                <img src="{{ $logoSrc }}" class="logo" alt="Logo">
            </td>
            <td style="width: 75%;">
                <div class="company-name">{{ strtoupper($company->nama_perusahaan ?? 'PT. JANUR TANGGUH ABADI') }}</div>
                <div class="company-line">{{ $company->alamat ?? 'Jl. Ikan Sepat IV No. 26, Tanjung Perak' }}</div>
                <div class="company-line">Surabaya 60177</div>
                <div class="company-line">Telp. {{ $company->telepon ?? '+6231 9901 8632' }}</div>
            </td>
        </tr>
    </table>

    <table class="invoice">
        <tr>
            <td style="width: 55%;" class="label-bar center">BILL TO</td>
            <td style="width: 45%;" rowspan="2">
                <table class="meta">
                    <tr>
                        <td style="width: 38%;">INVOICE DATE</td>
                        <td style="width: 5%;">:</td>
                        <td class="value">{{ $shortDate($invoice->tanggal_invoice) }}</td>
                    </tr>
                    <tr>
                        <td>INVOICE NO.</td>
                        <td>:</td>
                        <td class="value">{{ $invoice->no_invoice }}</td>
                    </tr>
                    <tr>
                        <td>TERMS</td>
                        <td>:</td>
                        <td class="value">{{ $invoice->terms }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="bill-name center">{{ strtoupper($booking->nama_customer) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="height: 18px;"></td>
        </tr>
        <tr>
            <td colspan="2" class="details">
                <table class="details-table">
                    <tr>
                        <td style="width: 18%;">JOA#</td>
                        <td class="colon">:</td>
                        <td style="width: 38%;">{{ $container->joa_number ?? $invoice->no_invoice }}</td>
                        <td style="width: 13%;">Stuff Date</td>
                        <td class="colon">:</td>
                        <td>{{ $shortDate(optional($container)->stuff_date) }}</td>
                    </tr>
                    <tr>
                        <td>Shipping Line</td>
                        <td class="colon">:</td>
                        <td>{{ $container->shipping_line ?? '-' }}</td>
                        <td>ETD SUB</td>
                        <td class="colon">:</td>
                        <td>{{ $shortDate(optional($container)->etd) }}</td>
                    </tr>
                    <tr>
                        <td>Feeder Vessel</td>
                        <td class="colon">:</td>
                        <td>{{ $container->feeder_vessel ?? '-' }}</td>
                        <td>ETA</td>
                        <td class="colon">:</td>
                        <td>{{ $shortDate(optional($container)->eta) }}</td>
                    </tr>
                    <tr>
                        <td>Connecting Vessel</td>
                        <td class="colon">:</td>
                        <td>{{ $container->connecting_vessel ?? '-' }}</td>
                        <td>Cont. no.</td>
                        <td class="colon">:</td>
                        <td>{{ $container->no_container ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Destination</td>
                        <td class="colon">:</td>
                        <td>{{ strtoupper($container->destination ?? $destination ?? '-') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Quantity</td>
                        <td class="colon">:</td>
                        <td>{{ $booking->jumlah_container }} X 20' DRY</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="section-title center">DESCRIPTION</td>
            <td class="section-title center">AMOUNT</td>
        </tr>
        <tr>
            <td class="description">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="border: 0; width: 28%;">Biaya pengiriman dari</td>
                        <td style="border: 0; width: 4%;">:</td>
                        <td style="border: 0; width: 10%;" class="center">1</td>
                        <td style="border: 0; width: 8%;" class="center">X</td>
                        <td style="border: 0;">IDR {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="border: 0;" colspan="5">{{ $origin }} ke {{ $destination }}</td>
                    </tr>
                </table>
            </td>
            <td class="amount-cell">
                <span class="bold">IDR</span>
                <span style="float: right;">{{ $money($invoice->subtotal) }}</span>
            </td>
        </tr>
        <tr>
            <td class="summary-label"></td>
            <td class="summary-amount"><span class="bold">IDR</span><span style="float: right;">{{ $money($invoice->subtotal) }}</span></td>
        </tr>
        <tr>
            <td class="summary-label">PPn {{ number_format((float) $invoice->ppn_persen, 1, ',', '.') }} % <span style="margin-left: 90px;">:</span></td>
            <td class="summary-label"><span class="bold">IDR</span><span style="float: right;">{{ $money($invoice->ppn_nominal) }}</span></td>
        </tr>
        <tr>
            <td class="summary-label"></td>
            <td class="summary-amount"><span class="bold">IDR</span><span style="float: right;" class="bold">{{ $money($invoice->total_bayar) }}</span></td>
        </tr>
        <tr>
            <td class="payment">
                Pembayaran harap ditransfer ke rekening berikut :
                <table style="margin-top: 8px; width: 65%;">
                    <tr>
                        <td style="width: 34%;">Nama</td>
                        <td style="width: 6%;">:</td>
                        <td>PT. Janur Tangguh Abadi</td>
                    </tr>
                    <tr>
                        <td>Rekening</td>
                        <td>:</td>
                        <td>BCA 010.577.5771</td>
                    </tr>
                </table>
            </td>
            <td class="signature">
                <div class="signature-name">( PENANDA TANGAN )</div>
                <div class="signature-label">Authorized Signature</div>
            </td>
        </tr>
    </table>
</body>
</html>
