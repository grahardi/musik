<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();

            // Info utama
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('artist');
            $table->string('artist_slug')->nullable();

            // Dipakai buat filter abjad (A-Z / 0-9), di-generate otomatis dari title/artist
            $table->char('first_letter_title', 1)->index();
            $table->char('first_letter_artist', 1)->index();

            // Info musik
            $table->string('original_key', 10)->nullable();
            $table->string('capo', 10)->nullable();
            $table->string('genre')->nullable();

            // Isi chord, format inline: [C]lirik lagu [G]disini
            $table->longText('chord_body');

            // Jejak asal import (buat kredit/atribusi, bukan buat auto-publish)
            $table->string('source_url')->nullable();
            $table->enum('source_site', ['manual', 'chordtela', 'ultimate-guitar'])->default('manual');

            // Draft dulu sampai admin review & edit, baru bisa publish
            $table->boolean('is_published')->default(false);

            $table->unsignedInteger('views_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['is_published']);
            $table->index(['artist']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('songs');
    }
};
