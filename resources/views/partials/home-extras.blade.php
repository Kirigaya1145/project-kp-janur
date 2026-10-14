<section class="container py-5"><h2 class="h3 text-navy">Pengiriman yang Lebih Mudah</h2>
<div class="row g-3 my-3">@foreach(['Pilihan Kontainer'=>'Pilih ukuran 20 atau 40 feet sesuai kebutuhan pengiriman.','Estimasi Transparan'=>'Lihat estimasi berdasarkan rute, ukuran, dan jumlah kontainer.','Konsultasi Jadwal'=>'Diskusikan penawaran harga dan jadwal bersama tim kami.'] as $title=>$description)
<div class="col-md-4"><div class="border rounded-3 p-4 h-100"><h3 class="h5">{{ $title }}</h3><p>{{ $description }}</p></div></div>@endforeach</div>
<div class="row g-3 mb-4"><div class="col-md-6"><img class="w-100 rounded-3 home-media" loading="lazy" src="{{ asset('images/port-containers.jpg') }}" alt="Kapal dan kontainer di pelabuhan Jakarta"><small>Ilustrasi — Tom Fisk / Pexels</small></div>
<div class="col-md-6"><img class="w-100 rounded-3 home-media" loading="lazy" src="{{ asset('images/port-sunset.jpg') }}" alt="Aktivitas pelabuhan saat senja"><small>Ilustrasi — Tom Fisk / Pexels</small></div></div>
<div class="mb-4"><h2 class="h4 text-navy">Aktivitas Pelabuhan</h2>
<video class="rounded-3 home-video" controls preload="none" poster="{{ asset('images/port-containers.jpg') }}">
<source src="{{ asset('videos/port-dummy.mp4') }}" type="video/mp4">
Browser Anda tidak mendukung video.</video>
<p class="small text-steel">Video ilustrasi — Glenn Langhorst / Pexels. <a href="https://www.pexels.com/video/cargo-containers-in-a-port-9211525/" target="_blank" rel="noopener noreferrer">Lihat sumber video</a></p></div>
<h2 class="h3 text-navy">Testimoni Pelanggan</h2><p class="small text-steel">Data dummy untuk demonstrasi tampilan.</p>
<div class="row g-3">@foreach(['Andi — Distributor'=>'Pilihan ukuran kontainer dan rincian estimasinya mudah dipahami.','Sari — Pemilik Usaha'=>'Kami bisa mendiskusikan jadwal pengiriman sebelum menyetujui penawaran.','Budi — Tim Pengadaan'=>'Riwayat booking membantu kami memantau proses pengiriman.'] as $name=>$quote)
<div class="col-md-4"><blockquote class="border rounded-3 p-4 h-100"><p>“{{ $quote }}”</p><footer>{{ $name }} <small>(dummy)</small></footer></blockquote></div>@endforeach</div>
<div class="text-center mt-4">@include('partials.whatsapp')</div></section>