<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rute_harga')) {
            Schema::create('rute_harga', function (Blueprint $table) {
                $table->bigIncrements('rute_id');
                $table->string('pelabuhan_asal', 150);
                $table->string('pelabuhan_tujuan', 150);
                $table->decimal('harga_dasar', 12)->default(0);
                $table->text('keterangan')->nullable();
                $table->unique(['pelabuhan_asal', 'pelabuhan_tujuan']);
            });
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->bigIncrements('booking_id');
                $table->string('kode_booking', 20)->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('nama_customer', 100);
                $table->string('email_customer', 100)->nullable();
                $table->string('no_hp_customer', 20)->nullable();
                $table->unsignedBigInteger('rute_id')->nullable();
                $table->decimal('harga_estimasi', 12)->nullable();
                $table->decimal('harga_final', 12)->nullable();
                $table->enum('status_harga', ['menunggu_penawaran', 'sudah_ditawarkan', 'dikonfirmasi_customer', 'ditolak_customer'])->default('menunggu_penawaran');
                $table->string('status_booking', 50)->default('menunggu_penawaran');
                $table->unsignedBigInteger('diberikan_oleh')->nullable();
                $table->dateTime('tanggal_konfirmasi')->nullable();
                $table->string('estimasi_waktu', 50)->nullable();
                $table->date('tanggal_booking')->nullable();
                $table->date('tanggal_pengiriman')->nullable();
                $table->dateTime('waktu_muat')->nullable();
                $table->integer('jumlah_container')->default(1);
                $table->string('asal', 150)->nullable();
                $table->string('tujuan', 150)->nullable();
                $table->decimal('total_harga', 12)->default(0);
                $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
                $table->foreign('rute_id')->references('rute_id')->on('rute_harga')->nullOnDelete();
                $table->foreign('diberikan_oleh')->references('user_id')->on('users')->nullOnDelete();
            });
        } elseif (! Schema::hasColumn('bookings', 'status_booking')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('status_booking', 50)->default('menunggu_penawaran')->after('status_harga');
            });
        }

        if (! Schema::hasTable('booking_barang')) {
            Schema::create('booking_barang', function (Blueprint $table) {
                $table->bigIncrements('booking_barang_id');
                $table->unsignedBigInteger('booking_id');
                $table->string('kategori_barang', 100)->nullable();
                $table->string('nama_barang', 150);
                $table->integer('qty')->default(1);
                $table->decimal('berat_kg', 8, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('invoice')) {
            Schema::create('invoice', function (Blueprint $table) {
                $table->bigIncrements('invoice_id');
                $table->string('no_invoice', 50)->unique();
                $table->unsignedBigInteger('booking_id');
                $table->date('tanggal_invoice');
                $table->decimal('subtotal', 12)->default(0);
                $table->decimal('ppn_persen', 5, 2)->default(0);
                $table->decimal('ppn_nominal', 12)->default(0);
                $table->decimal('total_bayar', 12)->default(0);
                $table->string('terms', 20)->default('CASH');
                $table->enum('status_bayar', ['belum_lunas', 'lunas', 'sebagian'])->default('belum_lunas');
                $table->date('tanggal_lunas')->nullable();
                $table->text('catatan')->nullable();
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('bukti_pembayaran')) {
            Schema::create('bukti_pembayaran', function (Blueprint $table) {
                $table->bigIncrements('bukti_id');
                $table->unsignedBigInteger('invoice_id');
                $table->decimal('jumlah_bayar', 12);
                $table->string('file_bukti');
                $table->dateTime('tanggal_upload')->useCurrent();
                $table->enum('status_konfirmasi', ['menunggu', 'dikonfirmasi', 'ditolak'])->default('menunggu');
                $table->unsignedBigInteger('dikonfirmasi_oleh')->nullable();
                $table->dateTime('tanggal_konfirmasi')->nullable();
                $table->text('catatan_admin')->nullable();
                $table->foreign('invoice_id')->references('invoice_id')->on('invoice')->cascadeOnDelete();
                $table->foreign('dikonfirmasi_oleh')->references('user_id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('booking_container')) {
            Schema::create('booking_container', function (Blueprint $table) {
                $table->bigIncrements('container_id');
                $table->unsignedBigInteger('booking_id');
                $table->string('joa_number', 50)->nullable();
                $table->string('no_container', 50)->nullable();
                $table->string('shipping_line', 100)->nullable();
                $table->string('feeder_vessel', 100)->nullable();
                $table->string('connecting_vessel', 100)->nullable();
                $table->string('destination', 150)->nullable();
                $table->date('stuff_date')->nullable();
                $table->date('etd')->nullable();
                $table->date('eta')->nullable();
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('surat_jalan')) {
            Schema::create('surat_jalan', function (Blueprint $table) {
                $table->bigIncrements('surat_jalan_id');
                $table->string('no_surat_jalan', 50)->unique();
                $table->unsignedBigInteger('booking_id');
                $table->date('tanggal');
                $table->string('kendaraan', 50)->nullable();
                $table->string('nopol_kendaraan', 20)->nullable();
                $table->string('nama_sopir', 100)->nullable();
                $table->string('penerima_kepada', 150)->nullable();
                $table->string('lokasi_penerima', 150)->nullable();
                $table->string('nama_pengirim', 100)->nullable();
                $table->string('nama_penerima_ttd', 100)->nullable();
                $table->date('tanggal_diterima')->nullable();
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('status_bookings')) {
            Schema::create('status_bookings', function (Blueprint $table) {
                $table->bigIncrements('status_id');
                $table->unsignedBigInteger('booking_id');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->enum('status', ['diproses', 'selesai', 'dibatalkan'])->default('diproses');
                $table->text('keterangan')->nullable();
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
                $table->foreign('updated_by')->references('user_id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('status_bookings');
        Schema::dropIfExists('surat_jalan');
        Schema::dropIfExists('booking_container');
        Schema::dropIfExists('bukti_pembayaran');
        Schema::dropIfExists('invoice');
        Schema::dropIfExists('booking_barang');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('rute_harga');
    }
};
