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
        Schema::create('erp_customer', function (Blueprint $table) {
            $table->id();
            $table->string('customerID')->nullable();
            $table->string('mdCode')->nullable();
            $table->string('custCode')->nullable();
            $table->string('custName')->nullable();
            $table->string('contactCellNumber')->nullable();
            $table->string('contactPerson')->nullable();
            $table->string('contactLandline')->nullable();
            $table->string('address')->nullable();
            $table->string('frequencyCategory')->nullable();
            $table->string('mcpDay')->nullable();
            $table->string('mcpSchedule')->nullable();
            $table->string('geolocation')->nullable();
            $table->string('lastUpdated')->nullable();
            $table->string('lastPurchase')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->longText('storeImage')->nullable();
            $table->string('syncstat')->nullable();
            $table->string('dates_tamp')->nullable();
            $table->string('time_stamp')->nullable();
            $table->string('isLockOn')->nullable();
            $table->char('priceCode', 2)->nullable();
            $table->decimal('baseGPSLat')->nullable();
            $table->longText('storeImage2')->nullable();
            $table->string('uploaded_image')->nullable();
            $table->string('custType')->nullable();
            $table->string('isVisit')->nullable();
            $table->string('DefaultOrdType')->nullable();
            $table->string('CityMunCode')->nullable();
            $table->string('REGION')->nullable();
            $table->string('PROVINCE')->nullable();
            $table->string('MUNICIPALITY')->nullable();
            $table->string('BARANGAY')->nullable();
            $table->string('Area')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('KASOSYO')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //  Schema::dropIfExists('tblCustomer');
    }


 
};
