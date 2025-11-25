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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['qris', 'va', 'ewallet'])->nullable(); 
            $table->string('xendit_invoice_id')->nullable();
            $table->enum('xendit_status', ['PENDING', 'PAID', 'EXPIRED', 'FAILED'])->nullable(); 
            $table->string('invoice_url')->nullable(); 
            $table->decimal('amount', 12, 2);
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index('xendit_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
