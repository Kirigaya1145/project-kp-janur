<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_profiles') || Schema::hasColumn('company_profiles', 'logo')) {
            return;
        }

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('logo', 255)->nullable()->after('nama_perusahaan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('company_profiles') || ! Schema::hasColumn('company_profiles', 'logo')) {
            return;
        }

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
