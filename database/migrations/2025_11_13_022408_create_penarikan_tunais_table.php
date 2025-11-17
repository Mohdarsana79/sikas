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
        Schema::create('penarikan_tunais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penganggaran_id')->constrained('penganggarans')->onDelete('cascade');
            $table->date('tanggal_penarikan');
            $table->decimal('jumlah_penarikan', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penarikan_tunais');
    }
};
