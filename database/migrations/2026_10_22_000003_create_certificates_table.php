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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('bebras_username', 100)->index();
            $table->string('participant_name', 255);
            $table->string('school_name', 255)->nullable();
            $table->string('category', 20); // SiKecil, Siaga, Penggalang, Penegak
            $table->string('award_type', 20); // Participant, Winner
            $table->unsignedSmallInteger('year')->index();
            $table->string('file_path', 500);
            $table->string('original_filename', 255)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->char('file_sha256', 64)->nullable();
            $table->timestamps();

            $table->index(['bebras_username', 'year']);
            $table->index(['year', 'category']);
            $table->unique(['bebras_username', 'year', 'category', 'award_type'], 'cert_unique_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
