<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('employee_id')->unique()->nullable();
            $table->string('position')->nullable();

            // Stored as plain FK integers here; the FK constraints and the XOR
            // CHECK constraint are added together in create_offices_table, AFTER
            // offices exists and in the correct order to satisfy MySQL 8.4 error 3823.
            $table->unsignedBigInteger('school_id')->nullable();
            // office_id is added in create_offices_table (offices must exist first)

            $table->enum('status', ['Active', 'Inactive', 'On Leave', 'Retired'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
