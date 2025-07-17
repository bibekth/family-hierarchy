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
        Schema::create('hierarchies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users', 'id')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->date('dob')->nullable();
            $table->date('dod')->nullable();
            $table->enum('sex', ['M', 'F'])->nullable();
            $table->string('avatar')->nullable();
            $table->foreignId('father_id')->nullable()->constrained('hierarchies', 'id')->onDelete('cascade');
            $table->foreignId('mother_id')->nullable()->constrained('hierarchies', 'id')->onDelete('cascade');
            $table->foreignId('spouse_id')->nullable()->constrained('hierarchies', 'id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hierarchies');
    }
};
