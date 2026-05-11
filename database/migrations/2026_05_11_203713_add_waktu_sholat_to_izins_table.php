<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('izins', function (Blueprint $table) {
            $table->string('waktu_sholat')->nullable()->after('jenis_izin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izins', function (Blueprint $table) {
            $table->dropColumn('waktu_sholat');
        });
    }
};
