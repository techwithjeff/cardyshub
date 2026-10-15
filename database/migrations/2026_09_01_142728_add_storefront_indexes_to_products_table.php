<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes for the two homepage product rails.
     *
     * Both filter on `status` and order by `created_at, id`, and neither had an
     * index to use: MySQL was doing a full table scan plus a filesort on every
     * single homepage request. A filesort spills to a temporary file on disk,
     * which is why the homepage was the first thing to fall over when the
     * server ran out of temp space while every other page kept working.
     *
     * Two indexes rather than one, because a composite index only helps from
     * the left: (status, is_featured, created_at) cannot serve an ordered query
     * that filters on status alone, since is_featured sits in between.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // "New in": published, newest first.
            $table->index(['status', 'created_at'], 'products_status_created_at_index');

            // "Bestsellers": published and featured, newest first.
            $table->index(['status', 'is_featured', 'created_at'], 'products_status_featured_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_created_at_index');
            $table->dropIndex('products_status_featured_created_at_index');
        });
    }
};
