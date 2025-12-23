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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            // Link to the user who wrote the entry
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Type of entry
            $table->enum('type', ['text', 'audio']);

            // Stores the actual text body OR the S3 file path (e.g., 'journals/audio/123.mp3')
            $table->text('content');

            // Controls if the psychologist can see this entry
            $table->enum('visibility', ['private', 'shared'])->default('private');

            $table->timestamps();
            $table->softDeletes(); // GDPR: Allow "trash" before permanent delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
