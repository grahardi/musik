<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->foreignId('genre_id')->nullable()->after('genre')
                ->constrained('genres')->nullOnDelete();
        });

        // Kolom 'genre' (varchar) lama dibiarkan sementara buat data existing,
        // tapi tidak dipakai lagi di form/kode baru. Aman dihapus manual nanti
        // setelah migrasi data genre selesai.
    }

    public function down()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('genre_id');
        });
    }
};
