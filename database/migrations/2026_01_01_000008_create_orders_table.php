<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('discount_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->string('currency', 3)->default('GBP');
            $table->unsignedInteger('subtotal_pence');
            $table->unsignedInteger('discount_pence')->default(0);
            $table->unsignedInteger('shipping_pence')->default(0);
            $table->unsignedInteger('total_pence');
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('unit_price_pence');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('line_total_pence');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
