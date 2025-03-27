<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_details', function (Blueprint $table) {
            $table->id(); // Automatically creates an auto-incrementing ID column
            $table->string('supplier_name');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('supplier_type')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('tax_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps(); // Automatically adds 'created_at' and 'updated_at'
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_details');
    }
};
