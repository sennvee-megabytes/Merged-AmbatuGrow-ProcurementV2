<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'supplier_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('currency_id')->constrained('suppliers')->nullOnDelete();
            });
        }

        // Backfill supplier_id on existing products from product_suppliers pivot table or default supplier
        $defaultSupplier = \App\Models\Supplier::first();
        if ($defaultSupplier) {
            \Illuminate\Support\Facades\DB::table('products')
                ->whereNull('supplier_id')
                ->update(['supplier_id' => $defaultSupplier->id]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'supplier_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }
    }
};
