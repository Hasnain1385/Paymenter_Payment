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
        if (!Schema::hasTable('multibank_accounts')) {
            Schema::create('multibank_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Chase Bank / Zelle", "Revolut EUR", "HDFC UPI"
                $table->string('badge')->nullable(); // e.g. "Instant", "Zero Fee", "Recommended"
                $table->string('account_title'); // Account Holder Name
                $table->string('account_number'); // Account #, IBAN, UPI ID, or Wallet Address
                $table->string('routing_code')->nullable(); // SWIFT / BIC / IFSC / Routing Number
                $table->text('qr_payload')->nullable(); // Raw payload or payment URI for dynamic QR
                $table->string('qr_image_url')->nullable(); // Direct image URL or uploaded file path
                $table->text('instructions')->nullable(); // Custom instructions or notes
                $table->string('currency', 10)->default('ALL'); // Currency code or ALL
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multibank_accounts');
    }
};
