<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('size')->nullable()->after('name');
            $table->string('concern')->nullable()->after('size');
            $table->string('skin_type')->nullable()->after('concern');
            $table->string('key_active')->nullable()->after('skin_type');
            $table->text('how_to_use')->nullable()->after('description');
            $table->text('ingredients_inci')->nullable()->after('how_to_use');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['size', 'concern', 'skin_type', 'key_active', 'how_to_use', 'ingredients_inci']);
        });
    }
};
