<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_profiles')) {
            return;
        }

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->bigIncrements('profile_id');
            $table->string('nama_perusahaan', 150);
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->text('sejarah')->nullable();
            $table->text('alamat')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telepon', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
