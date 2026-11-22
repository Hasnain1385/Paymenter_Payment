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
        if (!Schema::hasTable('multibank_transactions')) {
            Schema::create('multibank_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('bank_account_name')->nullable();
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('currency', 10)->default('USD');
                $table->string('transaction_id', 150)->index(); // Customer TRX / UTR / Reference
                $table->string('sender_name', 150); // Customer Account Name / Sender Title
                $table->string('sender_account', 150)->nullable(); // Customer Bank or Wallet
                $table->string('proof_image')->nullable(); // Uploaded screenshot receipt path
                $table->text('customer_notes')->nullable();
                $table->string('status', 30)->default('pending')->index(); // pending, approved, declined, flagged
                $table->text('admin_notes')->nullable(); // Visible note if flagged/under review
                $table->text('decline_reason')->nullable(); // Visible reason if declined
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multibank_transactions');
    }
};
