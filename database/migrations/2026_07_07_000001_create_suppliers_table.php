<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('supplier_name'); // From Doc
            $table->string('name')->nullable(); // For backward compatibility
            $table->string('category')->nullable(); // From Doc
            $table->string('email')->nullable(); // From Doc
            $table->string('phone')->nullable(); // From Doc
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete(); // From Doc
            $table->string('status')->default('Active'); // Active | Blacklisted

            // UI & Legacy columns needed by current views
            $table->string('supplier_id')->nullable(); // Generated, e.g. AGR-00125
            $table->string('supplier_code')->nullable()->unique(); // Seeder legacy code
            $table->string('business_type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->date('since')->nullable();
            $table->string('location')->nullable();
            $table->date('last_transaction')->nullable();

            // Primary Contact Details (Legacy)
            $table->string('contact_name')->nullable();
            $table->string('contact_role')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            // Quick stats (Legacy)
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->decimal('avg_order_value', 14, 2)->default(0);
            $table->unsignedTinyInteger('on_time_rate')->default(0);

            // Contract (Legacy)
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->string('contract_duration')->nullable();
            $table->string('payment_terms')->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->string('contract_document')->nullable();
            $table->string('contract_document_size')->nullable();
            $table->json('contract_scope')->nullable();

            // Performance snapshot (Legacy)
            $table->string('avg_rating_delta')->nullable();
            $table->string('on_time_delta')->nullable();
            $table->decimal('quality_score', 3, 1)->default(0);
            $table->string('quality_delta')->nullable();
            $table->string('total_orders_delta')->nullable();

            // Blacklist info (Legacy)
            $table->string('blacklist_reason')->nullable();
            $table->date('blacklisted_since')->nullable();
            $table->string('risk_level')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
