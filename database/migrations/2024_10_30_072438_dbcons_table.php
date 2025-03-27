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
        Schema::create('dbcons', function (Blueprint $table) {
            $table->id();
            $table->string('machineIdKey');
            $table->string('EncryptedPassword');
            $table->string('PlainTextPassword');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dbcons');
    }
};
