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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psychologist_id')->constrained('users');
            $table->foreignId('patient_id')->constrained('users');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('meeting_link')->nullable(); // Google Meet/Zoom URL
            $table->enum('status', ['scheduled', 'completed', 'cancelled']);
            $table->text('psychologist_notes')->nullable(); // Private notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
