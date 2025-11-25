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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('total_price');
            $table->string('booking_code')->unique();
            $table->enum('status', ['pending', 'waiting_payment', 'paid', 'cancelled', 'checked_in', 'completed'])->default('pending');
            $table->string('qr_code_url')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'check_in', 'check_out']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
