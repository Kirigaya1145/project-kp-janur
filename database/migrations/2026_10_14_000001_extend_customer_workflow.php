<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        foreach (['email_verified_at', 'remember_token'] as $column) {
            if (!Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $t) use ($column) {
                    if ($column === 'email_verified_at') $t->timestamp($column)->nullable();
                    else $t->rememberToken();
                });
            }
        }
        // Existing accounts keep access; all new registrations must verify email.
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at'=>now()]);
        if (!Schema::hasTable('tarif_container')) {
            Schema::create('tarif_container', function(Blueprint $t) {
                $t->id('tarif_id');
                $t->unsignedBigInteger('rute_id');
                $t->unsignedSmallInteger('ukuran_feet');
                $t->decimal('harga',12,2);
                $t->unique(['rute_id','ukuran_feet']);
                $t->foreign('rute_id')->references('rute_id')->on('rute_harga')->cascadeOnDelete();
            });
            // Legacy price represents 20 feet only; 40 feet requires an explicit admin tariff.
            foreach(DB::table('rute_harga')->get() as $rute) {
                if ($rute->harga_dasar > 0) DB::table('tarif_container')->insert([
                    'rute_id'=>$rute->rute_id,'ukuran_feet'=>20,'harga'=>$rute->harga_dasar
                ]);
            }
        }
        Schema::table('bookings', function(Blueprint $t) {
            $t->unsignedSmallInteger('ukuran_container')->nullable();
            $t->decimal('harga_satuan_container',12,2)->nullable();
            $t->unsignedSmallInteger('estimasi_hari')->nullable();
            $t->date('tanggal_pengiriman_usulan')->nullable();
            $t->text('catatan_penolakan')->nullable();
            $t->unsignedInteger('versi_penawaran')->default(0);
        });
        DB::table('bookings')->whereNotNull('harga_final')->update(['versi_penawaran'=>1]);
        foreach(DB::table('bookings')->get() as $b) {
            if (preg_match('/^\s*(\d+)\s*(?:hari)?\s*$/i', (string)$b->estimasi_waktu, $m) && (int)$m[1]>0) {
                DB::table('bookings')->where('booking_id',$b->booking_id)->update(['estimasi_hari'=>(int)$m[1]]);
            }
        }
        DB::table('invoice')->where('status_bayar','sebagian')->update(['status_bayar'=>'belum_lunas']);
        if (DB::getDriverName()==='mysql') DB::statement("ALTER TABLE invoice MODIFY status_bayar ENUM('belum_lunas','lunas') NOT NULL DEFAULT 'belum_lunas'");
    }
    public function down(): void {
        // Email verification timestamps intentionally retained to preserve account history.
        if (DB::getDriverName()==='mysql') DB::statement("ALTER TABLE invoice MODIFY status_bayar ENUM('belum_lunas','lunas','sebagian') DEFAULT 'belum_lunas'");
        Schema::table('bookings', function(Blueprint $t) {
            $t->dropColumn(['ukuran_container','harga_satuan_container','estimasi_hari','tanggal_pengiriman_usulan','catatan_penolakan','versi_penawaran']);
        });
        Schema::dropIfExists('tarif_container');
    }
};
