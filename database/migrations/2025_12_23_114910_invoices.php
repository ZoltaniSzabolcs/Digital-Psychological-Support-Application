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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Financials (Decimal is safer for currency than Float)
            $table->decimal('amount', 10, 2); // Supports up to 99,999,999.99
            $table->string('currency')->default('RON');

            // Romanian Fiscal Identification
            $table->string('cif_cui')->nullable(); // For B2B or medical deduction
            $table->string('series_number')->unique(); // e.g., "PSY-00123"

            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');

            // Path to the generated PDF on storage (S3 or local)
            $table->string('pdf_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
