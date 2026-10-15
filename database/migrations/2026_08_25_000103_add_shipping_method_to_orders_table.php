<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Both the code and the label are stored. The code is what the app
            // reasons about; the label is a snapshot, so an order placed under
            // "Royal Mail Tracked 24" still reads that way years later even if
            // the service is renamed or dropped from the shortlist.
            $table->string('shipping_method')->nullable()->after('shipping_pence');
            $table->string('shipping_method_name')->nullable()->after('shipping_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_method', 'shipping_method_name']);
        });
    }
};
