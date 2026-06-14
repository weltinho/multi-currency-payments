<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single table for payment requests — rate fields are write-once (see Payment model).
        // Indexes support employee lists, finance filters, and future 48h expiration job.
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('description');
            $table->char('currency', 3);
            $table->decimal('local_amount', 15, 2);
            $table->decimal('exchange_rate', 18, 8);
            $table->decimal('eur_amount', 15, 2);
            $table->string('rate_source', 64);
            $table->timestamp('rate_fetched_at');
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
