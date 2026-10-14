<div class="my-3"><strong>Status: {{ $invoice->status_bayar==='lunas'?'Lunas':'Belum Lunas' }}</strong>
@if($invoice->status_bayar==='lunas')
<p class="mb-1">Tanggal pelunasan: {{ optional($invoice->tanggal_lunas)->format('d/m/Y') ?? '-' }}</p>
<img src="{{ asset('images/signature-dummy.jpg') }}" width="145" alt="Tanda tangan dummy"><p class="small text-steel">Tanda tangan contoh</p>
@endif</div>