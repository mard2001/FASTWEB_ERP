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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('mobile'); // Assuming 'mobile' is the column in the 'otps' table
            
            $table->foreign('mobile') // Define foreign key for 'mobile' column
                ->references('mobile') // Reference the 'mobile' column in the 'users' table
                ->on('users') // Specify that it references the 'users' table
                ->onDelete('cascade'); // Ensure that OTPs are deleted when the user is deleted

            $table->string('otp');
            $table->timestamp('otp_expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
