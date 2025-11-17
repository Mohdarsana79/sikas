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
        Schema::create('rekaman_perubahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rkas_perubahan_id')->constrained('rkas_perubahans')->onDelete('cascade');
            $table->string('action'); // 'update', 'delete'
            $table->json('changes')->nullable(); // Menyimpan perubahan data
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekaman_perubahans');
    }
};
