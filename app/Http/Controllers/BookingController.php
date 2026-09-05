<?php

namespace App\Http\Controllers;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $bookings = Booking::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('pages.booking-riwayat', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('pages.booking-create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'nama_customer' => ['required', 'string', 'max:100'],
            'email_customer' => ['nullable', 'email', 'max:100'],
            'no_hp_customer' => ['required', 'string', 'max:20'],
            'asal' => ['required', 'string', 'max:150'],
            'tujuan' => ['required', 'string', 'max:150'],
            'tanggal_pengiriman' => ['required', 'date', 'after_or_equal:today'],
            'jumlah_container' => ['required', 'integer', 'min:1'],
            'barang' => ['required', 'array', 'min:1'],
            'barang.*.nama_barang' => ['required', 'string', 'max:150'],
            'barang.*.kategori_barang' => ['nullable', 'string', 'max:100'],
            'barang.*.qty' => ['required', 'integer', 'min:1'],
            'barang.*.berat_kg' => ['nullable', 'numeric', 'min:0'],
            'barang.*.keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $booking = Booking::create([
                'kode_booking' => $this->generateKodeBooking(),
                'user_id' => Auth::id(),
                'nama_customer' => $validated['nama_customer'],
                'email_customer' => $validated['email_customer'] ?? null,
                'no_hp_customer' => $validated['no_hp_customer'],
                'status_harga' => 'menunggu_penawaran',
                'tanggal_booking' => now()->toDateString(),
                'tanggal_pengiriman' => $validated['tanggal_pengiriman'],
                'jumlah_container' => $validated['jumlah_container'],
                'asal' => $validated['asal'],
                'tujuan' => $validated['tujuan'],
                'total_harga' => 0,
            ]);

            foreach ($validated['barang'] as $item) {
                $booking->barang()->create([
                    'nama_barang' => $item['nama_barang'],
                    'kategori_barang' => $item['kategori_barang'] ?? null,
                    'qty' => $item['qty'],
                    'berat_kg' => $item['berat_kg'] ?? 0,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            session(['last_kode_booking' => $booking->kode_booking]);
        });

        return redirect('/booking/sukses')
            ->with('success', 'Booking berhasil diajukan. Tim kami akan segera menghubungi Anda untuk penawaran harga.');
    }

    public function sukses()
    {
        $kodeBooking = session('last_kode_booking');

        if (! $kodeBooking) {
            return redirect('/booking');
        }

        return view('pages.booking-sukses', compact('kodeBooking'));
    }

    public function formCek()
    {
        return view('pages.booking-cek');
    }

    public function show(string $id)
    {
        //
        $booking = Booking::with(['barang', 'statusHistory' => function ($q) {
                $q->latest();
            }])
            ->where('kode_booking', $id)
            ->first();

        if (! $booking) {
            return back()->with('error', 'Kode booking tidak ditemukan. Periksa kembali kode yang Anda masukkan.');
        }

        return view('pages.booking-cek', compact('booking'));
    }

    private function generateKodeBooking()
    {
        $lastId = (Booking::max('booking_id') ?? 0) + 1;

        return 'BKJ-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
    }

}
