<div class="border rounded-3 p-3 mb-4 bg-white">
<strong>Kontainer:</strong> {{ $booking->jumlah_container }} × {{ $booking->ukuran_container ? $booking->ukuran_container.' feet' : 'Ukuran belum tercatat (booking lama)' }}
@if($booking->harga_satuan_container)<p class="mb-0">Tarif estimasi per kontainer: Rp {{ number_format($booking->harga_satuan_container,0,',','.') }}</p>@endif
<p class="mb-0">Estimasi Waktu Pengiriman (hari): {{ $booking->estimasi_hari ?? '-' }}</p>
@if($booking->tanggal_pengiriman_usulan && $booking->status_harga!=='dikonfirmasi_customer')<p class="mb-0">Usulan tanggal dari admin: {{ $booking->tanggal_pengiriman_usulan->format('d/m/Y') }} (menunggu persetujuan)</p>@endif
@include('partials.whatsapp',['message'=>'Halo, saya ingin konsultasi booking '.$booking->kode_booking.' untuk rute '.$booking->asal.' ke '.$booking->tujuan])
</div>